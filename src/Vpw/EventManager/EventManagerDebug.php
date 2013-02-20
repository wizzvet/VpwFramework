<?php
/**
 * @author christophe.borsenberger@vosprojetsweb.pro
 */

namespace Vpw\EventManager;

use Zend\EventManager\ResponseCollection;

use Zend\EventManager\EventInterface;

/**
 *
 */
class EventManagerDebug extends \Zend\EventManager\EventManager
{
    public function __construct($identifiers = null)
    {
        parent::__construct($identifiers);

        //echo 'Create EventManagerDebug<br/>';
        //$this->printStackTrace();
    }

    protected function triggerListeners($event, EventInterface $e, $callback = null)
    {
        $responses = new ResponseCollection();
        $listeners = $this->getListeners($event);

        // Add shared/wildcard listeners to the list of listeners,
        // but don't modify the listeners object
        $sharedListeners = $this->getSharedListeners($event);
        $sharedWildcardListeners = $this->getSharedListeners('*');
        $wildcardListeners = $this->getListeners('*');
        if (count($sharedListeners) || count($sharedWildcardListeners) || count($wildcardListeners)) {
            $listeners = clone $listeners;

            // Shared listeners on this specific event
            $this->insertListeners($listeners, $sharedListeners);

            // Shared wildcard listeners
            $this->insertListeners($listeners, $sharedWildcardListeners);

            // Add wildcard listeners
            $this->insertListeners($listeners, $wildcardListeners);
        }

        foreach ($listeners as $listener) {
            //echo '<br/><br/>';

            // Trigger the listener's callback, and push its result onto the
            // response collection
            $listenerCallback = $listener->getCallback();

            echo 'Event : ', $e->getName(),  ', ';

            echo 'Target : ', get_class($e->getTarget()), ', ';

            echo 'Trigger Callback : '; $this->printCallback($callback); echo ', ';

            echo 'Listener Callback : '; $this->printCallback($listenerCallback);

            echo 'Priority : ', $listener->getMetadatum('priority');

            echo '<br/>';

            //$this->printStackTrace();

            $responses->push(call_user_func($listenerCallback, $e));

            // If the event was asked to stop propagating, do so
            if ($e->propagationIsStopped()) {
                //echo 'Event has asked to stop propagating<br/>';
                $responses->setStopped(true);
                break;
            }

            // If the result causes our validation callback to return true,
            // stop propagation
            if ($callback && call_user_func($callback, $responses->last())) {
                //echo 'Callback validation has asked to stop propagating<br/>';
                //$this->printCallback($callback); echo '<br/>';
                $responses->setStopped(true);
                break;
            }
        }

        return $responses;
    }


    private function printCallback($callback)
    {
        if (is_string($callback)) {
            echo $callback, ' ';
        } else if (is_object($callback)) {
            echo get_class($callback), ' ';
        } else if (is_array($callback)) {
            foreach ($callback as $tmp) {
                if (is_object($tmp)) {
                    echo get_class($tmp), ' ';
                } else if (is_string($tmp)) {
                    echo $tmp, ' ';
                } else {
                    echo gettype($tmp), ' ';
                }
            }
        } else {
            echo gettype($callback), ' ';
        }
    }

    private function printStackTrace()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $size = sizeof($backtrace);

        for ($i = 1; $i < $size; $i++){
            echo $backtrace[$i]['class'],$backtrace[$i]['type'], $backtrace[$i]['function'], '<br/>';
        }
    }
}
