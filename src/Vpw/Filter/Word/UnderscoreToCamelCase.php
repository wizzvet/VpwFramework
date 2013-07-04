<?php
/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 * Created : 21 juin 2013
 * Encoding : UTF-8
 */
namespace Vpw\Filter\Word;

use Zend\Filter\Word\SeparatorToCamelCase;
use Zend\Stdlib\StringUtils;

class UnderscoreToCamelCase extends SeparatorToCamelCase
{

    protected $pregQuotedSeparator;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct('_');

        if (StringUtils::hasPcreUnicodeSupport()) {
            $this->patterns = array(
                '#(' . $this->pregQuotedSeparator.')(\p{L}{1})#u',
                '#(^\p{Ll}{1})#u',
            );
            if (!extension_loaded('mbstring')) {
                $this->replacements = array(
                    function ($matches) {
                        return strtoupper($matches[2]);
                    },
                    function ($matches) {
                        return strtoupper($matches[1]);
                    },
                );
            } else {
                $this->replacements = array(
                    function ($matches) {
                        return mb_strtoupper($matches[2], 'UTF-8');
                    },
                    function ($matches) {
                        return mb_strtoupper($matches[1], 'UTF-8');
                    },
                );
            }
        } else {
            $this->patterns = array(
                '#(' . $this->pregQuotedSeparator.')([A-Za-z]{1})#',
                '#(^[A-Za-z]{1})#',
            );
            $this->replacements = array(
                function ($matches) {
                    return strtoupper($matches[2]);
                },
                function ($matches) {
                    return strtoupper($matches[1]);
                },
            );
        }
    }

    public function setSeparator($separator)
    {
        parent::setSeparator($separator);

        // a unicode safe way of converting characters to \x00\x00 notation
        $this->pregQuotedSeparator = preg_quote($this->separator, '#');
    }


    public function filter($value)
    {
        $filtered = $value;
        foreach ($this->patterns as $index => $pattern) {
            $filtered = preg_replace_callback($pattern, $this->replacements[$index], $filtered);
        }
        return $filtered;
    }
}
