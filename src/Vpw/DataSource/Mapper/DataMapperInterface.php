<?php
namespace Vpw\DataSource\Mapper;

use Vpw\DataSource\AbstractObject;

interface DataMapperInterface
{
    public function save(AbstractObject $object);

    public function insert(AbstractObject $object);

    public function update(AbstractObject $object);

    public function delete(AbstractObject $object);
}
