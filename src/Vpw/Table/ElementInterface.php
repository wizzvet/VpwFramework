<?php
/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 */

namespace Vpw\Table;

interface ElementInterface
{
    /**
     * Set options for an element
     *
     * @param  array|\Traversable $options
     * @return ElementInterface
     */
    public function setOptions($options);

    /**
     * get the defined options
     *
     * @return array
     */
    public function getOptions();

    /**
     * return the specified option
     *
     * @param string $option
     * @return null|mixed
     */
    public function getOption($option);

    /**
     * Set a single element attribute
     *
     * @param  string $key
     * @param  mixed $value
     * @return ElementInterface
     */
    public function setAttribute($key, $value);

    /**
     * Retrieve a single element attribute
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key);

    /**
     * Return true if a specific attribute is set
     *
     * @param  string $key
     * @return bool
     */
    public function hasAttribute($key);

    /**
     * Set many attributes at once
     *
     * Implementation will decide if this will overwrite or merge.
     *
     * @param  array|\Traversable $arrayOrTraversable
     * @return ElementInterface
     */
    public function setAttributes($arrayOrTraversable);

    /**
     * Retrieve all attributes at once
     *
     * @return array|\Traversable
     */
    public function getAttributes();
}
