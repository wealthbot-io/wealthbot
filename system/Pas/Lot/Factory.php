<?php

namespace Pas\Lot;

class Factory
{
    public static $register = array();

    public static function make($class)
    {
        if (!isset(self::$register[$class])) {
            $className = "Pas\\Lot\\$class";
            self::$register[$class] = new $className;
        }

        return self::$register[$class];
    }

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
}