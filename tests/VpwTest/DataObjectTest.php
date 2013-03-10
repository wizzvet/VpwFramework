<?php
namespace ZfeDataTest;

use ZfeDataTest\TestAsset\FooObject;

use PHPUnit_Framework_TestCase;

class DataObjectTest extends PHPUnit_Framework_TestCase
{

    public function testEmptyConstruct()
    {
        $foo = new FooObject();
        $this->assertNull($foo->getFoo());
    }

    public function testArrayConstruct()
    {
        $foo = new FooObject(array('foo'=>'bar'));
        $this->assertEquals('bar', $foo->getFoo());
    }
}
