<?php
namespace VpwTest\Dal;

use VpwTest\Dal\Asset\FooObject;

use PHPUnit_Framework_TestCase;

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
}
