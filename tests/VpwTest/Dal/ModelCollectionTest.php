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
            'foo' => null,
            'ref' => 'A',
            'id' => 16357,
            'update_time' => null,
            'object' => null
        ),

        array(
            'foo' => 'bar2',
            'ref' => 'B',
            'id' => 2146,
            'update_time' => '2014-09-23 10:42:00',
            'object' => null
        ),

        array(
            'foo' => 'bar3',
            'ref' => 'C',
            'id' => 489,
            'update_time' => '2014-09-22 10:42:00',
            'object' => null
        )
    );

    public function setUp()
    {
        $this->object0 = new FooObject($this->data[0]);
        $this->object2 = new FooObject($this->data[2]);
        $this->object1 = new FooObject($this->data[1]);

        $this->collection = new ModelCollection();
        $this->collection->add($this->object0);
        $this->collection->add($this->object1);
        $this->collection->add($this->object2);
    }


    public function testExchangeArray()
    {
        $this->collection->exchangeArray(array($this->object2));

        $this->assertCount(1, $this->collection);
    }


    public function testCount()
    {
        $this->assertCount(3, $this->collection);
    }

    public function testIteration()
    {
        $this->collection->rewind();
        $o = $this->collection->current();
        $this->assertInstanceOf('\Vpw\Dal\ModelObject', $o);
        $this->assertEquals(null, $o->getFoo());

        $this->collection->next();
        $o = $this->collection->current();
        $this->assertInstanceOf('\VpwTest\Dal\Asset\FooObject', $o);
        $this->assertEquals('bar2', $o->getFoo());

        $this->collection->next();
        $o = $this->collection->current();
        $this->assertInstanceOf('\VpwTest\Dal\Asset\FooObject', $o);
        $this->assertEquals('bar3', $o->getFoo());
    }

    public function testArrayCopy()
    {
        $this->assertEquals($this->collection->getArrayCopy(), $this->data);
    }

    public function testRemoveObject()
    {
        $objectRemoved = $this->collection->remove($this->object0);
        $this->assertEquals($this->object0, $objectRemoved);
        $this->assertCount(2, $this->collection);

        $objectRemoved = $this->collection->remove($this->object1->getIdentityKey());
        $this->assertEquals($this->object1, $objectRemoved);
        $this->assertCount(1, $this->collection);

        $this->assertNull($this->collection->remove("une cle qui n'existe pas"));
    }

    public function testContainsObject()
    {
        $this->assertTrue($this->collection->contains($this->object0));
        $this->assertTrue($this->collection->contains($this->object1->getIdentityKey()));
        $this->assertFalse($this->collection->contains("une cle qui n'existe pas"));
    }


    public function testGet()
    {
        $this->assertEquals($this->object1, $this->collection->get($this->object1->getIdentityKey()));
    }


    public function testDiff()
    {
        $newCollection = new ModelCollection();
        $newCollection->add(clone $this->object0);
        $newCollection->add(clone $this->object1);

        $diff = $this->collection->diff($newCollection);
        $this->assertCount(1, $diff);
        $this->assertEquals('bar3', $diff[0]->getFoo());
    }


    public function testIntersect()
    {
        $newCollection = new ModelCollection();
        $newCollection->add(clone $this->object1);
        $newCollection->add(clone $this->object2);

        $intersection = $newCollection->intersect($this->collection);
        $this->assertCount(2, $intersection);
        $this->assertEquals('bar2', $intersection->get($this->object1->getIdentityKey())->getFoo());
        $this->assertEquals('bar3', $intersection->get($this->object2->getIdentityKey())->getFoo());
    }


    public function testClone()
    {
        $clonedCollection = clone $this->collection;

        $this->assertFalse($clonedCollection === $this->collection);
        $this->assertFalse($clonedCollection->get('bar2') === $this->collection->get('bar2'), 'Objects have the same ref');
        $this->assertTrue($clonedCollection->get('bar2') == $this->collection->get('bar2'), 'Objects are not identical');
    }


    public function testIdentity()
    {
        $this->assertEquals(
            array(
                $this->object0->getIdentity(),
                $this->object1->getIdentity(),
                $this->object2->getIdentity(),
            ),
            $this->collection->getIdentity()
        );
    }

    public function testEmptyCollectionIdentity()
    {
        $collection = new ModelCollection();
        $this->assertNull($collection->getIdentity());
    }


    public function testNumericSortCollection()
    {
        $this->collection->sort('id');

        $ids = array();
        foreach ($this->collection as $model) {
            $ids[] = $model->getId();
        }

        $this->assertEquals(array(489, 2146, 16357), $ids);
    }


    public function testStringSortCollection()
    {
        $this->collection->sort('ref');

        $refs = array();
        foreach ($this->collection as $model) {
            $refs[] = $model->getRef();
        }

        $this->assertEquals(array('A', 'B', 'C'), $refs);
    }
}
