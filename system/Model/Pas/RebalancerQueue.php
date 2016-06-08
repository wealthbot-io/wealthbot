<?php

namespace Model\Pas;

class RebalancerQueue extends Base
{
    /**
     * @var  string
     */
    protected $amount;

    /**
     * @var  string
     */
    protected $status;

    const STATUS_SELL = 'sell';
    const STATUS_BUY = 'buy';

    public function setAmount($amount)
    {
        return $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}