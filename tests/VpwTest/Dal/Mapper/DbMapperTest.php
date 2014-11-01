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
                'update_time' => '2014-09-23 10:42:20',
                'id' => 22
            )
        );
    }

    public function testInsertStatement()
    {
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
        $stmt = $this->mapper->getUpdateStatement($this->object);
        $this->assertEquals("UPDATE `foo` SET `ref` = ?, `id` = ? WHERE `foo` = ?", $stmt->getSql());

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(3, $parameterContainer);

        $this->assertEquals('bar', $parameterContainer->offsetGet('where1'));
        $this->assertEquals(22, $parameterContainer->offsetGet('id'));
        $this->assertEquals('titi', $parameterContainer->offsetGet('ref'));
    }

    public function testDeleteStatement()
    {
        $this->object->setLoaded(true);

        $stmt = $this->mapper->getDeleteStatement($this->object);
        $this->assertEquals("DELETE FROM `foo` WHERE `foo` = ?", $stmt->getSql());

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(1, $parameterContainer);
        $this->assertEquals('bar', $parameterContainer->offsetGet('where1'));
    }

    public function testInsertOnDuplicatePrimaryKeyUpdateInsertStatement()
    {
        $stmt = $this->mapper->getInsertOnDuplicatePrimaryKeyUpdateInsertStatement($this->object);
        $this->assertEquals(
            "INSERT INTO `foo` SET `foo` = ?, `ref` = ?, `id` = ? ON DUPLICATE KEY UPDATE `ref` = ?, `id` = ?",
            $stmt->getSql()
        );

        $parameterContainer = $stmt->getParameterContainer();

        $this->assertCount(5, $parameterContainer);
    }
}
