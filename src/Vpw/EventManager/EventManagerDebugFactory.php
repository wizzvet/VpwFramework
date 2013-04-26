<?php
/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */

namespace Vpw\EventManager;

use Vpw\EventManager\EventManagerDebug;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventManagerDebugFactory implements FactoryInterface
{
    /**
     * Create an EventManager instance
     *
     * Creates a new EventManager instance, seeding it with a shared instance
     * of SharedEventManager.
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return EventManager
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $em = new EventManagerDebug();
        $em->setSharedManager($serviceLocator->get('SharedEventManager'));

        return $em;
    }
}
