<?php
namespace Vpw\Form\Fieldset;

/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 * Created : 11 avr. 2013
 * Encoding : UTF-8
 */

use Zend\Form\Fieldset;
use Vpw\Dal\Mapper\MetadataInterface;
use Zend\InputFilter\InputFilterProviderInterface;
use Zend\Db\Metadata\Object\ColumnObject;

class MetadataBasedFieldset extends Fieldset implements InputFilterProviderInterface
{

    private $metadata;

    public function __construct(MetadataInterface $metadata, $name = 'data')
    {
        parent::__construct($name);

        $this->metadata = $metadata;

        foreach ($this->metadata->getColumns() as $name => $column) {
            $this->add($this->getElementSpec($column));
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\InputFilter\InputFilterProviderInterface::getInputFilterSpecification()
     */
    public function getInputFilterSpecification()
    {
        $spec = array();

        foreach ($this->getElements() as $name => $element) {
            $spec[$name] = $this->getElementFilterSpec($this->metadata->getColumn($name));
        }

        return $spec;
    }

    /**
     *
     * @param  ColumnObject $column
     * @return array
     */
    private function getElementSpec(ColumnObject $column)
    {
        if ($column->getErrata('auto_increment') === true) {
            return array(
                'name' => $column->getName(),
                'type' => 'Zend\Form\Element\Hidden'
            );
        }

        $spec = array(
            'name' => $column->getName(),
            'attributes' => array('class' => 'input-medium'),
            'options' => array(
                'label' => $column->getName(),
            )
        );

        switch ($column->getDataType()) {
            case 'tinyint':
            case 'smallint':
            case 'bigint':
            case 'mediumint':
                $spec['type'] = 'Zend\Form\Element\Number';
                $spec['attributes']['class'] = 'input-medium';
                break;
            default:
            case 'varchar':
                $spec['type'] = 'Zend\Form\Element\Text';

                if ($column->getCharacterMaximumLength() < 50) {
                    $spec['attributes']['class'] = 'input-medium';
                } elseif ($column->getCharacterMaximumLength() < 100) {
                    $spec['attributes']['class'] = 'input-large';
                } elseif ($column->getCharacterMaximumLength() < 150) {
                    $spec['attributes']['class'] = 'input-xlarge';
                } else {
                    $spec['attributes']['class'] = 'input-xxlarge';
                }
                break;
            case 'text':
            case 'blob':
                $spec['type'] = 'Zend\Form\Element\Textarea';
                $spec['attributes']['class'] = 'input-xxlarge';
                break;
        }

        return $spec;
    }

    /**
     *
     * @param  ColumnObject $column
     * @return array        array of spec
     */
    private function getElementFilterSpec(ColumnObject $column)
    {
        $spec = array(
            'name' => $column->getName(),
            'required' => true,
            'allow_empty' => $column->getErrata('auto_increment'),
            'filters' => array(
                array(
                    'name' => 'stringtrim'
                ),
                array(
                    'name' => 'striptags'
                ),
             )
        );

        if ($column->isNullable() === true) {
            $spec['filters'][] = array(
                'name' => 'null'
            );
        }

        return $spec;
    }
}
