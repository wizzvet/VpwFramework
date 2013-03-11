<?php
namespace VpwTest\Dal\Mapper;

use VpwTest\Dal\Asset\FooObject;

use Vpw\Dal\Mapper\MockMapper;

use Zend\Http\Client\Adapter\Test;

use PHPUnit_Framework_TestCase;

class MockMapperTest extends PHPUnit_Framework_TestCase
{

    public function testEmptyConstruct()
    {
        $mapper = new MockMapper();
        $this->assertCount(0, $mapper->getStorage());
    }

    public function testAddObjects()
    {
        $mapper = new MockMapper();

        $mapper->save(
            new FooObject(
                array(
                    'foo' => 'bar',
                    'ref' => 'A'
                )
            )
        );

        $mapper->save(
            new FooObject(
                array(
                    'foo' => 'bar2',
                    'ref' => 'B'
                )
            )
        );

        $this->assertCount(2, $mapper->getStorage());
    }


    public function testAddIdenticalObjects()
    {
        $mapper = new MockMapper();

        $mapper->save(
            new FooObject(
                array(
                    'foo' => 'bar',
                    'ref' => 'A'
                )
            )
        );

        $mapper->save(
            new FooObject(
                array(
                    'foo' => 'bar',
                    'ref' => 'A'
                )
            )
        );

        $this->assertCount(1, $mapper->getStorage());
    }


    public function testAddSameObject()
    {
        $object = new FooObject(
            array(
                'foo' => 'bar',
                'ref' => 'A'
            )
        );
        $mapper = new MockMapper();

        $mapper->save($object);
        $mapper->save($object);

        $this->assertCount(1, $mapper->getStorage());
    }


    public function testAddObjectsWithIdenticalFieldIdentity()
    {
        $mapper = new MockMapper();

        $mapper->save(
            new FooObject(
                array(
                    'foo' => 'bar',
                    'ref' => 'A'
                )
            )
        );

        $mapper->save(
            new FooObject(
                array(
                    'foo' => 'bar',
                    'ref' => 'B'
                )
            )
        );

        $storage = $mapper->getStorage();
        $this->assertCount(1, $storage);

        $objects = $storage->getIterator();
        $objects->rewind();
        $this->assertEquals('B', $objects->current()->getRef());
    }


    public function testDeleteObjects()
    {
        $mapper = new MockMapper();

        $object1 = new FooObject(
            array(
                'foo' => 'bar',
                'ref' => 'A'
            )
        );

        $object2 = clone $object1;
        $object2->setFoo('bar2');
        $object2->setRef('toto');

        $object3 = clone $object2;
        $object3->setFoo('bar3');
        $object3->setRef('titi');

        $mapper->save($object1);
        $mapper->save($object2);
        $mapper->save($object3);

        $this->assertCount(3, $mapper->getStorage());

        $mapper->delete($object1);
        $this->assertCount(2, $mapper->getStorage());

        $objects = $mapper->getStorage()->getIterator();
        $this->assertEquals('toto', $objects->current()->getRef());
    }
}
