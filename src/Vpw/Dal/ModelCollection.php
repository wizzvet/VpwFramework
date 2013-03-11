<?php
namespace Vpw\Dal;

use Zend\Db\Adapter\Driver\ResultInterface;

use Zend\Db\ResultSet\ResultSetInterface;

class ModelCollection implements \Iterator
{

    /**
     * @var AbstractObject
     */
    private $dataObjectPrototype;


    /**
     * @var \Iterator
     */
    private $dataSource;


    /**
     * http://ralphschindler.com/2012/03/09/php-constructor-best-practices-and-the-prototype-pattern
     *
     * @param AbstractObject $dataObjectPrototype
     */
    public function __construct(ModelObject $dataObjectPrototype)
    {
        $this->dataObjectPrototype = $dataObjectPrototype;
    }


    public function initialize($dataSource)
    {
        if (($dataSource instanceof \Iterator) === false) {
            throw new \BadMethodCallException("The datasource is not an Iterator object");
        }
        $this->dataSource = $dataSource;
    }

    public function rewind()
    {
        $this->dataSource->rewind();
    }

    public function current()
    {
        $data = $this->dataSource->current();
        $o = clone $this->dataObjectPrototype;
        $o->exchangeArray($data);
        return $o;
    }

    public function key()
    {
        return $this->dataSource->key();
    }

    public function next()
    {
        $this->dataSource->next();
    }

    public function valid()
    {
        return $this->dataSource->valid();
    }

    public function count()
    {
        return $this->dataSource->count();
    }
}
