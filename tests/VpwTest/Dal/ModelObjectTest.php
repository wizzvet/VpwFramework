<?php
namespace VpwTest\Dal;

use VpwTest\Dal\Asset\FooObject;

use PHPUnit_Framework_TestCase;
use VpwTest\Dal\Asset\Foo2Object;
use Vpw\Dal\ModelCollection;

class ModelObjectTest extends PHPUnit_Framework_TestCase
{

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

    public function testLoad()
    {
        $foo = new FooObject();
        $foo->load(array('foo'=>'bar'));
        $this->assertEquals('bar', $foo->getFoo());
        $this->assertTrue($foo->isLoaded());
    }

    public function testArrayCopy()
    {
        $data = array('foo'=>'bar', 'ref' => null, 'id' => null);
        $foo = new FooObject($data);
        $copy = $foo->getArrayCopy();
        $this->assertEquals($data, $copy);
    }

    public function testDeepArrayCopy()
    {
        $data2 = array('id' => 4, 'foo' => 'barz', 'ref' => 'wizzvet');
        $data = array('foo'=>'bar', 'id' => null);
        $foo = new FooObject($data);
        $foo->setRef(new Foo2Object($data2));

        $copy = $foo->getArrayCopy(true);
        $this->assertTrue(is_array($copy['ref']), 'ref is not an array');
        $this->assertEquals($copy['ref'], $data2);
    }

    public function testDeepArrayCopyWithCollection()
    {
        $data2 = array('id' => 4, 'foo' => 'barz', 'ref' => 'wizzvet');
        $data = array('foo'=>'bar', 'id' => null);
        $foo = new FooObject($data);
        $foo->setRef(new ModelCollection(array(new Foo2Object($data2))));

        $copy = $foo->getArrayCopy(true);
        $this->assertTrue(is_array($copy['ref']), 'ref is not an array');
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
        $this->assertEquals('foo-bar', $foo->getIdentityKey());
    }
}
