<?php
namespace Vpw\Db\Adapter\Driver\Mysqli;

use Zend\EventManager\EventManagerInterface;

use Zend\EventManager\EventManagerAwareInterface;

use Zend\EventManager\EventManager;

class Statement extends \Zend\Db\Adapter\Driver\Mysqli\Statement implements EventManagerAwareInterface
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventManagerInterface
     */
    protected $events = null;

    /**
     * @var null
     */
    protected $event = null;

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
        if ($this->events === null) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }

    public function execute($parameters = null)
    {
        $this->preExecute();
        $res = parent::execute($parameters);
        $this->postExecute($res);

        return $res;
    }

    protected function preExecute()
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this);
    }

    protected function postExecute($res)
    {
        $this->getEventManager()->trigger(__FUNCTION__, $this, array('result' => $res));
    }

    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
