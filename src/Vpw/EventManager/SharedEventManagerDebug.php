<?php
/**
 * @author christophe.borsenberger@vosprojetsweb.pro
 */

namespace Vpw\EventManager;

/**
 *
 */
class SharedEventManagerDebug extends Zend\EventManager\SharedEventManager
{
    public function __construct()
    {
        $this->attach('*', '*', array($this, 'logEvent', 10000));
    }

    public function logEvent($e)
    {
        var_dump($e);
    }
}