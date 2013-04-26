<?php
namespace Vpw\Db\Adapter\Driver\Mysqli;

use Zend\Db\Adapter\Driver\Mysqli\Result;

class Mysqli extends \Zend\Db\Adapter\Driver\Mysqli\Mysqli
{
    public function __construct($connection, Result $resultPrototype = null, array $options = array())
    {
        parent::__construct(new Connection($connection), new Statement(), $resultPrototype, $options);
    }

    public function createStatement($sqlOrResource = null)
    {
        $statement = parent::createStatement($sqlOrResource);
        $statement->setConnection($this->getConnection());

        return $statement;
    }
}
