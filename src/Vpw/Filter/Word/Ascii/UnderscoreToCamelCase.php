<?php
/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 * Created : 21 juin 2013
 * Encoding : UTF-8
 */
namespace Vpw\Filter\Word\Ascii;

use Zend\Filter\Word\AbstractSeparator;

class UnderscoreToCamelCase extends AbstractSeparator
{

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        parent::__construct('_');
    }


    public function filter($value, $upFirstLetter = false)
    {
        $value = trim($value, '_');

        $underscores = array();
        $nbUnderscores = 0;

        $offset = 0;
        while (($pos = strpos($value, $this->separator, $offset)) !== false) {
            $underscores[] = $pos;
            $offset = $pos + 1;
            $nbUnderscores++;
        }

        if ($nbUnderscores > 0) {
            $filtered = '';
            $start = 0;
            $lastLen = 0;

            for ($i = 0; $i < $nbUnderscores; $i++) {

                $length = $underscores[$i] - $start;
                $filtered .= substr($value, $start, $length);
                $start = $underscores[$i] + 1;

                if ($lastLen > 0) {
                    //$filtered[$lastLen] = strtr($filtered[$lastLen], 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                    $filtered[$lastLen] = strtoupper($filtered[$lastLen]);
                }

                $lastLen += $length;
            }

            $filtered .= substr($value, $start);
            if ($lastLen > 0) {
                //$filtered[$lastLen] = strtr($filtered[$lastLen], 'abcdefghijklmnopqrstuvwxyz', 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
                $filtered[$lastLen] = strtoupper($filtered[$lastLen]);
            }

        } else {
            $filtered = $value;
        }

        if ($upFirstLetter === true) {
            $filtered[0] = strtoupper($filtered[0]);
        }

        return $filtered;
    }
}
