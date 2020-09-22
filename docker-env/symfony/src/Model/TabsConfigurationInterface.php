<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.09.13
 * Time: 13:23
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

interface TabsConfigurationInterface
{
    /**
     * Generate collection of tabs.
     *
     * @return TabCollection
     */
    public function generate();
}
