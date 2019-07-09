<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 10.03.14
 * Time: 19:00
 */

namespace System\Model\WealthbotRebalancer;




class Position extends Base
{
    /** @var float */
    private $amount;

    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

} 