<?php
namespace Vpw\Table\View\Helper;

use Zend\Stdlib\ArraySerializableInterface;

use Zend\View\Helper\AbstractHtmlElement;

use Zend\View\Helper\AbstractHelper;

class Table extends AbstractHtmlElement
{

    /**
     *
     * @var Vpw\Table\Table
     */
    private $table;

    /**
     *
     * @param Table $table
     * @return \Vpw\Table\Table
     */
    public function __invoke(\Vpw\Table\Table $table=null)
    {
        if ($table !== null) {
            $this->setTable($table);
        }
        return $this;
    }

    /**
     *
     * @param Table $table
     * @return \Vpw\Table\Table
     */
    public function setTable(\Vpw\Table\Table $table)
    {
        $this->table = $table;
        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            echo $e;
        }
    }

    public function render()
    {
        $html = '<table' . $this->htmlAttribs($this->table->getAttributes()) . '>' . PHP_EOL .
        $this->getHeaderRendering() . PHP_EOL .
        $this->getFooterRendering() . PHP_EOL .
        $this->getBodyRendering() . PHP_EOL .
        '</table>';

        return $html;
    }

    public function getBodyRendering()
    {
        $body = '<tbody>'.PHP_EOL;

        foreach ($this->table->getRows() as $row) {
            $body .= $this->getRowRendering($row);
        }

        $body .= '</tbody>'.PHP_EOL;

        return $body;
    }


    public function getRowRendering($row)
    {
        $html = '<tr>'.PHP_EOL;

        if ($row instanceof ArraySerializableInterface) {
            $row = $row->getArrayCopy();
        }

        foreach ($this->table->getColumns() as $column) {
            $html .= '<td' . $this->htmlAttribs($column->getAttributes()) . '>';

            $data = (array_key_exists($column->getName(), $row) ? $row[$column->getName()] : $row);

            if ($column->hasTemplate() === true) {
                $html .= $this->view->render($column->getTemplate(), $data);
            } else {
                $helper = $column->getHelper();

                if (is_string($helper)) {
                    $helper = $this->view->plugin($helper);
                }

                $html .= $helper($data);
            }

            $html .= '</td>';
        }

        $html .= '</tr>'.PHP_EOL;

        return $html;
    }


    public function getHeaderRendering()
    {
        $escaper = $this->view->plugin('escapehtml');

        $thead = '<thead>'.PHP_EOL;

        $thead .= '<tr>'.PHP_EOL;

        foreach ($this->table->getColumns() as $column) {
            $thead .= '<th>' . $escaper($column->getLabel()) . '</th>'.PHP_EOL;
        }

        $thead .= '</tr>'.PHP_EOL;

        $thead .= '</thead>'.PHP_EOL;

        return $thead;
    }

    public function getFooterRendering()
    {
        return '';
    }
}