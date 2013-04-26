<?php
namespace VpwTest\Dal;

use Zend\Db\Adapter\Adapter;

use VpwTest\Dal\Asset\FooMapper;

use VpwTest\Dal\Asset\MockResult;

use VpwTest\Dal\Asset\FooObject;

use Vpw\Dal\ModelCollection;

use PHPUnit_Framework_TestCase;

class ModelCollectionTest extends PHPUnit_Framework_TestCase
{
    private $collection;

    private $object0;

    private $object1;

    private $object2;

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
        $this->object0 = new FooObject($this->data[0]);
        $this->object1 = new FooObject($this->data[1]);
        $this->object2 = new FooObject(
            array(
                'foo' => 'bar3',
                'ref' => 'C',
                'id' => null
            )
        );

        $this->collection = new ModelCollection();
        $this->collection->add($this->object0);
        $this->collection->add($this->object1);
    }


    public function testExchangeArray()
    {
        $this->collection->exchangeArray(array($this->object2));

        $this->assertCount(1, $this->collection);
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

    public function testRemoveObject()
    {
        $objectRemoved = $this->collection->remove($this->object0);
        $this->assertEquals($this->object0, $objectRemoved);
        $this->assertCount(1, $this->collection);

        $objectRemoved = $this->collection->remove($this->object1->getIdentityKey());
        $this->assertEquals($this->object1, $objectRemoved);
        $this->assertCount(0, $this->collection);

        $this->assertNull($this->collection->remove($this->object2->getIdentityKey()));
    }

    public function testContainsObject()
    {
        $this->assertTrue($this->collection->contains($this->object0));
        $this->assertTrue($this->collection->contains($this->object1->getIdentityKey()));
        $this->assertFalse($this->collection->contains($this->object2->getIdentityKey()));
    }


    public function testGet()
    {
        $this->assertEquals($this->object0, $this->collection->get($this->object0->getIdentityKey()));
    }
}
