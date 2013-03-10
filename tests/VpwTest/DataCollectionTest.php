<?php
namespace ZfeDataTest;

use ZfeDataTest\TestAsset\MockResult;

use ZfeDataTest\TestAsset\FooObject;

use ZfeData\DataCollection;

use PHPUnit_Framework_TestCase;

class DataCollectionTest extends PHPUnit_Framework_TestCase
{
    private $collection;

    public function setUp()
    {
        $this->collection = new DataCollection(new FooObject());
        $this->collection->initialize(
            new MockResult(
                array(
                    array(
                        'foo' => 'bar',
                        'ref' => 'A'
                    ),

                    array(
                        'foo' => 'bar2',
                        'ref' => 'B'
                    ),
                )
            )
        );
    }


    public function testCount()
    {
        $this->assertCount(2, $this->collection);
    }

    public function testIteration()
    {
        $this->collection->rewind();
        $o = $this->collection->current();
        $this->assertInstanceOf('\ZfeData\DataObject', $o);
        $this->assertEquals('bar', $o->getFoo());

        $this->collection->next();
        $o = $this->collection->current();
        $this->assertInstanceOf('\ZfeDataTest\TestAsset\FooObject', $o);
        $this->assertEquals('bar2', $o->getFoo());
    }
}
