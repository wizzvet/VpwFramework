<?php
namespace Vpw\Dal;

class IdentityMap extends \ArrayObject implements IdentityMapInterface
{
    public function add(ModelObject $object)
    {
        $this->offsetSet($object->getIdentityKey(), $object);
    }

    public function remove(ModelObject $object)
    {
        $this->offsetUnset($object->getIdentityKey());
    }

    public function contains(ModelObject $object)
    {
        return $this->offsetExists($object->getIdentityKey());
    }
}
