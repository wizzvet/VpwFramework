<?php
/**
 * Package représentant une table HTML : Colonne, En-Tête, ligne, cellule
 *
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */
namespace Vpw\Table;


class Table extends Element
{

    /**
     * Liste des colonnes à afficher
     * @var array|Traversable
     */
    private $columns = array();

    /**
     * Liste des lignes à afficher
     * @var array|Traversable
     */
    private $rows;


    public function __construct($columns = null, $rows = null)
    {
        if ($columns !== null) {
            $this->setColumns($columns);
        }

        if ($rows !== null) {
            $this->setRows($rows);
        }
    }


    public function setColumns($columns)
    {
        if (!is_array($columns) && !$columns instanceof Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable object');
        }

        $this->columns = array();
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
    }


    public function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setRows($rows)
    {
        if (!is_array($rows) && !$rows instanceof \Traversable) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable object');
        }

        $this->rows = $rows;
    }

    public function getRows()
    {
        return $this->rows;
    }
}
