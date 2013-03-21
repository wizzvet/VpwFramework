<?php
namespace VpwTest\Dal;

use VpwTest\Dal\Asset\MockResult;

use VpwTest\Dal\Asset\FooObject;

use Vpw\Dal\ModelCollection;

use PHPUnit_Framework_TestCase;

class ModelCollectionTest extends PHPUnit_Framework_TestCase
{
    private $collection;

    private $data = array(
        array(
            'foo' => 'bar',
            'ref' => 'A',
            'id' => null
        ),

        array(
            'foo' => 'bar2',
            'ref' => 'B',
            'id' => null
        ),
    );

    public function setUp()
    {
        $this->collection = new ModelCollection(new FooObject());
        $this->collection->initialize(new MockResult($this->data));
    }


    public function testCount()
    {
        $this->assertCount(2, $this->collection);
    }

    public function testIteration()
    {
        $this->collection->rewind();
        $o = $this->collection->current();
        $this->assertInstanceOf('\Vpw\Dal\ModelObject', $o);
        $this->assertEquals('bar', $o->getFoo());

        $this->collection->next();
        $o = $this->collection->current();
        $this->assertInstanceOf('\VpwTest\Dal\Asset\FooObject', $o);
        $this->assertEquals('bar2', $o->getFoo());
    }

    public function testArrayCopy()
    {
        $this->assertEquals($this->collection->getArrayCopy(), $this->data);

    }
}
