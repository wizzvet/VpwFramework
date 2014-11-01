<?php
/**
 * Important : Les classes héritant de cet objet devront déclarées leurs attributs
 * en "protected", afin que la serialization fonctionne correctement.
 *
 * Si on attribut de la classe ne doit pas être serializer, il suffit de la déclarer en private.
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 */

namespace Vpw\Dal;

use Vpw\Filter\Word\Ascii\UnderscoreToCamelCase;
use Zend\Stdlib\Hydrator\HydratorAwareInterface;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Vpw\Dal\Hydrator\ModelObjectHydrator;

abstract class ModelObject implements \ArrayAccess, HydratorAwareInterface
{
    /**
     *
     * @var UnderscoreToCamelCase
     */
    private static $filter;

    /**
     * @var HydratorInterface
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
    private $hydrator;


    public function __construct(array $data = null)
    {
        if ($data !== null) {
            $this->getHydrator()->hydrate($data, $this);
        }
    }

    /**
     * Retourne une clé permettant d'identifier cet objet.
     *
     * On ne stocke pas la clé en cache, car dès qu'une valeur de le clé est modifiée, il faut invalider le cache. Et ceci doit être fait
     * dans chaque sous classe, ce qui peut entraîner bcp d'erreurs lors de la maintenance.
     */
    final public function getIdentityKey()
    {
        $identity = $this->getIdentity();

        if (is_array($identity) == false) {
            return $identity;
        }

        return $this->buildIdentityKey($identity);
    }

    /**
     * @return mixed
     */
    abstract public function getIdentity();

    /**
     * Prefix with "id" to not have a numeric identity key
     *
     * @param array $identity
     * @return NULL|string
     */
    final protected function buildIdentityKey(array $identity)
    {
        $key = 'id';

        foreach ($identity as $val) {

            if ($val === null) {
                return null;
            }

            $key .= '-' . $val;
        }

        return $key;
    }

    /**
     * Delete function because, only the mapper can load data and the mapper, already everything to load data
     * @param array $data
     */

//     public function load(array $data)
//     {
//         $this->exchangeArray($data);
//         $this->setLoaded(true);
//     }

    /**
     * @param array $data
     */

    public function isLoaded()
    {
        return $this->loaded === true;
    }

    public function setLoaded($loaded)
    {
        $this->loaded = (bool) $loaded;
    }

    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    public function addFlags($flags)
    {
        $this->flags = $this->flags | $flags;
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function hasFlags($flag)
    {
        return (($this->flags & $flag) === $flag);
    }

    /**
     * @param offset
     */
    public function offsetExists ($offset)
    {
        return method_exists($this, 'set' . self::getFilter()->filter($offset, true));
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
     * Check if the two values are differents. If yes, it means the identity key has changed => it is potentially
     * a new record, so we reset the loaded state.
     *
     * @param mixed $newValue
     * @param mixed $oldValue
     */
    protected function updateIdentityPart($key, $value)
    {
        if ($this->offsetGet($key) !== $value) {
            $this->{$key} = $value;
            $this->setLoaded(false);
        }
    }
}
