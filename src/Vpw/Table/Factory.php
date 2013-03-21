<?php
namespace Vpw\Table;

use Vpw\Dal\Mapper\DbMetadata;

class Factory
{

    public function createTable(DbMetadata $metadata)
    {
        $columns = array();

        foreach ($metadata->getColumns() as $metaColumn) {
            $column = new Column($metaColumn->getName());
            $column->setLabel($metaColumn->getName());

            $columns[] = $column;
        }

        $table = new Table($columns);
        $table->setAttribute('class', 'table');

        return $table;
    }

}