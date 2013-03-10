<?php
namespace Vpw\DataSource;

interface IdentityMapInterface
{
    public function add(AbstractObject $object);

    public function remove(AbstractObject $object);

    public function contains(AbstractObject $object);
}
