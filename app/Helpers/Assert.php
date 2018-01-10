<?php
/**
 * Created by PhpStorm.
 * User: eddymens
 * Date: 06/09/2016
 * Time: 10:53 AM.
 */

namespace App\Helpers;

use BadMethodCallException;

class Assert extends Helper
{
   
    /**
     * check if $value is an integer. eg `->beforeCreating()->whenever(assertIts::anInteger(3))->then->stopAndOutput(1001,'message', 'its an integer') #its an integer`
     *
     * @param $value
     *
     * @return bool
     */
    public static function anInteger($value)
    {
        return (is_int($value))?:false;
    }

    /**
     * check if $value is a string. eg: `->beforeCreating()->whenever(assertIts::aString("Hello"))->then->stopAndOutput(1001,'message', 'its a string') #its a string`
     *
     * @param $value
     *
     * @return bool
     */
    public static function aString($value)
    {
        return (is_string($value))?:false;
    }

    /**
     * check if $value is a  boolean eg: `->beforeCreating()->whenever(assertIts::aBoolean(1 == 1))->then->stopAndOutput(1001,'message', 'its a boolean') #its a boolean`
     *
     * @param $value
     *
     * @return bool
     */
    public static function aBoolean($value)
    {
        return (is_bool($value))?:false;
    }

    /**
     * check if $value is a float eg: `->beforeCreating()->whenever(assertIts::aFloat(3.034))->then->stopAndOutput(1001,'message', 'its a float') #its a float`
     *
     * @param $value
     *
     * @return bool
     */
    public static function aFloat($value)
    {
        return (is_float($value))?:false;
    }

    /**
     *  check if $value is within th range $min $max eg: `->beforeCreating()->whenever(assertIts::withinRange($value=2, $min=1, $max=4))->then->stopAndOutput(1001,'message', 'its within range') #its within range`
     *
     * @param $value
     * @param $min
     * @param $max
     *
     * @return bool
     */
    public static function withinRange($value, $min, $max)
    {
        return ($min < $value && $value < $max)?:false;
    }

    /**
     *  check if $value is upppercase eg: `->beforeCreating()->whenever(assertIts::upperCase("HELLO"))->then->stopAndOutput(1001,'message', 'its upper case') #its upper case`
     *
     * @param $value
     *
     * @return bool
     */
    public static function upperCase($value)
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_upper($value);
        setlocale(LC_CTYPE, $locale);

        return (!$valid) ? true : false;
    }

    /**
     * check if $value is lowercase. eg: `->beforeCreating()->whenever(assertIts::lowerCase("hello"))->then->stopAndOutput(1001,'message', 'its lower case ') #its lower case`
     *
     * @param $value
     *
     * @return bool
     */
    public static function lowerCase($value)
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_lower($value);
        setlocale(LC_CTYPE, $locale);

        return (!$valid) ? true : false;
    }

    /**
     * check if $value is alphanumeric. eg: `->beforeCreating()->whenever(assertIts::alphanumeric("E23D"))->then->stopAndOutput(1001,'message', 'its alphanumeric') #its alphanumeric`
     *
     * @param $value
     *
     * @return bool
     */
    public static function alphanumeric($value)
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_alnum($value);
        setlocale(LC_CTYPE, $locale);

        return (!$valid) ? true : false;
    }

    /**
     * check if $value are alphabets eg: `->beforeCreating()->whenever(assertIts::alphabets("abcd"))->then->stopAndOutput(1001,'message', 'its alphabets') #its alphabets`
     *
     * @param $value
     *
     * @return bool
     */
    public static function alphabets($value)
    {
        $locale = setlocale(LC_CTYPE, 0);
        setlocale(LC_CTYPE, 'C');
        $valid = !ctype_alpha($value);
        setlocale(LC_CTYPE, $locale);

        return (!$valid) ? true : false;
    }

    /**
     * check if $value startswith $prefix eg: `->beforeCreating()->whenever(assertIts::startsWith("E23D", "E"))->then->stopAndOutput(1001,'message', 'it starts with E') #it starts with E`
     *
     * @param $value
     * @param $prefix
     *
     * @return bool
     */
    public static function startsWith($value, $prefix)
    {
        return (0 === strpos($value, $prefix)) ? true : false;
    }

    /**
     *check if $value ends with $suffix eg: `->beforeCreating()->whenever(assertIts::endsWith("E23D", "D"))->then->stopAndOutput(1001,'message', 'it ends with D') #it ends with D`
     *
     * @param $value
     * @param $suffix
     *
     * @return bool
     */
    public static function endsWith($value, $suffix)
    {
        return ($suffix == substr($value, -strlen($suffix))) ? true : false;
    }

    /**
     * check if $value is matched regex eg: `->beforeCreating()->whenever(assertIt::matchesRegex("edmond@devless.io", "<email-regex-goes-here>"))->then->stopAndOutput(1001,'message', 'it matches the email regex')`
     *
     * @param $value
     * @param $pattern
     *
     * @return bool
     */
    public static function matchesRegex($value, $pattern)
    {
        return (preg_match($pattern, $value)) ? true : false;
    }

    /**
     * check if $value is an email eg: `->beforeCreating()->whenever(assertIts::anEmail("edmond@devless.io"))->then->stopAndOutput(1001,'message', 'its an email') #its an email`
     *
     * @param $value
     *
     * @return bool
     */
    public static function anEmail($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }


    /**
     *  check if $value is not an empty array or empty string eg: `->beforeCreating()->whenever(assertIt::notEmpty("some text"))->then->stopAndOutput(1001,'message', 'its not empty') #its not empty`
     *
     * @param $value
     *
     * @return bool
     */
    public static function notEmpty($value)
    {
        if (is_array($value)) {
            return (sizeof($value) != 0)?:false;
        }
        if (is_string($value)) {
            return (strlen($value) != 0)?:false;
        }
        return false;
    }

    /**
     *check if $value is  an empty array or empty string eg: `->beforeCreating()->whenever(assertIt::isEmpty(""))->then->stopAndOutput(1001,'message', 'its  empty') #its  empty`
     *
     * @param $value
     *
     * @return bool
     */
    public static function isEmpty($value)
    {
        if (is_array($value)) {
            return (sizeof($value) == 0)?:false;
        }
        if (is_string($value)) {
            return (strlen($value) == 0)?:false;
        }
        return false;
    }
    /**
     * check if $value is contains $subString eg: `->beforeCreating()->whenever(assertIt::contains("edmond@devless.io", "edmond"))->then->stopAndOutput(1001,'message', 'email containes edmond') #email containes edmond`
     *
     * @param $value
     * @param $subString
     *
     * @return bool
     */
    public static function contains($value, $subString)
    {
        return (false !== strpos($value, $subString)) ? true : false;
    }

    /**
     * check if $value equals $value1 eg: `->beforeCreating()->whenever(assertIts::equal("a", "a"))->then->stopAndOutput(1001,'message', 'a is equal to a :)') #a is equal to a :)`
     *
     * @param $value
     * @param $value1
     *
     * @return bool
     */
    public static function equal($value, $value1)
    {
        return ($value == $value1)?:false;
    }

    /**
     *check if $value is not equal to $value1 eg: `->beforeCreating()->whenever(assertIts::notEqual("a", "b"))->then->stopAndOutput(1001,'message', 'a is not equal to b ') #a is not equal to b `
     *
     * @param $value
     * @param $value1
     *
     * @return bool
     */
    public static function notEqual($value, $value1)
    {
        return ($value != $value1)?:false;
    }
    /**
     * check if $value is greater than $value1 eg: `->beforeCreating()->whenever(assertIt::greaterThan(45, 12))->then->stopAndOutput(1001,'message', '45 is greater than 12') #45 is greater than 12`
     *
     * @param $value
     *
     * @return bool
     */
    public static function greaterThan($value, $value1)
    {
        return ($value > $value1)?:false;
    }

    /**
     * check if $value is less than $value1 eg: `->beforeCreating()->whenever(assertIt::lessThan(12, 45))->then->stopAndOutput(1001,'message', '12 is less than 45') #12 is less than 45`
     *
     * @param $value
     * @param $value1
     *
     * @return bool
     */
    public static function lessThan($value, $value1)
    {
        return ($value < $value1)?:false;
    }

    /**
     * check if $value is greater than or equal to $value1 eg: `->beforeCreating()->whenever(assertIt::greaterThanOrEqualTo(45, 45))->then->stopAndOutput(1001,'message', '45 is greater than or equal to 45') #45 is greater than or equal to 45`
     *
     * @param $value
     * @param $value1
     *
     * @return bool
     */
    public static function greaterThanOrEqualTo($value, $value1)
    {
        return ($value >= $value1)?:false;
    }

    /**
     * check if $value is less than or equal to $value1  `->beforeCreating()->whenever(assertIt::lessThanOrEqualTo($v=45, $v1=45))->then->stopAndOutput(1001,'message', "$v is less than or equal to $v1") #45 is less than or equal to 45`
     *
     * @param $value
     * @param $value1
     *
     * @return bool
     */
    public static function lessThanOrEqualTo($value, $value1)
    {
        return ($value <= $value1)?:false;
    }

    
    public static function __callStatic($name, $arguments)
    {
        if ($name == 'empty') {
            $method = '_empty';
            return call_user_func_array(['static', $method], $args);
        }

        throw new BadMethodCallException('No such method: '.$name);
    }

    
    public function __construct()
    {
    }
}
