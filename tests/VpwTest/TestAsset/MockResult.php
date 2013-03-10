<?php
namespace ZfeDataTest\TestAsset;

use Zend\Db\Adapter\Driver\ResultInterface;

class MockResult implements ResultInterface
{

    private $data;

    private $position;


    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Force buffering
     *
     * @return void
     */
    public function buffer()
    {

    }

    /**
     * Check if is buffered
     *
     * @return bool|null
    */
    public function isBuffered()
    {
        return false;
    }

    /**
     * Is query result?
     *
     * @return bool
    */
    public function isQueryResult()
    {
        return false;
    }

    /**
     * Get affected rows
     *
     * @return integer
    */
    public function getAffectedRows()
    {
        return 0;
    }

    /**
     * Get generated value
     *
     * @return mixed|null
    */
    public function getGeneratedValue()
    {
        return false;
    }

    /**
     * Get the resource
     *
     * @return mixed
    */
    public function getResource()
    {
        return null;
    }

    /**
     * Get field count
     *
     * @return integer
    */
    public function getFieldCount()
    {
        return 0;
    }


    public function count()
    {
        return count($this->data);
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function next()
    {
        ++$this->position;
    }

    public function current()
    {
        return $this->data[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function valid()
    {
        return isset($this->data[$this->position]);
    }
}
