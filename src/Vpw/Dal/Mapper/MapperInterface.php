<?php
/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */
namespace Vpw\Dal\Mapper;

use Vpw\Dal\ModelObject;

interface MapperInterface
{
    public function save(ModelObject $object);

    public function insert(ModelObject $object);

    public function update(ModelObject $object);

    public function delete(ModelObject $object);

    public function getMetadata();

    public function find($key, $flags = 0);

    public function findAll($criteria, $options = null, $flags = 0);
}
