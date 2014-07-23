<?php
namespace Vpw\Dal;

use Zend\Stdlib\ArraySerializableInterface;
use Zend\Stdlib\ArrayObject;


/**
 * The identity key could be null for the new object. So we have to handle this specifity.
 *
 * @author christophe.borsenberger@wizzvet.com
 *
 */
class ModelCollection implements \Iterator, \Countable, ArraySerializableInterface
{

    /**
     * @var ArrayObject
     */
    protected $storage;

    /**
     * @var int
     */
    protected $totalNbRows = 0;

    /**
     * @var \Iterator
     */
    private $iterator = null;

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

    public function getStorage()
    {
        return $this->storage;
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
     * @param ModelCollection $collection
     */
    public function addAll(ModelCollection $collection)
    {
        foreach ($collection as $model) {
            $this->add($model);
        }
    }

    /**
     * @param ModelObject $object
     */
    public function add(ModelObject $object)
    {
        if ($this->contains($object) === false) {

            $key = $object->getIdentityKey();

            if ($key === null) {
                $this->storage->append($object);
            } else {
                $this->storage->offsetSet($key, $object);
            }

            $this->iterator = null;
        }
    }


    /**
     * @param string|ModelObject $key
     */
    public function remove($keyOrModelObject)
    {
        $key = $this->getIdentityKey($keyOrModelObject);

        if ($key === null) {
            foreach ($this->getIterator() as $index => $tmp) {
                if ($tmp === $keyOrModelObject) {
                    $this->storage->offsetUnset($index);
                    $this->iterator = null;
                }
                return $tmp;
            }
        } else {
            $o = $this->storage->offsetGet($key);
            $this->storage->offsetUnset($key);
            return $o;
        }
    }


    /**
     * @param string $key
     * @return ModelObject the model object or null
     */
    public function get($key)
    {
        return $this->storage->offsetGet($key);
    }

    /**
     * We use the '==' operator to compare the data of thes objects, and not the objects themselves.
     * @param string|ModelObject $key
     *
     * @return boolean
     */
    public function contains($keyOrModelObject)
    {
        $key = $this->getIdentityKey($keyOrModelObject);

        if ($key === null) {
            foreach ($this->getIterator() as $object) {
                if ($object == $keyOrModelObject) {
                    return true;
                }
            }
        }

        return $this->storage->offsetExists($key);
    }

    /**
     * Return the identity key
     *
     * @param string|ModelObject $keyOrModelObject
     * @return $string
     */
    private function getIdentityKey($keyOrModelObject)
    {
        if ($keyOrModelObject instanceof ModelObject) {
            return $keyOrModelObject->getIdentityKey();
        }

        return $keyOrModelObject;
    }






    /**
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return count($this->storage) === 0;
    }

    public function clear()
    {
        $this->storage->exchangeArray(array());
        $this->iterator = null;
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current ()
    {
        return $this->getIterator()->current();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next ()
    {
        $this->getIterator()->next();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key ()
    {
        return $this->getIterator()->key();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid ()
    {
        return $this->getIterator()->valid();
    }

    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind ()
    {
        $this->getIterator()->rewind();
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
        $this->storage->uasort(function ($o1, $o2) use ($key) {
            $val1 = $o1->offsetGet($key);
            $val2 = $o2->offsetGet($key);

            if (is_int($val1) === true && is_int($val2) === true) {
                if ($val1 < $val2) {
                    return -1;
                }

                if ($val1 > $val2) {
                    return 1;
                }

                return 0;
            }

            return strcmp($val1, $val2);
        });

        $this->iterator = null;
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
        $intersection = new ModelCollection();

        foreach ($this->getIterator() as $model) {
            if ($collection->contains($model) === true) {
                $intersection->add($model);
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

    public function __clone()
    {
        $this->storage = clone $this->storage;
        $this->iterator = null;

        foreach ($this->storage as $index => $model) {
            $this->storage->offsetSet($index, clone $model);
        }
    }
}
