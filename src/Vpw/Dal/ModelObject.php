<?php
/**
 * Important : Les classes héritant de cet objet devront déclarées leurs attributs
 * en "protected", afin que la fonction get_object_vars puisse les récupérer.
 *
 * Si on attribut de la classe ne doit pas être exporter, il suffit de le mettre en private
 * Par exemple : les objets issus des clé étrangères
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */

namespace Vpw\Dal;

use Zend\Stdlib\ArraySerializableInterface;
use Vpw\Filter\Word\Ascii\UnderscoreToCamelCase;
use Zend\Filter\Word\CamelCaseToUnderscore;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\Filter\MethodMatchFilter;
use Zend\Stdlib\Hydrator\Filter\FilterComposite;
use Vpw\Dal\Hydrator\ModelObjectHydrator;

abstract class ModelObject implements ArraySerializableInterface, \ArrayAccess, HydratorAwareInterface
{
    /**
     *
     * @var UnderscoreToCamelCase
     */
    private static $filter;


    /**
     * @var ClassMethods
     */
    protected static $defaultHydrator;

    /**
     * Lazy load
     * @return \Zend\Filter\Word\UnderscoreToCamelCase
     */
    private static function getFilter()
    {
        if (self::$filter === null) {
            self::$filter = new UnderscoreToCamelCase();
        }

        return self::$filter;
    }

    protected static function getDefaultHydrator()
    {
        if (self::$defaultHydrator === null) {
            self::$defaultHydrator = new ModelObjectHydrator();
        }

        return self::$defaultHydrator;
    }


    /**
     * Flag indiquant si l'objet a été chargé à partir de la base de données
     * @var unknown
     */
    protected $loaded = false;

    /**
     * Flags to know what has been loaded is this model object
     *
     * !Important : laisser en protected pour que ca passe dans la serialization
     *
     * @var number
     */
    protected $flags = 0;

    /**
     *
     * @var HydratorInterface
     */
    protected $hydrator;




    public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->exchangeArray($data);
        }
    }

    /**
     * Retourne une clé qui permet d'identifier cet objet
     */
    final public function getIdentityKey()
    {
        $identity = $this->getIdentity();

        if ($identity === null) {
            return null;
        }

        if (is_array($identity) == false) {
            return $identity;
        }

        $key = '';
        foreach ($identity as $val) {
            if ($val === null) {
                return null;
            }

            $key .= $val . '-';
        }

        return substr($key, 0, -1);
    }

    /**
     * @return mixed
     */
    abstract public function getIdentity();


    /**
     *
     * @param array $data
     */
    public function load(array $data)
    {
        $this->exchangeArray($data);
        $this->setLoaded(true);
    }

    /**
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $data)
    {
        $filter = self::getFilter();

        foreach ($data as $name => $value) {
            if ($value === null) {
                continue;
            }

            $methodName = 'set' . $filter->filter($name, true);
            if (method_exists($this, $methodName) === true) {
                $this->$methodName($value);
            }
        }
    }

    /**
     * Returns only "real" data, not metadata like "flags". So if you want cache a model object use
     * serialize instead of getArrayCopy
     *
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy($deep = false)
    {
        $copy = $this->getHydrator()->extract($this);

        if ($deep === false) {
            return $copy;
        }

        foreach ($copy as $index => $value) {
            if (is_object($value) && $value instanceof ArraySerializableInterface) {
                $copy[$index] = $value->getArrayCopy(true);
            }
        }

        return $copy;
    }

    public function isLoaded()
    {
        return $this->loaded;
    }

    public function setLoaded($loaded)
    {
        $this->loaded = (bool) $loaded;
    }

    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    public function hashFlag($flag)
    {
        return ($flag & $this->flags === $flag);
    }

    /**
     * @param offset
     */
    public function offsetExists ($offset)
    {
        return method_exists($this, 'get' . self::getFilter()->filter($offset, true));
    }

    /**
     * @param offset
     */
    public function offsetGet ($offset)
    {
        $methodName = 'get' . self::getFilter()->filter($offset, true);

        return $this->$methodName();
    }

    /**
     * @param offset
     * @param value
     */
    public function offsetSet ($offset, $value)
    {
        $methodName = 'set' . self::getFilter()->filter($offset, true);

        return $this->$methodName($value);
    }

    /**
     * @param offset
     */
    public function offsetUnset ($offset)
    {
        $methodName = 'set' . self::getFilter()->filter($offset, true);

        return $this->$methodName(null);
    }


    /**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\HydratorAwareInterface::getHydrator()
     */
    public function getHydrator()
    {
        if ($this->hydrator === null) {
            return static::getDefaultHydrator();
        }

        return $this->hydrator;
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\Hydrator\HydratorAwareInterface::setHydrator()
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }


    /**
     * Serialize all data & metadata. Use this method to cache objects
     *
     * @return string
     */
    public function __sleep()
    {
        $vars = array_keys(get_object_vars($this));
        unset($vars[array_search('hydrator', $vars)]);

        return $vars;
    }
}
