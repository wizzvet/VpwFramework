<?php
namespace Vpw\Dal;

use Zend\Stdlib\ArraySerializableInterface;

class ModelCollection implements \Iterator, \Countable, ArraySerializableInterface
{

    /**
     * @var array
     */
    protected  $storage = array();

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
     * On n'utiliser pas l' "identity key" comme index, car elle peut est être nulle, pour les objects nouvellement
     * créés
     *
     * @param ModelObject $object
     */
    public function add(ModelObject $object)
    {
        if ($this->contains($object) === false) {
            $this->storage[] = $object;
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

        foreach ($this->storage as $index => $object) {
            if ($object->getIdentityKey() === $key) {
                unset($this->storage[$index]);
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
        foreach ($this->storage as $object) {
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

        foreach ($this->storage as $object) {
            if ($object->getIdentityKey() === $key) {
                return true;
            }
        }

        return false;
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


    public function __sleep()
    {
        return array(
            'storage',
            'totalNbRows'
        );
    }
}
