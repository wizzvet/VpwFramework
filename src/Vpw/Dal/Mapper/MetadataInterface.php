<?php
namespace Vpw\Dal\Mapper;

use Zend\Db\Metadata\Object\ColumnObject;
use Zend\Db\Metadata\Object\ConstraintObject;

/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 * Created : 11 avr. 2013
 * Encoding : UTF-8
 */

interface MetadataInterface extends \Serializable
{
    /**
     * @return array A List of ColumnObject objects
     */
    public function getColumns();

    /**
     * @param string $name
     * @return null|ColumnObject
     */
    public function getColumn($name);

    /**
     * @return List of ConstraintObject objects
     */
    public function getConstraints();


    /**
     * @return null|array Array of column name
     */
    public function getPrimaryKey();

    /**
     * @return null|ColumnObject Auto increment column
     */
    public function getAutoIncrementColumn();

}