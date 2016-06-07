<?php

namespace Model\Pas;

class PortfolioTwrValue extends Base
{
    /**
     * @var float
     */
    protected $netValue;

    /**
     * @var float
     */
    protected $grossValue;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * Set net value
     *
     * @param float $netValue
     * @return this
     */
    public function setNetValue($netValue)
    {
        $this->netValue = $netValue;

        return $this;
    }

    /**
     * Get net value
     *
     * @return float
     */
    public function getNetValue()
    {
        return $this->netValue;
    }

    /**
     * Set gross value
     *
     * @param float $grossValue
     * @return this
     */
    public function setGrossValue($grossValue)
    {
        $this->grossValue = $grossValue;

        return $this;
    }

    /**
     * Get gross value
     *
     * @return float
     */
    public function getGrossValue()
    {
        return $this->grossValue;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }


    /**
     * Set client id
     *
     * @param string $clientId
     * @return this
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get client id
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }
}