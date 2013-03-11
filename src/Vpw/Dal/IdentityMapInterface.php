<?php
namespace Vpw\Dal;

interface IdentityMapInterface
{
    public function add(ModelObject $object);

    public function remove(ModelObject $object);

    public function contains(ModelObject $object);
}
