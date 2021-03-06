<?php
/**
 * Mysql meta data source avec une fonctionnalité en plus que celle du ZF2 :
 *  - Indique via le champ "erratas", si une colonne à le flag "AUTO_INCREMENT"
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 */

namespace Vpw\Db\Metadata\Source;

use Zend\Db\Adapter\Adapter;

class MysqlMetadata extends \Zend\Db\Metadata\Source\MysqlMetadata
{

    protected function loadColumnData($table, $schema)
    {
        if (isset($this->data['columns'][$schema][$table])) {
            return;
        }
        $this->prepareDataHierarchy('columns', $schema, $table);
        $p = $this->adapter->getPlatform();

        $isColumns = array(
            array('C','ORDINAL_POSITION'),
            array('C','COLUMN_DEFAULT'),
            array('C','IS_NULLABLE'),
            array('C','DATA_TYPE'),
            array('C','CHARACTER_MAXIMUM_LENGTH'),
            array('C','CHARACTER_OCTET_LENGTH'),
            array('C','NUMERIC_PRECISION'),
            array('C','NUMERIC_SCALE'),
            array('C','COLUMN_NAME'),
            array('C','COLUMN_TYPE'),
            array('C','EXTRA'),
        );

        array_walk(
            $isColumns,
            function (&$c) use ($p) {
                $c = $p->quoteIdentifierChain($c);
            }
        );

        $sql = 'SELECT ' . implode(', ', $isColumns)
             . ' FROM ' . $p->quoteIdentifierChain(array('INFORMATION_SCHEMA','TABLES')) . 'T'
             . ' INNER JOIN ' . $p->quoteIdentifierChain(array('INFORMATION_SCHEMA','COLUMNS')) . 'C'
             . ' ON ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
             . '  = ' . $p->quoteIdentifierChain(array('C','TABLE_SCHEMA'))
             . ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_NAME'))
             . '  = ' . $p->quoteIdentifierChain(array('C','TABLE_NAME'))
             . ' WHERE ' . $p->quoteIdentifierChain(array('T','TABLE_TYPE'))
             . ' IN (' . $p->quoteValueList(array('BASE TABLE', 'VIEW')) . ')'
             . ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_NAME'))
             . '  = ' . $p->quoteValue($table);

        if ($schema != self::DEFAULT_SCHEMA) {
            $sql .= ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
                  . ' = ' . $p->quoteValue($schema);
        } else {
            $sql .= ' AND ' . $p->quoteIdentifierChain(array('T','TABLE_SCHEMA'))
                  . ' != ' . $p->quoteValue('INFORMATION_SCHEMA');
        }

        $results = $this->adapter->query($sql, Adapter::QUERY_MODE_EXECUTE);
        $columns = array();
        foreach ($results->toArray() as $row) {
            $erratas = array();
            $erratas['auto_increment'] = (strpos($row['EXTRA'], 'auto_increment') !== false);

            if (stripos($row['EXTRA'], 'on update CURRENT_TIMESTAMP') !== false) {
                $erratas['on_update'] = 'CURRENT_TIMESTAMP';
            }

            $matches = array();
            if (preg_match('/^(?:enum|set)\((.+)\)$/i', $row['COLUMN_TYPE'], $matches)) {
                $permittedValues = $matches[1];
                if (
                    preg_match_all(
                        "/\\s*'((?:[^']++|'')*+)'\\s*(?:,|\$)/",
                        $permittedValues,
                        $matches,
                        PREG_PATTERN_ORDER
                    )
                ) {
                    $permittedValues = str_replace("''", "'", $matches[1]);
                } else {
                    $permittedValues = array($permittedValues);
                }
                $erratas['permitted_values'] = $permittedValues;
            }
            $columns[$row['COLUMN_NAME']] = array(
                'ordinal_position'          => $row['ORDINAL_POSITION'],
                'column_default'            => $row['COLUMN_DEFAULT'],
                'is_nullable'               => ('YES' == $row['IS_NULLABLE']),
                'data_type'                 => $row['DATA_TYPE'],
                'character_maximum_length'  => $row['CHARACTER_MAXIMUM_LENGTH'],
                'character_octet_length'    => $row['CHARACTER_OCTET_LENGTH'],
                'numeric_precision'         => $row['NUMERIC_PRECISION'],
                'numeric_scale'             => $row['NUMERIC_SCALE'],
                'numeric_unsigned'          => (false !== strpos($row['COLUMN_TYPE'], 'unsigned')),
                'erratas'                   => $erratas,
            );
        }

        $this->data['columns'][$schema][$table] = $columns;
    }
}
