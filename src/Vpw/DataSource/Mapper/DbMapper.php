<?php
namespace Vpw\DataSource\Mapper;

use Zend\Db\Sql\Predicate\PredicateInterface;

use Zend\Db\Sql\Select;

use Vpw\DataSource\Exception\RuntimeException;

use Zend\Db\Metadata\Object\ColumnObject;

use Zend\Db\Metadata\Object\ConstraintObject;

use Zend\Db\Adapter\Driver\StatementInterface;

use Zend\Filter\Word\UnderscoreToCamelCase;

use Zend\Db\Adapter\ParameterContainer;

use Zend\Db\Sql\Update;

use Zend\Db\Adapter\Adapter;

class DbMapper implements DataMapperInterface
{

    /**
     * @var Adapter
     */
    protected $db;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var ConstraintObject
     */
    private $primaryKey;

    /**
     * @var ColumnObject
     */
    private $autoIncrementColumn;

    /**
     * @var bool
     */
    private $aiColumnHasBeenSearched = false;

    /**
     * @var UnderscoreToCamelCase
     */
    private $filter;




    /**
     *
     * @param Adapter $db
     * @param string $schema
     * @param string $table
     */
    public function __construct(Adapter $db, $table)
    {
        $this->db = $db;
        $this->table = $table;
        $this->filter = new UnderscoreToCamelCase();
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\DataSource\Mapper\DataMapperInterface::save()
     */
    public function save(AbstractObject $object)
    {
        if ($object->isLoaded() === true) {
            $this->update($object);
        } else {
            $this->insert($object);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\DataSource\Mapper\DataMapperInterface::insert()
     */
    public function insert(AbstractObject $object)
    {
        $result = $this->getInsertStatement($object)->execute();

        $aiColumn = $this->getAutoIncrementColumn();
        if ($aiColumn !== null) {
            $method = $this->getMethodSetterName($aiColumn->getName());
            $object->{$method}($this->db->getDriver()->getLastGeneratedValue());
        }

        $object->setLoaded(true);

        return $result->getAffectedRows();
    }


    /**
     * @param AbstractObject $object
     * @return \Driver\StatementInterface
     */
    public function getInsertStatement(AbstractObject $object)
    {
        $statement = $this->db->createStatement(
            "INSERT INTO " . $this->db->getPlatform()->quoteIdentifier($this->table)
        );

        $this->addSetPartInStatement($object, $statement);

        return $statement;
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\DataSource\Mapper\DataMapperInterface::update()
     */
    public function update(AbstractObject $object)
    {
        $result = $this->getUpdateStatement($object)->execute();
        return $result->getAffectedRows();
    }


    /**
     * @param AbstractObject $object
     * @return \Driver\StatementInterface
     */
    public function getUpdateStatement(AbstractObject $object)
    {

        $statement = $this->db->createStatement(
            "UPDATE " . $this->db->getPlatform()->quoteIdentifier($this->table)
        );

        $this->addSetPartInStatement($object, $statement, $this->getPrimaryKey()->getColumns());
        $this->addIdentitySqlInStatement($object, $statement);

        return $statement;
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\DataSource\Mapper\DataMapperInterface::delete()
     */
    public function delete(AbstractObject $object)
    {
        if ($object->isLoaded() === true) {
            $result = $this->getDeleteStatement($object)->execute();
            return $result->getAffectedRows();
        }
    }


    /**
     * @param AbstractObject $object
     * @return \Driver\StatementInterface
     */
    public function getDeleteStatement(AbstractObject $object)
    {
        $statement = $this->db->createStatement(
            "DELETE FROM " . $this->db->getPlatform()->quoteIdentifier($this->table)
        );

        $this->addIdentitySqlInStatement($object, $statement);

        return $statement;
    }




    /**
     * @param AbstractObject $object
     * @param StatementInterface $statement
     */
    protected function addSetPartInStatement(AbstractObject $object, StatementInterface $statement,
        array $excluded = array())
    {
        $platform = $this->db->getPlatform();
        $driver = $this->db->getDriver();

        $sql = $statement->getSql() . " SET ";
        $parameterContainer = $statement->getParameterContainer();

        foreach ($this->getMetadata()['columns'] as $column) {
            $columnName = $column->getName();

            if (in_array($columnName, $excluded, true) === true) {
                continue;
            }

            $methodName = $this->getMethodGetterName($columnName);

            if (method_exists($object, $methodName) === false) {
                continue;
            }

            $sql .= $platform->quoteIdentifier($columnName) . '=' . $driver->formatParameterName($columnName) . ', ';
            $parameterContainer->offsetSet(
                $columnName,
                $object->$methodName(),
                $this->columnTypeToParameterType($column->getDataType())
            );
        }

        $statement->setSql(substr($sql, 0, -2));
    }


    /**
     * @param AbstractObject $object
     * @param StatementInterface $statement
     * @throws \RuntimeException
     */
    protected function addIdentitySqlInStatement(AbstractObject $object, StatementInterface $statement)
    {
        $platform = $this->db->getPlatform();
        $driver = $this->db->getDriver();

        $sql = $statement->getSql() . " WHERE ";
        $parameterContainer = $statement->getParameterContainer();

        $columns = $this->getMetadata()['columns'];

        foreach ($this->getPrimaryKey()->getColumns() as $columnName) {
            $methodName = $this->getMethodGetterName($columnName);

            if (method_exists($object, $methodName) === false) {
                throw new \RuntimeException("No method '".get_class($object)."::".$methodName."' exists");
            }

            foreach ($columns as $column) {
                if ($column->getName() === $columnName) {
                    $sql .= $platform->quoteIdentifier($columnName) . '=' . $driver->formatParameterName($columnName) . ' AND ';
                    $parameterContainer->offsetSet(
                        $columnName,
                        $object->$methodName(),
                        $this->columnTypeToParameterType($column->getDataType())
                    );

                    break;
                }
            }
        }

        $statement->setSql(substr($sql, 0, -5));
    }


    /**
     * @param string $columnName
     * @return string
     */
    protected function getMethodGetterName($columnName)
    {
        return 'get' . ucfirst($this->filter->filter($columnName));
    }

    /**
     * @param string $columnName
     * @return string
     */
    protected function getMethodSetterName($columnName)
    {
        return 'set' . ucfirst($this->filter->filter($columnName));
    }

    /**
     * @return array
     */
    protected function getMetadata()
    {
        if ($this->metadata === null) {
            $this->metaData = $this->loadMetaData();
        }

        return $this->metadata;
    }

    abstract protected function loadMetaData();


    protected function setMetadata(array $metaData)
    {
        if (isset($metadata['columns']) === false) {
            throw new RuntimeException("A 'columns' key is required in metadata array");
        }

        if (isset($metadata['constraints']) === false) {
            throw new RuntimeException("A 'constraints' key is required in metadata array");
        }

        $this->metadata = $metaData;
    }

    /**
     * @return ConstraintObject
     */
    protected function getPrimaryKey()
    {
        if ($this->primaryKey === null) {
            $constraints = $this->getMetadata()['constraints'];
            foreach ($constraints as $constraint) {
                if ($constraint->isPrimaryKey() === true) {
                    $this->primaryKey = $constraint;
                    break;
                }
            }
        }

        return $this->primaryKey;
    }

    protected function getAutoIncrementColumn()
    {
        if ($this->aiColumnHasBeenSearched === false) {
            foreach ($this->getMetadata()['columns'] as $column) {
                if ($column->getErrata('auto_increment') === true) {
                    $this->autoIncrementColumn = $column;
                }
            }

            $this->aiColumnHasBeenSearched = true;
        }

        return $this->autoIncrementColumn;
    }

    public function columnTypeToParameterType($type)
    {
        switch ($type)
        {
            default:
                return ParameterContainer::TYPE_STRING;
            case 'timestamp':
            case 'year':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
                return ParameterContainer::TYPE_INTEGER;
                break;
            case 'double':
            case 'float':
                return ParameterContainer::TYPE_DOUBLE;
        }
    }


    public function find($pk)
    {
        $select = new Select($this->table);
    }

    public function findAll(PredicateInterface $predicate)
    {

    }
}
