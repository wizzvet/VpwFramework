<?php
namespace Vpw\Dal;

use Zend\Filter\Word\UnderscoreToCamelCase;

use Zend\Stdlib\ArraySerializableInterface;

abstract class ModelObject implements ArraySerializableInterface
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

    public function getArrayCopy()
    {
        $data = array();

        $filter = new UnderscoreToCamelCase();

        foreach (array_keys(get_object_vars($this)) as $var) {
            if ($var === 'loaded') {
                continue;
            }

            $methodName = 'get' . ucfirst($filter->filter($var));

            if (method_exists($this, $methodName) === false) {
                continue;
            }

            $data[$var] = $this->$methodName();
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
}
