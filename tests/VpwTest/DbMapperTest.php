<?php
namespace ZfeDataTest;

use Zend\Db\Adapter\ParameterContainer;

use Zend\Db\Adapter\Adapter;

use ZfeDataTest\TestAsset\FooObject;

use ZfeData\Mapper\DbMapper;

use PHPUnit_Framework_TestCase;

class DbMapperTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var DbMapper
     */
    private $mapper;

    /**
     * @var FooObject
     */
    private $object;


    public function setUp()
    {
        $db = new Adapter(
            array(
                'driver' => 'mysqli',
                'hostname' => 'localhost',
                'username' => 'root',
                'database' => 'tests'
            )
        );

        $this->mapper = new DbMapper(
            $db,
            'tests',
            'foo'
        );

        $this->object = new FooObject(
            array(
                'foo' => 'bar',
                'ref' => 'titi'
            )
        );
    }

    public function testInsertStatement()
    {
        $this->object->setId(22);
        $stmt = $this->mapper->getInsertStatement($this->object);
        $this->assertEquals("INSERT INTO `foo` SET `id`=?, `foo`=?, `ref`=?", $stmt->getSql());

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(3, $parameterContainer);

        $this->assertEquals(22, $parameterContainer->offsetGet('id'));
        $this->assertEquals(ParameterContainer::TYPE_INTEGER, $parameterContainer->offsetGetErrata('id'));

        $this->assertEquals('bar', $parameterContainer->offsetGet('foo'));
        $this->assertEquals(ParameterContainer::TYPE_STRING, $parameterContainer->offsetGetErrata('foo'));

        $this->assertEquals('titi', $parameterContainer->offsetGet('ref'));
        $this->assertEquals(ParameterContainer::TYPE_STRING, $parameterContainer->offsetGetErrata('ref'));
    }

    public function testInsertObject()
    {
        $this->object->setId(1);
        $this->assertEquals(1, $this->mapper->insert($this->object));
        $this->assertEquals(1, $this->object->getId());
    }

    /**
     * @expectedException \Zend\Db\Adapter\Exception\RuntimeException
     */
    public function testInsertExistingObject()
    {
        $this->object->setId(1);
        $this->object->setLoaded(true);
        $this->assertEquals(1, $this->mapper->insert($this->object));
    }

    public function testUpdateStatement()
    {
        $this->object->setId(22);
        $stmt = $this->mapper->getUpdateStatement($this->object);
        $this->assertEquals("UPDATE `foo` SET `foo`=?, `ref`=? WHERE `id`=?", $stmt->getSql());

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(3, $parameterContainer);

        $this->assertEquals(22, $parameterContainer->offsetGet('id'));
        $this->assertEquals(ParameterContainer::TYPE_INTEGER, $parameterContainer->offsetGetErrata('id'));

        $this->assertEquals('bar', $parameterContainer->offsetGet('foo'));
        $this->assertEquals(ParameterContainer::TYPE_STRING, $parameterContainer->offsetGetErrata('foo'));

        $this->assertEquals('titi', $parameterContainer->offsetGet('ref'));
        $this->assertEquals(ParameterContainer::TYPE_STRING, $parameterContainer->offsetGetErrata('ref'));
    }

    public function testUpdateObject()
    {
        $this->object->setId(1);
        $this->object->setFoo(rand(0, 100));
        $this->object->setLoaded(true);
        $this->assertEquals(1, $this->mapper->update($this->object));
    }

    public function testUdpateNonExistingObject()
    {
        $this->object->setId(-1);
        $this->object->setFoo('update');
        $this->object->setLoaded(true);
        $this->assertEquals(0, $this->mapper->update($this->object));
    }

    public function testDeleteStatement()
    {
        $this->object->setId(22);
        $this->object->setLoaded(true);

        $stmt = $this->mapper->getDeleteStatement($this->object);
        $this->assertEquals("DELETE FROM `foo` WHERE `id`=?", $stmt->getSql());

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(1, $parameterContainer);

        $this->assertEquals(22, $parameterContainer->offsetGet('id'));
        $this->assertEquals(ParameterContainer::TYPE_INTEGER, $parameterContainer->offsetGetErrata('id'));
    }

    public function testDeleteObject()
    {
        $this->object->setId(1);
        $this->object->setLoaded(true);
        $this->assertEquals(1, $this->mapper->delete($this->object));
    }

    public function testDeleteNonExistingObject()
    {
        $this->object->setId(-1);
        $this->object->setLoaded(true);
        $this->assertEquals(0, $this->mapper->delete($this->object));
    }
}
