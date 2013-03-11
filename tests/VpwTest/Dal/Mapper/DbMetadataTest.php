<?php
namespace VpwTest\Dal\Mapper;

use Zend\Db\Metadata\Object\ConstraintObject;

use Zend\Db\Metadata\Object\ColumnObject;

use Vpw\Dal\Mapper\DbMetadata;

use PHPUnit_Framework_TestCase;

class DbMetadataTest extends PHPUnit_Framework_TestCase
{

    private $dbMetadata;

    public function setUp()
    {
        $fooColumn = new ColumnObject('foo', 'bar', 'buz');
        $fooColumn->setDataType('varchar');
        $fooColumn->setErrata('auto_increment', true);

        $pkColumn = new ConstraintObject('pk', 'bar', 'buz');
        $pkColumn->setType('PRIMARY KEY');
        $pkColumn->setColumns(array('foo'));

        $this->dbMetadata = new DbMetadata(array('foo' => $fooColumn), array($pkColumn));
    }

    public function testColumns()
    {
        $this->assertEquals(1, sizeof($this->dbMetadata->getColumns()));
    }

    public function testConstraints()
    {
        $this->assertEquals(1, sizeof($this->dbMetadata->getConstraints()));
    }

    public function testGetPrimaryKey()
    {
        $pk = $this->dbMetadata->getPrimaryKey()[0];
        $this->assertEquals('foo', $pk);
    }

    public function testAiColumn()
    {
        $this->assertEquals('foo', $this->dbMetadata->getAutoIncrementColumn()->getName());
    }
}
