<?php
namespace VpwTest\Dal\Mapper;

use VpwTest\Dal\Asset\FooMapper;

use VpwTest\Dal\Asset\FooObject;

use Vpw\Dal\Mapper\DbMapper;

use Zend\Db\Adapter\ParameterContainer;

use Zend\Db\Adapter\Adapter;

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
                'username' => 'tdpr-admin',
                'database' => 'tdpr_test',
                'password' => 'neric'
            )
        );

        $this->mapper = new FooMapper(
            $db,
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
