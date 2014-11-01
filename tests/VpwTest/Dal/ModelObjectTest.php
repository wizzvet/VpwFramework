<?php
namespace VpwTest\Dal;

use VpwTest\Dal\Asset\FooObject;

use PHPUnit_Framework_TestCase;
use VpwTest\Dal\Asset\Foo2Object;
use Vpw\Dal\ModelCollection;

class ModelObjectTest extends PHPUnit_Framework_TestCase
{
    const MEMBER = 4;

    const USER = 8;

    const EXPERT = 16;



    public function testEmptyConstruct()
    {
        $foo = new FooObject();
        $this->assertNull($foo->getFoo());
        $this->assertFalse($foo->isLoaded());
    }

    public function testArrayConstruct()
    {
        $foo = new FooObject(array('foo'=>'bar'));
        $this->assertEquals('bar', $foo->getFoo());
        $this->assertFalse($foo->isLoaded());
    }

    public function testHydrator()
    {
        $data = array('foo'=>'bar', 'ref' => null, 'id' => null);
        $foo = new FooObject($data);
        $this->assertInstanceOf('\Zend\Stdlib\Hydrator\ClassMethods', $foo->getHydrator());
    }

    public function testScalarIdentity()
    {
        $data = array('foo'=>'bar', 'ref' => null, 'id' => null);
        $foo = new FooObject($data);
        $this->assertEquals($foo->getIdentity(), $data['foo']);
    }

    public function testScalarIdentityKey()
    {
        $data = array('foo'=>'bar', 'ref' => null, 'id' => null);
        $foo = new FooObject($data);
        $this->assertEquals($foo->getIdentityKey(), $data['foo']);
    }

    public function testArrayIdentityKeyWithNullPart()
    {
        $data = array('foo'=>'bar', 'ref' => null, 'id' => null);
        $foo = new Foo2Object($data);
        $this->assertNull($foo->getIdentityKey());
    }

    public function testArrayIdentityKey()
    {
        $data = array('foo'=>'foo', 'ref' => 'bar', 'id' => null);
        $foo = new Foo2Object($data);
        $this->assertEquals('id-foo-bar', $foo->getIdentityKey());
    }

    public function testAddFlags()
    {
        $foo = new FooObject();
        $foo->addFlags(self::MEMBER);
        $this->assertTrue($foo->hasFlags(self::MEMBER));
        $this->assertFalse($foo->hasFlags(self::USER));
    }

    public function testChangeFlags()
    {
        $foo = new FooObject();
        $foo->setFlags(self::USER);
        $foo->addFlags(self::MEMBER);

        $this->assertTrue($foo->hasFlags(self::USER));
        $this->assertTrue($foo->hasFlags(self::MEMBER));
        $this->assertFalse($foo->hasFlags(self::EXPERT));
    }

    public function testReplaceFlags()
    {
        $foo = new FooObject();
        $foo->addFlags(self::MEMBER);
        $foo->setFlags(self::EXPERT);

        $this->assertFalse($foo->hasFlags(self::USER));
        $this->assertFalse($foo->hasFlags(self::MEMBER));
        $this->assertTrue($foo->hasFlags(self::EXPERT));
        $this->assertEquals(self::EXPERT, $foo->getFlags());
    }

}
