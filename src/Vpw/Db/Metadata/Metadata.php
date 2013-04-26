<?php
namespace Vpw\Db\Metadata;

use Zend\Db\Adapter\Adapter;

class Metadata extends \Zend\Db\Metadata\Metadata
{
    protected function createSourceFromAdapter(Adapter $adapter)
    {
        switch ($adapter->getPlatform()->getName()) {
            case 'MySQL':
                return new Source\MysqlMetadata($adapter);
            default:
                return parent::createSourceFromAdapter($adapter);
        }
    }
}
