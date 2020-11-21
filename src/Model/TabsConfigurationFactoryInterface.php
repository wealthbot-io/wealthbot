<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.10.13
 * Time: 19:12
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

interface TabsConfigurationFactoryInterface
{
    /**
     * @return TabsConfigurationInterface
     */
    public function create();
}
