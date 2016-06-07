<?php

namespace Wealthbot\ClientBundle\Entity;

/**
 * PortfolioTwrValue.
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
     * @var \Wealthbot\UserBundle\Entity\User
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
     * @return this
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
     * @return this
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
     * @param \Wealthbot\UserBundle\Entity\User $client
     *
     * @return SystemAccount
     */
    public function setClient(\Wealthbot\UserBundle\Entity\User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \Wealthbot\UserBundle\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }
}
