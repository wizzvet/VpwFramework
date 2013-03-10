<?php
namespace ZfeDataTest\TestAsset;

use ZfeData\DataObject;

class FooObject extends DataObject
{
    protected $foo;

    protected $ref;

    protected $id;


    /**
    * @param int $id
    * @return FooObject
    */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
    * @return int
    */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $foo
     */
    public function setFoo($foo)
    {
        $this->foo = $foo;
    }

    /**
     * @return string
     */
    public function getFoo()
    {
        return $this->foo;
    }


    /**
    * @param string $ref
    */
    public function setRef($ref)
    {
        $this->ref = $ref;
        return $this;
    }

    /**
    * @return string
    */
    public function getRef()
    {
        return $this->ref;
    }


    /**
     * (non-PHPdoc)
     * @see \ZfeData\Mapper\DataObject::getIdentityField()
     */
    public function getIdentityField()
    {
        return $this->getFoo();
    }
}
