<?php
/**
 *
 * @author christophe.borsenberger@vosprojetsweb.pro
 *
 * Created : 21 juin 2013
 * Encoding : UTF-8
 */
namespace VpwTest\Filter\Word\Ascii;

use Vpw\Filter\Word\Ascii\UnderscoreToCamelCase;
use PHPUnit_Framework_TestCase;

class UnderscoreToCamelCaseTest extends PHPUnit_Framework_TestCase
{

    private $filter;

    public function setup()
    {
        $this->filter = new UnderscoreToCamelCase();
    }

    public function testNameWithoutUnderscore()
    {
        $this->assertEquals('foo', $this->filter->filter('foo'));
        $this->assertEquals('Foo', $this->filter->filter('foo', true));
    }

    public function testNameWithOneUnderscore()
    {
        $this->assertEquals('fooFoo', $this->filter->filter('foo_foo'));
        $this->assertEquals('FooFoo', $this->filter->filter('foo_foo', true));
    }

    public function testNameWithManyUnderscores()
    {
        $this->assertEquals('fooFooFoo', $this->filter->filter('foo_foo_foo'));
        $this->assertEquals('FooFooFoo', $this->filter->filter('foo_foo_foo', true));
    }

    public function testNameEndWithUnderscore()
    {
        $this->assertEquals('foo', $this->filter->filter('foo_'));
        $this->assertEquals('foo', $this->filter->filter('foo__'));
    }

    public function testNameStartWithUnderscore()
    {
        $this->assertEquals('foo', $this->filter->filter('__foo'));
    }
}
