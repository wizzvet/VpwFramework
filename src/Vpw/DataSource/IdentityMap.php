<?php
namespace Vpw\DataSource;

class IdentityMap extends \ArrayObject implements IdentityMapInterface
{
    public function add(DataObject $object)
    {
        $this->offsetSet($object->getIdentityKey(), $object);
    }

    public function remove(DataObject $object)
    {
        $this->offsetUnset($object->getIdentityKey());
    }

    public function contains(DataObject $object)
    {
        return $this->offsetExists($object->getIdentityKey());
    }
}
