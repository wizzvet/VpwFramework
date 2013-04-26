<?php
namespace Vpw\Db\Adapter\Driver\Mysqli;

use Zend\EventManager\EventManagerInterface;

use Zend\EventManager\EventManagerAwareInterface;

use Zend\EventManager\EventManager;

class Connection extends \Zend\Db\Adapter\Driver\Mysqli\Connection implements EventManagerAwareInterface
{

    protected $events;

    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers(
            array(
                __CLASS__,
                get_called_class(),
            )
        );
        $this->events = $events;

        return $this;
    }

    public function getEventManager()
    {
        if (null === $this->events) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    public function getHostname()
    {
        if (isset($this->connectionParameters['hostname']) === true) {
            return $this->connectionParameters['hostname'];
        }

        if (isset($this->connectionParameters['host']) === true) {
            return $this->connectionParameters['host'];
        }

        return null;
    }

    public function execute($sql)
    {
        $this->preExecute($sql);
        $res = parent::execute($sql);
        $this->postExecute($sql, $res);

        return $res;
    }

    public function connect()
    {
        if ($this->isConnected() === true) {
            return;
        }

        $this->preConnect();
        parent::connect();
        $this->postConnect();
    }

    protected function preExecute($sql)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('query' => $sql));
    }

    protected function postExecute($sql, $res)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('query' => $sql, 'result'=>$res));
    }

    protected function preConnect()
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this);
    }

    protected function postConnect()
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this);
    }
}
