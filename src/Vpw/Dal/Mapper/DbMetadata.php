<?php
/**
 *
 * Je n'utilise pas les classes car il ne faut que l'on puisse générer dynamiquement les metadata.
 * Ca evité aussi de charger toute une arborescence de fichier.
 * C'est aussi plus simple de sérialiser cet objet que l'oject Meta du zf2
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */

namespace Vpw\Dal\Mapper;

use Zend\Db\Metadata\Object\ColumnObject;

use Vpw\Dal\Exception\RuntimeException;

class DbMetadata implements MetadataInterface
{

    /**
     * List of Zend\Db\Metadata\Object\ColumnObject object
     * @var array
     */
    private $columns;

    /**
     * List of Zend\Db\Metadata\Object\ConstraintObject object
     * @var array
     */
    private $constraints;

    public function __construct(array $columns, array $constraints)
    {
        $this->columns = $columns;
        $this->constraints = $constraints;
    }

    /**
     *
     * @return array ColumnObject
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function getColumn($name)
    {
        if (array_key_exists($name, $this->columns) === false) {
            throw new RuntimeException("No column found with this name '$name'");
        }

        return $this->columns[$name];
    }

    public function getConstraints()
    {
        return $this->constraints;
    }

    public function getPrimaryKey()
    {
        foreach ($this->constraints as $constraint) {
            if ($constraint->isPrimaryKey() === true) {
                return $constraint->getColumns();
            }
        }

        return null;
    }

    public function getAutoIncrementColumn()
    {
        foreach ($this->columns as $column) {
            if ($column->getErrata('auto_increment') !== false) {
                return $column;
            }
        }

        return null;
    }

    public function serialize()
    {
        return serialize(
            array(
                $this->columns,
                $this->constraints
            )
        );
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->columns = $data[0];
        $this->constraints = $data[1];
    }
}
