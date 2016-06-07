<?php

namespace Pas\TwrCalculator;

class Factory
{
    /**
     * @var array
     */
    protected static $registry = array();

    public static function add($object)
    {
        self::$registry[$object->getKey()] = $object;
    }

    public static function get($key)
    {
        return isset(self::$registry[$key]) ? self::$registry[$key] : null;
    }

    public static function remove($key)
    {
        if (array_key_exists($key, self::$registry)) {
            unset(self::$registry[$key]);
        }
    }
}