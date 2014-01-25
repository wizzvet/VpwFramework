<?php
namespace Vpw\Dal\Hydrator;

use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
use Zend\Stdlib\Hydrator\Filter\FilterComposite;
/**
 *
 * @author christophe.borsenberger@wizzvet.com
 *
 * Created : 23 janv. 2014
 * Encoding : UTF8
 */

class ModelObjectHydrator extends ClassMethods
{
    public function __construct($underscoreSeparatedKeys = true)
    {
        parent::__construct($underscoreSeparatedKeys);

        $this->filterComposite->removeFilter("is");
        $this->filterComposite->removeFilter("has");

        $this->addFilter('getHydrator', new MethodMatchFilter('getHydrator'), FilterComposite::CONDITION_AND);
        $this->addFilter('getArrayCopy', new MethodMatchFilter('getArrayCopy') , FilterComposite::CONDITION_AND);
        $this->addFilter('isLoaded', new MethodMatchFilter('isLoaded') , FilterComposite::CONDITION_AND);
        $this->addFilter('getIdentityKey', new MethodMatchFilter('getIdentityKey') , FilterComposite::CONDITION_AND);
        $this->addFilter('getIdentity', new MethodMatchFilter('getIdentity') , FilterComposite::CONDITION_AND);
    }


}