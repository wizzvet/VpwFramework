<?php
namespace VpwTest\Dal\Asset;

use Vpw\Dal\Mapper\DbMetadata;

use Zend\Db\Metadata\Object\ConstraintObject;

use Zend\Db\Metadata\Object\ColumnObject;

use Vpw\Dal\Mapper\DbMapper;

class FooMapper extends DbMapper
{
    protected $modelObjectClassName = "VpwTest\Dal\Asset\FooObject";

    protected function loadMetadata()
    {
        $fooColumn = new ColumnObject('foo', 'bar', 'buz');
        $fooColumn->setDataType('varchar');

        $refColumn = new ColumnObject('ref', 'bar', 'buz');
        $refColumn->setDataType('varchar');

        $idColumn = new ColumnObject('id', 'bar', 'buz');
        $idColumn->setDataType('varchar');
        $idColumn->setErrata('auto_increment', true);

        $tsColumn = new ColumnObject('update_time', 'bar', 'buz');
        $tsColumn->setDataType('timstamp');
        $tsColumn->setErrata('on_update', 'CURRENT_TIMESTAMP');

        $pkConstraint = new ConstraintObject('pk', 'bar', 'buz');
        $pkConstraint->setType('PRIMARY KEY');
        $pkConstraint->setColumns(array('id'));

        return new DbMetadata(
            array(
                'foo' => $fooColumn,
                'ref' => $refColumn,
                'id' => $idColumn,
                'update_time' => $tsColumn
            ),
            array(
                $pkConstraint
            )
        );
    }
}
