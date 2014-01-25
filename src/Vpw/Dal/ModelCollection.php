<?php
namespace Vpw\Dal;

use Zend\Stdlib\ArraySerializableInterface;
use Zend\Stdlib\ArrayObject;

class ModelCollection implements \Iterator, \Countable, ArraySerializableInterface
{

    /**
     * @var ArrayObject
     */
    protected $storage;

    /**
     *
     * @var \Iterator
     */
    private $iterator;

    /**
     * @var int
     */
    protected $totalNbRows = 0;

    /**
     *
     * @param array $objects
     */
    public function __construct(array $objects = array())
    {
        $this->setStorage(new ArrayObject());
        $this->exchangeArray($objects);
    }

    public function setStorage(ArrayObject $storage)
    {
        $this->storage = $storage;
        $this->iterator = null;
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
     * On n'utiliser pas l' "identity key" comme index, car elle peut est être nulle, pour les objects nouvellement
     * créés
     *
     * @param ModelObject $object
     */
    public function add(ModelObject $object)
    {
        if ($this->contains($object) === false) {
            $this->storage->append($object);
            $this->iterator = null;
        }
    }


    /**
     *
     * @param ModelCollection $collection
     */
    public function addAll(ModelCollection $collection)
    {
        foreach ($collection as $model) {
            $this->add($model);
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

        if ($key === null) {
            return null;
        }

        foreach ($this->getIterator() as $index => $object) {
            if ($object->getIdentityKey() === $key) {
                $this->storage->offsetUnset($index);
                $this->iterator = null;
                return $object;
            }
        }

        return null;
    }

    /**
     *
     * @param string $key
     */
    public function get($key)
    {
        foreach ($this->getIterator() as $object) {
            if ($object->getIdentityKey() === $key) {
                return $object;
            }
        }

        return null;
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

        if ($key === null) {
            return false;
        }

        foreach ($this->getIterator() as $object) {
            if ($object->getIdentityKey() === $key) {
                return true;
            }
        }

        return false;
    }


    public function isEmpty()
    {
        return count($this->storage) === 0;
    }

    /**
     *
     */
    public function clear()
    {
        $this->storage->exchangeArray(array());
        $this->iterator = null;
    }


    public function current ()
    {
        return $this->getIterator()->current();
    }

    public function next ()
    {
        return $this->getIterator()->next();
    }

    public function key ()
    {
        return $this->getIterator()->key();
    }

    public function valid ()
    {
        return $this->getIterator()->valid();
    }

    public function rewind ()
    {
        return $this->getIterator()->rewind();
    }

    protected function getIterator()
    {
        if ($this->iterator === null) {
            $this->iterator = $this->storage->getIterator();
        }

        return $this->iterator;
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

    public function getArrayCopy($deep = false)
    {
        $array = array();

        foreach ($this->getIterator() as $object) {
            $array[] = $object->getArrayCopy($deep);
        }

        return $array;
    }

    public function sort($key)
    {
        $this->getIterator()->uasort(function ($o1, $o2) use ($key) {
            return strcmp($o1->offsetGet($key), $o2->offsetGet($key));
        });
    }


    /**
     * Return an array of ModelObject which are not present in $collection
     *
     * @param ModelCollection $collection
     */
    public function diff(ModelCollection $collection)
    {
        $diff = array();

        foreach ($this->getIterator() as $model) {
            if ($collection->contains($model) === false) {
                $diff[] = $model;
            }
        }

        return $diff;
    }


    public function intersect(ModelCollection $collection)
    {
        $intersection = array();

        foreach ($this->getIterator() as $model) {
            if ($collection->contains($model) === true) {
                $intersection[] = $model;
            }
        }

        return $intersection;
    }


    /**
     * @return null|array Returns an array of identity, or null if the collection is empty
     */
    public function getIdentity()
    {
        if ($this->isEmpty() === true) {
            return null;
        }

        $list = array();
        foreach ($this->getIterator() as $model) {
            $list[] = $model->getIdentity();
        }

        return $list;
    }

    public function __sleep()
    {
        return array(
            'storage',
            'totalNbRows'
        );
    }

    public function __clone()
    {
        $this->storage = clone $this->storage;
        $this->iterator = null;

        foreach ($this->storage as $index => $model) {
            $this->storage->offsetSet($index, clone $model);
        }
    }
}
