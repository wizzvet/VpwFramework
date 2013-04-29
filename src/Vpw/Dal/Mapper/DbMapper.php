<?php
namespace Vpw\Dal\Mapper;

use Zend\Db\Adapter\ParameterContainer;

use Zend\Db\Sql\PreparableSqlInterface;

use Zend\Db\Sql\Where;

use Vpw\Dal\ModelCollection;

use Zend\Db\Sql\Delete;

use Vpw\Dal\ModelObject;

use Zend\Db\Sql\Insert;

use Vpw\Dal\Mapper\DbMetadata;

use Zend\Db\Sql\Select;

use Zend\Db\Sql\Update;

use Zend\Db\Adapter\Adapter;
use Vpw\Dal\Exception\RuntimeException;
use Vpw\Dal\Exception\NoRowFoundException;

abstract class DbMapper implements MapperInterface
{

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $table;

    /**
     *
     * @var DbMetadata
     */
    private $metadata;

    /**
     *
     * @var ModelObject
     */
    private $modelObjectPrototype;

    /**
     *
     * @var string
     */
    protected $modelObjectClassName = "Vpw\Dal\ModelObject";

    /**
     *
     * @var string
     */
    protected $modelCollectionClassName = "Vpw\Dal\ModelCollection";

    /**
     * Map of all model object loaded by this mapper
     * @var array
     */
    protected $loadedMap = array();

    /**
     *
     * @param Adapter $db
     * @param string  $schema
     * @param string  $table
     */
    public function __construct(Adapter $adapter, $table)
    {
        $this->adapter = $adapter;
        $this->table = $table;
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\MapperInterface::save()
     */
    public function save(ModelObject $object)
    {
        if ($object->isLoaded() === true) {
            $this->update($object);
        } else {
            $this->insert($object);
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\MapperInterface::insert()
     */
    public function insert(ModelObject $object)
    {
        $statement = $this->getInsertStatement($object);
        $result = $statement->execute();

        $aiColumn = $this->getMetadata()->getAutoIncrementColumn();
        if ($aiColumn !== null) {
            $object->exchangeArray(array($aiColumn->getName() => $this->adapter->getDriver()->getLastGeneratedValue()));
        }

        $object->setLoaded(true);

        return $result->getAffectedRows();
    }

    /**
     * @param  ModelObject                                $object
     * @return \Zend\Db\Adapter\Driver\StatementInterface
     */
    public function getInsertStatement(ModelObject $object)
    {
        $insert = new Insert($this->table);
        $insert->values($object->getArrayCopy());

        return $this->createStatement($insert);
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\MapperInterface::update()
     */
    public function update(ModelObject $object)
    {
        $result = $this->getUpdateStatement($object)->execute();

        return $result->getAffectedRows();
    }

    /**
     * @param  ModelObject                $object
     * @return \Driver\StatementInterface
     */
    public function getUpdateStatement(ModelObject $object)
    {
        $data = $object->getArrayCopy();
        $pkColumnsName = $this->getMetadata()->getPrimaryKey();

        $where = array();
        foreach ($pkColumnsName as $name) {
            $where[$name] = $data[$name];
            unset($data[$name]);
        }

        $update = new Update($this->table);
        $update->set($data);
        $update->where($where);

        return $this->createStatement($update);
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\MapperInterface::delete()
     */
    public function delete(ModelObject $object)
    {
        if ($object->isLoaded() === true) {
            $result = $this->getDeleteStatement($object)->execute();
            $object->setLoaded(false);

            return $result->getAffectedRows();
        }
    }

    /**
     * @param  ModelObject                $object
     * @return \Driver\StatementInterface
     */
    public function getDeleteStatement(ModelObject $object)
    {
        $data = $object->getArrayCopy();
        $pkColumnsName = $this->getMetadata()->getPrimaryKey();

        $where = array();
        foreach ($pkColumnsName as $name) {
            $where[$name] = $data[$name];
        }

        $delete = new Delete($this->table);
        $delete->where($where);

        return $this->createStatement($delete);
    }

    /**
     * Create a statement based on an SQL object + set type hinting
     *
     * @param  PreparableSqlInterface                     $sql
     * @return \Zend\Db\Adapter\Driver\StatementInterface
     */
    protected function createStatement(PreparableSqlInterface $sql)
    {
        $statement = $this->adapter->getDriver()->createStatement();
        $sql->prepareStatement($this->adapter, $statement);

        $parameterContainer = $statement->getParameterContainer();

        $columns = $this->getMetadata()->getColumns();
        foreach ($columns as $column) {
            switch ($column->getDataType()) {
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'int':
                case 'bigint':
                    $parameterContainer->offsetSetErrata($column->getName(), ParameterContainer::TYPE_INTEGER);
                    break;
                case 'float':
                case 'double':
                    $parameterContainer->offsetSetErrata($column->getName(), ParameterContainer::TYPE_DOUBLE);
                    break;
                default:
                    $parameterContainer->offsetSetErrata($column->getName(), ParameterContainer::TYPE_STRING);
                    break;
            }

        }

        return $statement;
    }

    /**
     *
     * @return \Vpw\Dal\Mapper\DbMetadata
     */
    public function getMetadata()
    {
        if ($this->metadata === null) {
            $this->metadata = $this->loadMetaData();
        }

        return $this->metadata;
    }

    /**
     * @return \Vpw\Dal\Mapper\DbMetadata
     */
    abstract protected function loadMetadata();

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\MapperInterface::k()
     */
    public function find($key, $flags = 0)
    {
        return $this->findOne(
            $this->primaryKeyToWhere($key),
            $flags
        );
    }

    protected function findOne($where, $flags = 0)
    {
        $select = $this->createSelect($flags);
        $select->where($where);

        $resultSet = $this->createStatement($select)->execute();
        $collection = $this->loadData($resultSet, $flags);
        $resultSet->getResource()->close();

        if ($collection->count() === 0) {
            throw new NoRowFoundException("No row found");
        }

        return $collection->current();
    }

    /**
     *
     * @param  string                   $where
     * @param  string                   $options
     * @return \Vpw\Dal\ModelCollection
     */
    public function findAll($where = null, $options = null, $flags = 0)
    {
        $select = $this->createFindAllSelect($where, $options, $flags);
        $select->quantifier("SQL_CALC_FOUND_ROWS");

        $result = $this->createStatement($select)->execute();

        $totalNbRowsResult = $this->adapter->getDriver()->getConnection()->execute("SELECT FOUND_ROWS() as nb");
        $totalNbRows = $totalNbRowsResult->current()['nb'];
        $totalNbRowsResult->getResource()->close();

        $collection = $this->loadData($result, $flags);
        $collection->setTotalNbRows($totalNbRows);

        $result->getResource()->close();

        return $collection;
    }


    protected function createFindAllSelect($where = null, $options = null, $flags = 0)
    {
        $select = $this->createSelect($flags);

        if ($where !== null) {
            $select->where($where);
        }

        if ($options !== null) {
            if (isset($options['limit']) === true) {
                $select->limit($options['limit']);
            }

            if (isset($options['offset']) === true) {
                $select->offset($options['offset']);
            }

            if (isset($options['order']) === true) {
                $select->order($options['order']);
            }
        }

        return $select;
    }

    /**
     *
     * @param  number              $flags
     * @return \Zend\Db\Sql\Select
     */
    protected function createSelect($flags = 0)
    {
        return new Select($this->table);
    }

    /**
     *
     * @param  mixed              $key
     * @return \Zend\Db\Sql\Where
     */
    protected function primaryKeyToWhere($key)
    {
        $primaryKey = $this->getMetadata()->getPrimaryKey();

        if (is_array($key) === false) {
            $key = array_combine($primaryKey, array($key));
        }

        $where = new Where();
        foreach ($primaryKey as $columnName) {
            $where->equalTo($this->table.'.'.$columnName, $key[$columnName]);
        }

        return $where;
    }

    /**
     *
     * @param  \Iterator       $resultSet
     * @param  number          $flags
     * @return ModelCollection
     */
    public function loadData(\Iterator $resultSet, $flags = 0)
    {
        $collection = new $this->modelCollectionClassName();

        foreach ($resultSet as $data) {
            $model = $this->load($data, $flags);
            $collection->add($model);
        }

        return $collection;
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\MapperInterface::load()
     */
    public function load($data, $flags = 0)
    {
        $key = $this->getModelObjectKey($data);

        if (isset($this->loadedMap[$key]) === false) {
            $this->loadedMap[$key] = $this->doLoad($data, $flags);
        }

        $this->loadCollectionModels($this->loadedMap[$key], $data, $flags);

        return $this->loadedMap[$key];
    }

    /**
     *
     * @param  unknown              $data
     * @return \Vpw\Dal\ModelObject
     */
    protected function doLoad($data, $flags = 0)
    {
        $prototype = $this->createModelObject();
        $object = clone $prototype;
        $object->load($data);
        $object->setFlags($flags);

        return  $object;
    }

    /**
     *
     * @param unknown $model
     * @param array   $data
     * @param number  $flags
     */
    protected function loadCollectionModels(ModelObject $model, $data, $flags = 0)
    {
        //Par défault, on ne fait rien
    }

    /**
     * For performance reason, we clone a prototype
     * @return \Vpw\Dal\ModelObject
     */
    public function createModelObject()
    {
        return clone $this->getModelObjectPrototype();
    }

    /**
     *
     * @return \Vpw\Dal\ModelObject
     */
    private function getModelObjectPrototype()
    {
        if ($this->modelObjectPrototype === null) {
            $this->modelObjectPrototype = new $this->modelObjectClassName();
        }

        return $this->modelObjectPrototype;
    }

    /**
     *
     * @param array|\ArrayAccess|string $data
     */
    private function getModelObjectKey($data)
    {
        if (is_scalar($data) === true) {
            return strval($data);
        }

        $key = '';
        foreach ($this->getMetadata()->getPrimaryKey() as $name) {
            if (isset($data[$name]) === false) {
                throw new RuntimeException(
                    "Unable to find the primary-key field : $name in the data '".var_export($data, true)."'"
                );
            }
            $key .= $data[$name] . '-';
        }

        return substr($key, 0, -1);
    }

    public function getTableName()
    {
        return $this->table;
    }
}
