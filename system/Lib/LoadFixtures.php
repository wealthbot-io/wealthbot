<?php

namespace Lib;

use Model\WealthbotRebalancer\ModelInterface;

class LoadFixtures {

    public static function load($data, $class)
    {
        $objCollection = array();

        foreach ($data as $row) {
            $obj = new $class();

            if (!$obj instanceof ModelInterface) {
                throw new \Exception('$class must be instance of "Model\WealthbotRebalancer\ModelInterface"');
            }

            $obj->loadFromArray($row);

            $objCollection[] = $obj;
        }

        return $objCollection;
    }
}
