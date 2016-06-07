<?php

namespace Model\Pas;

class Position extends Base
{
    protected $date;
    protected $securityId;
    protected $clientSystemAccountId;
    protected $status;
    protected $amount;
    protected $quantity;

    public function __construct()
    {
        $this->status = 0;
    }

    public function setClientSystemAccountId($clientSystemAccountId)
    {
        $this->clientSystemAccountId = $clientSystemAccountId;

        return $this;
    }

    public function getClientSystemAccountId()
    {
        return $this->clientSystemAccountId;
    }

    public function setSecurityId($securityId)
    {
        $this->securityId = $securityId;

        return $this;
    }

    public function getSecurityId()
    {
        return $this->securityId;
    }

    /**
     * @param int $amount
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
        return (float) $this->amount;
    }


    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }
}