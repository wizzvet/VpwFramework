<?php
/**
 * Important : Les classes héritant de cet objet devront déclarées leurs attributs
 * en "protected", afin que la fonction get_object_vars puisse les récupérer.
 *
 * cf. http://www.php.net/manual/en/function.get-object-vars.php
 *
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */

namespace Vpw\Dal;

use Zend\Filter\Word\CamelCaseToUnderscore;

use Zend\Filter\Word\UnderscoreToCamelCase;

use Zend\Stdlib\ArraySerializableInterface;


abstract class ModelObject implements ArraySerializableInterface, \ArrayAccess
{
    private $loaded = false;

    public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->exchangeArray($data);
        }
    }

    public function load(array $data)
    {
        $this->exchangeArray($data);
        $this->setLoaded(true);
    }

    public function exchangeArray(array $data)
    {
        $filter = new UnderscoreToCamelCase();

        foreach ($data as $name => $value) {
            $methodName = 'set' . ucfirst($filter->filter($name));
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

            $key = strToLower($filter->filter($var));
            $methodName = 'get' . ucfirst($var);

            if (method_exists($this, $methodName) === false) {
                continue;
            }

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
        $this->loaded = (bool)$loaded;
    }

    /**
     * Retourne la valeur qui permet d'identifier cet objet
     */
    abstract public function getIdentityKey();



    /**
     * @param offset
     */
    public function offsetExists ($offset) {
        return method_exists($this, 'get' . ucfirst($offset));
    }

    /**
     * @param offset
     */
    public function offsetGet ($offset) {
        $methodName = 'get' . ucfirst($offset);
        return $this->$methodName();
    }

    /**
     * @param offset
     * @param value
     */
    public function offsetSet ($offset, $value) {
        $methodName = 'set' . ucfirst($offset);
        return $this->$methodName($value);
    }

    /**
     * @param offset
     */
    public function offsetUnset ($offset) {
        $methodName = 'set' . ucfirst($offset);
        return $this->$methodName(null);
    }

}
