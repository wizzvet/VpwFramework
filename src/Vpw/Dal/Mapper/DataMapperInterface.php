<?php
namespace Vpw\Dal\Mapper;

use Vpw\Dal\ModelObject;

interface DataMapperInterface
{
    public function save(ModelObject $object);

    public function insert(ModelObject $object);

    public function update(ModelObject $object);

    public function delete(ModelObject $object);
}
