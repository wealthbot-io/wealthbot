<?php

namespace App\Entity;

/**
 * Class PortfolioTwrValue
 * @package App\Entity
 */
class PortfolioTwrValue
{
    /**
     * @var int
     */
    protected $id;

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
     * @param \App\Entity\User
     */
    private $client;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set net value.
     *
     * @param float $netValue
     *
     * @return $this
     */
    public function setNetValue($netValue)
    {
        $this->netValue = $netValue;

        return $this;
    }

    /**
     * Get net value.
     *
     * @return float
     */
    public function getNetValue()
    {
        return $this->netValue;
    }

    /**
     * Set gross value.
     *
     * @param float $grossValue
     *
     * @return $this
     */
    public function setGrossValue($grossValue)
    {
        $this->grossValue = $grossValue;

        return $this;
    }

    /**
     * Get gross value.
     *
     * @return float
     */
    public function getGrossValue()
    {
        return $this->grossValue;
    }

    /**
     * Set date.
     *
     * @param \DateTime $date
     *
     * @return Position
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return SystemAccount
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }
}
