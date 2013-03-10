<?php
namespace Vpw\DataSource\Mapper;


use Vpw\DataSource\AbstractObject;

use Vpw\DataSource\IdentityMap;

use Vpw\DataSource\IdentityMapInterface;

class MockMapper implements DataMapperInterface
{

    /**
     * @var IdentityMap
     */
    private $storage;

    public function __construct(IdentityMapInterface $storage = null)
    {
        if ($storage === null) {
            $storage = new IdentityMap();
        }
        $this->storage = $storage;
    }

    /**
     * @param AbstractObject $object
     */
    public function save(AbstractObject $object)
    {
        if ($this->storage->contains($object) === false) {
            $this->insert($object);
        } else {
            $this->update($object);
        }
    }

    /**
     * @param AbstractObject $object
     */
    public function insert(AbstractObject $object)
    {
        $this->storage->add($object);
    }

    /**
     * @param AbstractObject $object
     */
    public function update(AbstractObject $object)
    {
        $this->storage->add($object);
    }

    /**
     * @param AbstractObject $object
     */
    public function delete(AbstractObject $object)
    {
        $this->storage->remove($object);
    }


    /**
     * @return IdentityMapInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }
}
