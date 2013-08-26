<?php
/**
 * Important : Les classes héritant de cet objet devront déclarées leurs attributs
 * en "protected", afin que la fonction get_object_vars puisse les récupérer.
 *
 * Si on attribut de la classe ne doit pas être exporter, il suffit de le mettre en private
 * Par exemple : les objets issus des clé étrangères
 *
 * cf. http://www.php.net/manual/en/function.get-object-vars.php
 *
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */

namespace Vpw\Dal;

use Zend\Stdlib\ArraySerializableInterface;
use Vpw\Filter\Word\Ascii\UnderscoreToCamelCase;
use Zend\Filter\Word\CamelCaseToUnderscore;

abstract class ModelObject implements ArraySerializableInterface, \ArrayAccess
{
    /**
     *
     * @var UnderscoreToCamelCase
     */
    private static $filter;

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

    /**
     * Flag indiquant si l'objet a été chargé à partir de la base de données
     * @var unknown
     */
    private $loaded = false;

    /**
     * Flags to know what has been loaded is this model object
     *
     * !Important : laisser en protected pour que ca passe dans la serialization
     *
     * @var number
     */
    protected $flags = 0;


    public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->exchangeArray($data);
        }
    }

    /**
     * Retourne la valeur qui permet d'identifier cet objet
     */
    abstract public function getIdentityKey();

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
     * (non-PHPdoc)
     * @see \Zend\Stdlib\ArraySerializableInterface::exchangeArray()
     */
    public function exchangeArray(array $data)
    {
        $filter = self::getFilter();

        foreach ($data as $name => $value) {
            $methodName = 'set' . $filter->filter($name, true);
            if (method_exists($this, $methodName) === true) {
                $this->$methodName($value);
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Stdlib\ArraySerializableInterface::getArrayCopy()
     */
    public function getArrayCopy()
    {
        $filter = new CamelCaseToUnderscore();

        $data = array();

        foreach (array_keys(get_object_vars($this)) as $var) {
            if ($var === 'loaded') {
                continue;
            }

            $methodName = 'get' . ucfirst($var);
            if (method_exists($this, $methodName) === false) {
                continue;
            }

            $key = strToLower($filter->filter($var));
            $data[$key] = $this->$methodName();
        }

        return $data;
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
}
