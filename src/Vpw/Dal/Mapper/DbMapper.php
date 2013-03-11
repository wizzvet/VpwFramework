<?php
namespace Vpw\Dal\Mapper;

use Vpw\Dal\ModelCollection;

use Zend\Db\Sql\Delete;

use Vpw\Dal\ModelObject;

use Zend\Db\Sql\Insert;

use Vpw\Dal\Mapper\DbMetadata;

use Vpw\Dal\Exception\RuntimeException;

use Zend\Db\Sql\Select;

use Zend\Db\Sql\Update;

use Zend\Db\Adapter\Adapter;

use Zend\Db\Adapter\ParameterContainer;

abstract class DbMapper implements DataMapperInterface
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
     *
     * @var DbMetadata
     */
    private $metadata;

    /**
     *
     * @var ModelObject
     */
    private $modelObjectPrototype;


    protected $modelObjectClassName = "Vpw\Dal\ModelObject";


    protected $modelCollectionClassName = "Vpw\Dal\ModelCollection";


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
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\DataMapperInterface::save()
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
     * @see \Vpw\Dal\Mapper\DataMapperInterface::insert()
     */
    public function insert(ModelObject $object)
    {
        $statement = $this->getInsertStatement($object);
        $result = $statement->execute();

        $aiColumn = $this->getMetadata()->getAutoIncrementColumn();
        if ($aiColumn !== null) {
            $object->exchangeArray(array($aiColumn->getName() => $this->db->getDriver()->getLastGeneratedValue()));
        }

        $object->setLoaded(true);

        return $result->getAffectedRows();
    }


    /**
     * @param ModelObject $object
     * @return \Driver\StatementInterface
     */
    public function getInsertStatement(ModelObject $object)
    {
        $insert = new Insert($this->table);
        $insert->columns(array_keys($this->getMetadata()->getColumns()));
        $insert->values($object->getArrayCopy());

        $statement = $this->db->getDriver()->createStatement();
        $insert->prepareStatement($this->db, $statement);

        return $statement;
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\DataMapperInterface::update()
     */
    public function update(ModelObject $object)
    {
        $result = $this->getUpdateStatement($object)->execute();
        return $result->getAffectedRows();
    }


    /**
     * @param ModelObject $object
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

        $statement = $this->db->getDriver()->createStatement();
        $update->prepareStatement($this->db, $statement);

        return $statement;
    }

    /**
     * (non-PHPdoc)
     * @see \Vpw\Dal\Mapper\DataMapperInterface::delete()
     */
    public function delete(ModelObject $object)
    {
        if ($object->isLoaded() === true) {
            $result = $this->getDeleteStatement($object)->execute();
            return $result->getAffectedRows();
        }
    }


    /**
     * @param ModelObject $object
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

        $statement = $this->db->getDriver()->createStatement();
        $delete->prepareStatement($this->db, $statement);

        return $statement;
    }


    /**
     *
     * @return \Vpw\Dal\Mapper\DbMetadata
     */
    protected function getMetadata()
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
     *
     * @param unknown $where
     * @return ModelObject
     */
    public function find($where)
    {
        $select = $this->getDefaultSelect();
        $select->where($where);

        $statement = $this->db->getDriver()->createStatement();
        $select->prepareStatement($this->db, $statement);

        $result = $statement->execute();

        $object = new $this->modelObjectClassName();

        $object->exchangeArray($result->current());

        return $object;
    }

    /**
     *
     * @param unknown $where
     * @return ModelCollection
     */
    public function findAll($where)
    {
        $select = $this->getDefaultSelect();
        $select->where($where);

        $statement = $this->db->getDriver()->createStatement();
        $select->prepareStatement($this->db, $statement);

        $result = $statement->execute();

        $collection = new $this->modelCollectionClassName(new $this->modelObjectClassName());
        $collection->initialize($result);

        return $collection;
    }


    public function getDefaultSelect()
    {
        return new Select($this->table);
    }
}
