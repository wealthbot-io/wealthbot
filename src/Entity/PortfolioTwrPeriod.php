<?php

namespace App\Entity;

/**
 * Class PortfolioTwrPeriod
 * @package App\Entity
 */
class PortfolioTwrPeriod
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var float
     */
    protected $netMtd;

    /**
     * @var float
     */
    protected $grossMtd;

    /**
     * @var float
     */
    protected $netQtd;

    /**
     * @var float
     */
    protected $grossQtd;

    /**
     * @var float
     */
    protected $netYtd;

    /**
     * @var float
     */
    protected $grossYtd;

    /**
     * @var float
     */
    protected $netYr1;

    /**
     * @var float
     */
    protected $grossYr1;

    /**
     * @var float
     */
    protected $netYr3;

    /**
     * @var float
     */
    protected $grossYr3;

    /**
     * @var float
     */
    protected $netSinceInception;

    /**
     * @var float
     */
    protected $grossSinceInception;

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
     * Set net mtd.
     *
     * @param float $netMtd
     *
     * @return ClientTwrPeriod
     */
    public function setNetMtd($netMtd)
    {
        $this->netMtd = $netMtd;

        return $this;
    }

    /**
     * Get mtd.
     *
     * @return float
     */
    public function getNetMtd()
    {
        return $this->netMtd;
    }

    /**
     * Set gross mtd.
     *
     * @param float $grossMtd
     *
     * @return ClientTwrPeriod
     */
    public function setGrossMtd($grossMtd)
    {
        $this->grossMtd = $grossMtd;

        return $this;
    }

    /**
     * Get gross mtd.
     *
     * @return float
     */
    public function getGrossMtd()
    {
        return $this->grossMtd;
    }

    /**
     * Set net qtd.
     *
     * @param float $netQtd
     *
     * @return ClientTwrPeriod
     */
    public function setNetQtd($netQtd)
    {
        $this->netQtd = $netQtd;

        return $this;
    }

    /**
     * Get net qtd.
     *
     * @return float
     */
    public function getNetQtd()
    {
        return $this->netQtd;
    }

    /**
     * Set gross qtd.
     *
     * @param float $grossQtd
     *
     * @return ClientTwrPeriod
     */
    public function setGrossQtd($grossQtd)
    {
        $this->grossQtd = $grossQtd;

        return $this;
    }

    /**
     * Get gross qtd.
     *
     * @return float
     */
    public function getGrossQtd()
    {
        return $this->grossQtd;
    }

    /**
     * Set net ytd.
     *
     * @param float $netYtd
     *
     * @return ClientTwrPeriod
     */
    public function setNetYtd($netYtd)
    {
        $this->netYtd = $netYtd;

        return $this;
    }

    /**
     * Get net ytd.
     *
     * @return float
     */
    public function getNetYtd()
    {
        return $this->netYtd;
    }

    /**
     * Set gross ytd.
     *
     * @param float $grossYtd
     *
     * @return ClientTwrPeriod
     */
    public function setGrossYtd($grossYtd)
    {
        $this->grossYtd = $grossYtd;

        return $this;
    }

    /**
     * Get gross ytd.
     *
     * @return float
     */
    public function getGrossYtd()
    {
        return $this->grossYtd;
    }

    /**
     * Set net yr1.
     *
     * @param float $netYr1
     *
     * @return ClientTwrPeriod
     */
    public function setNetYr1($netYr1)
    {
        $this->netYr1 = $netYr1;

        return $this;
    }

    /**
     * Get net yr1.
     *
     * @return float
     */
    public function getNetYr1()
    {
        return $this->netYr1;
    }

    /**
     * Set gross yr1.
     *
     * @param float $grossYr1
     *
     * @return ClientTwrPeriod
     */
    public function setGrossYr1($grossYr1)
    {
        $this->grossYr1 = $grossYr1;

        return $this;
    }

    /**
     * Get gross yr1.
     *
     * @return float
     */
    public function getGrossYr1()
    {
        return $this->grossYr1;
    }

    /**
     * Set net yr3.
     *
     * @param float $netYr3
     *
     * @return ClientTwrPeriod
     */
    public function setNetYr3($netYr3)
    {
        $this->netYr3 = $netYr3;

        return $this;
    }

    /**
     * Get net yr3.
     *
     * @return float
     */
    public function getNetYr3()
    {
        return $this->netYr3;
    }

    /**
     * Set gross yr3.
     *
     * @param float $grossYr3
     *
     * @return ClientTwrPeriod
     */
    public function setGrossYr3($grossYr3)
    {
        $this->grossYr3 = $grossYr3;

        return $this;
    }

    /**
     * Get gross yr3.
     *
     * @return float
     */
    public function getGrossYr3()
    {
        return $this->grossYr3;
    }

    /**
     * Set net since inception.
     *
     * @param float $netSinceInception
     *
     * @return ClientTwrPeriod
     */
    public function setNetSinceInception($netSinceInception)
    {
        $this->netSinceInception = $netSinceInception;

        return $this;
    }

    /**
     * Get net since inception.
     *
     * @return float
     */
    public function getNetSinceInception()
    {
        return $this->netSinceInception;
    }

    /**
     * Set gross since inception.
     *
     * @param float $grossSinceInception
     *
     * @return ClientTwrPeriod
     */
    public function setGrossSinceInception($grossSinceInception)
    {
        $this->grossSinceInception = $grossSinceInception;

        return $this;
    }

    /**
     * Get gross since inception.
     *
     * @return float
     */
    public function getGrossSinceInception()
    {
        return $this->grossSinceInception;
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
