<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 16.01.14
 * Time: 18:08.
 */

namespace Wealthbot\ClientBundle\Model;

interface PaymentWorkflowableInterface extends WorkflowableInterface
{
    /**
     * Get workflow amount.
     *
     * @return float
     */
    public function getWorkflowAmount();
}
