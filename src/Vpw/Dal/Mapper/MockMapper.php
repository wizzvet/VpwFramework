<?php
namespace Vpw\Dal\Mapper;

use Vpw\Dal\IdentityMapInterface;

use Vpw\Dal\IdentityMap;

use Vpw\Dal\ModelObject;

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
     * @param ModelObject $object
     */
    public function save(ModelObject $object)
    {
        if ($this->storage->contains($object) === false) {
            $this->insert($object);
        } else {
            $this->update($object);
        }
    }

    /**
     * @param ModelObject $object
     */
    public function insert(ModelObject $object)
    {
        $this->storage->add($object);
    }

    /**
     * @param ModelObject $object
     */
    public function update(ModelObject $object)
    {
        $this->storage->add($object);
    }

    /**
     * @param ModelObject $object
     */
    public function delete(ModelObject $object)
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
