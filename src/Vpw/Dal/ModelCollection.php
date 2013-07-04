<?php
namespace Vpw\Dal;

use Zend\Stdlib\ArraySerializableInterface;

class ModelCollection implements \Iterator, \Countable, ArraySerializableInterface
{

    /**
     * @var array
     */
    private $storage = array();

    /**
     * @var int
     */
    private $totalNbRows = 0;

    /**
     *
     * @param array $objects
     */
    public function __construct(array $objects = array())
    {
        $this->exchangeArray($objects);
    }


    public function isEmpty()
    {
        return count($this->storage) === 0;
    }

    /**
     * Returns the total number of rows available, not in the collection, but in globaly
     * Useful for pagination, by example
     * @return number
     */
    public function getTotalNbRows()
    {
        return $this->totalNbRows;
    }

    /**
     * @param int $totalNbRows
     */
    public function setTotalNbRows($totalNbRows)
    {
        $this->totalNbRows = intval($totalNbRows);
    }

    /**
     *
     * @param ModelObject $object
     */
    public function add(ModelObject $object)
    {
        $key = $object->getIdentityKey();

        if (isset($storage[$key]) === false) {
            $this->storage[$key] = $object;
        }
    }

    /**
     *
     * @param string|ModelObject $key
     */
    public function remove($key)
    {
        if ($key instanceof ModelObject) {
            $key = $key->getIdentityKey();
        }

        if (isset($this->storage[$key]) === false) {
            return null;
        }

        $object = $this->storage[$key];
        unset($this->storage[$key]);

        return $object;
    }

    /**
     *
     * @param string $key
     */
    public function get($key)
    {
        return isset($this->storage[$key]) ? $this->storage[$key] : null;
    }

    /**
     *
     * @param string|ModelObject $key
     */
    public function contains($key)
    {
        if ($key instanceof ModelObject) {
            $key = $key->getIdentityKey();
        }

        return isset($this->storage[$key]);
    }

    /**
     *
     */
    public function clear()
    {
        $this->storage = array();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current ()
    {
        $current = current($this->storage);
        return ($current === false) ? null : $current;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next ()
    {
        next($this->storage);
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key ()
    {
        return key($this->storage);
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid ()
    {
        return key($this->storage) !== null;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind ()
    {
        reset($this->storage);
    }

    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->storage);
    }

    /**
     *
     * (non-PHPdoc)
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $objects)
    {
        $this->clear();
        foreach ($objects as $object) {
            $this->add($object);
        }
    }

    public function getArrayCopy()
    {
        $array = array();

        foreach ($this->storage as $object) {
            $array[] = $object->getArrayCopy();
        }

        return $array;
    }
}
