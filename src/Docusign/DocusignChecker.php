<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.09.13
 * Time: 18:10
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

class DocusignChecker
{
    private $conditions;

    public function __construct($conditions)
    {
        if (!is_array($conditions)) {
            $conditions = [$conditions];
        }

        $this->conditions = $conditions;
    }

    /**
     * Check conditions by $param.
     *
     * @param $param
     *
     * @return bool
     */
    public function checkConditions($param)
    {
        foreach ($this->conditions as $condition) {
            if (($condition instanceof DocusignConditionInterface)) {
                if (!$condition->check($param)) {
                    return false;
                }
            }
        }

        return true;
    }
}
