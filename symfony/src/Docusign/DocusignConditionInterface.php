<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.09.13
 * Time: 17:59
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

interface DocusignConditionInterface
{
    /**
     * Check condition.
     *
     * @param $param
     *
     * @return mixed
     */
    public function check($param);
}
