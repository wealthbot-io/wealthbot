<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 30.01.14
 * Time: 11:50.
 */

namespace App\Model;

interface PaymentActivityInterface extends ActivityInterface
{
    /**
     * Get activity amount.
     *
     * @return float
     */
    public function getActivityAmount();
}
