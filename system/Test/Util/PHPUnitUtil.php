<?php

namespace Test\Util;


class PHPUnitUtil
{
    /**
     * Call private or protected method
     *
     * @param $object
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    public static function callMethod($object, $methodName, array $args = array())
    {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $args);
    }
} 