<?php
/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */
namespace Vpw\Table;

use Zend\Stdlib\Hydrator\Filter\GetFilter;

use Zend\Filter\FilterInterface;

use Zend\Filter\Word\UnderscoreToDash;

class Column extends Element
{

    /**
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $helper = 'escapehtml';

    /**
     *
     * @var string
     */
    private $label;


    /**
     *
     * @var string
     */
    private $template;


    /**
     *
     * @param string|HelperInterface $name
     * @param string $type
     */
    public function __construct($name)
    {
        $this->setName($name);
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     *
     * @return string|HelperInterface
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     *
     * @param string|HelperInterface $name
     */
    public function setHelper($helper)
    {
        $this->helper = $helper;
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     *
     * @param string $tag
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }


    /**
     *
     * @return boolean
     */
    public function hasTemplate()
    {
        return $this->template !== null;
    }

    /**
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}