<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.10.13
 * Time: 14:29
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

interface WorkflowableInterface
{
    /**
     * Get workflow message code.
     *
     * @return string
     */
    public function getWorkflowMessageCode();
}
