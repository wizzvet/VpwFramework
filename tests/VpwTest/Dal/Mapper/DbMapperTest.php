<?php
namespace VpwTest\Dal\Mapper;

use VpwTest\Dal\Asset\FooMapper;

use VpwTest\Dal\Asset\FooObject;

use Vpw\Dal\Mapper\DbMapper;

use Zend\Db\Adapter\ParameterContainer;

use Zend\Db\Adapter\Adapter;

use PHPUnit_Framework_TestCase;
use Zend\Db\Adapter\Driver\Mysqli\Statement;

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
        $driver = $this->getMockBuilder('Zend\Db\Adapter\Driver\Mysqli\Mysqli')
            ->disableOriginalConstructor()
            ->getMock();

        $driver->method('createStatement')->willReturn(new Statement());
        $driver->method('formatParameterName')->willReturn('?');

        $db = new Adapter($driver, new \Zend\Db\Adapter\Platform\Mysql());

        $this->mapper = new FooMapper(
            $db,
            'foo'
        );

        $this->object = new FooObject(
            array(
                'foo' => 'bar',
                'ref' => 'titi',
                'update_time' => '2014-09-23 10:42:20'
            )
        );
    }

    public function testInsertStatement()
    {
        $this->object->setId(22);
        $stmt = $this->mapper->getInsertStatement($this->object);
        $this->assertEquals("INSERT INTO `foo` (`foo`, `ref`, `id`) VALUES (?, ?, ?)", $stmt->getSql());

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(3, $parameterContainer);

        $this->assertEquals(22, $parameterContainer->offsetGet('id'));
        $this->assertEquals('bar', $parameterContainer->offsetGet('foo'));
        $this->assertEquals('titi', $parameterContainer->offsetGet('ref'));
    }


    public function testUpdateStatement()
    {
        $this->object->setId(22);
        $stmt = $this->mapper->getUpdateStatement($this->object);
        $this->assertEquals("UPDATE `foo` SET `foo` = ?, `ref` = ? WHERE `id` = ?", $stmt->getSql());

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(3, $parameterContainer);

        $this->assertEquals(22, $parameterContainer->offsetGet('where1'));
        $this->assertEquals('bar', $parameterContainer->offsetGet('foo'));
        $this->assertEquals('titi', $parameterContainer->offsetGet('ref'));
    }

    public function testDeleteStatement()
    {
        $this->object->setId(22);
        $this->object->setLoaded(true);

        $stmt = $this->mapper->getDeleteStatement($this->object);
        $this->assertEquals("DELETE FROM `foo` WHERE `id` = ?", $stmt->getSql());

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(1, $parameterContainer);

        $this->assertEquals(22, $parameterContainer->offsetGet('where1'));
    }
}
