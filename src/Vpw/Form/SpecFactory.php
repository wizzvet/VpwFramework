<?php
/**
 * Permet de créer les specifications de chaque type de formulaire : edit & delete,
 * à partir de Meta données
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */
namespace Vpw\Form;

use Zend\Filter\Word\UnderscoreToCamelCase;

use Zend\Db\Metadata\Object\ColumnObject;

use Vpw\Dal\Mapper\DbMetadata;

class SpecFactory
{
    /**
     *
     * @var DbMetadata
     */
    private $metadata;

    /**
     * Filter used to create label
     * @var UnderscoreToCamelCase
     */
    private $filter;

    public function __construct(DbMetadata $metadata)
    {
        $this->metadata = $metadata;
        $this->filter = new UnderscoreToCamelCase();
    }


    public function getFormSpec($type) {
        if ($type === 'add') {
            return $this->getAddFormSpec();
        }

        if ($type === 'edit') {
            return $this->getEditFormSpec();
        }

        if ($type === 'delete') {
            return $this->getDeleteFormSpec();
        }
    }

    public function getEditFormSpec()
    {
        $spec = $this->getDefaultFormSpec();

        foreach ($this->metadata->getColumns() as $name => $column) {
            $spec['elements'][] = array(
                'spec' => $this->getElementSpec($column)
            );

            $spec['input_filter'][$column->getName()] = $this->getFilterSpec($column);
        }

        return $spec;
    }


    public function getAddFormSpec()
    {
        $spec = $this->getDefaultFormSpec();


        foreach ($this->metadata->getColumns() as $name => $column) {

            $spec['elements'][] = array(
                'spec' => $this->getElementSpec($column)
            );

            $spec['input_filter'][$column->getName()] = $this->getFilterSpec($column);

            //Cas Spécial Auto Increment fields => allow empty value
            if ($column->getErrata('auto_increment') !== null) {
                $spec['input_filter'][$column->getName()]['allow_empty'] = true;
            }
        }

        return $spec;
    }


    public function getDefaultFormSpec()
    {
        return array(
            'name' => 'foo',
            'attributes' => array('method' => 'post', 'class' => 'form-horizontal'),
            'hydrator' => 'Zend\Stdlib\Hydrator\ArraySerializable',
            'elements' => array(),
            'input_filter' => array()
        );
    }


    public function getElementSpec(ColumnObject $column)
    {
        $spec = array(
            'name' => $column->getName(),
            'attributes' => array('class' => 'input-medium'),
            'options' => array(
                'label' => $this->filter->filter($column->getName()),
            )
        );

        switch($column->getDataType()) {
            case 'tinyint':
            case 'smallint':
            case 'bigint':
            case 'mediumint':
                $spec['type'] = 'Zend\Form\Element\Number';
                $spec['attributes']['class'] = 'input-medium';
                break;

            case 'text':
            case 'blob':
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
        }

        return $spec;
    }


    public function getFilterSpec(ColumnObject $column)
    {
        return array(
            'name' => $column->getName(),
            'required' => true,
            'allow_empty' => false,
            'filters' => array(
                array(
                    'name' => 'stringtrim'
                ),
                array(
                    'name' => 'striptags'
                )
             )
        );
    }

}