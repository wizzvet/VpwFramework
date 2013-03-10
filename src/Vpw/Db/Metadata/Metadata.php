<?php
namespace Vpw\Db\Metadata;

use Zend\Db\Metadata\MetadataInterface;

use Zend\Db\Adapter\Adapter;

use Zend\Db\Metadata\Source\SqlServerMetadata;

use Zend\Db\Metadata\Source\SqliteMetadata;

use Zend\Db\Metadata\Source\PostgresqlMetadata;

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
