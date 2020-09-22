<?php

namespace App\Entity;

/**
 * Class AccountTwrPeriod
 * @package App\Entity
 */
class AccountTwrPeriod
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
     * @var string
     */
    protected $accountNumber;

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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * Set account_number.
     *
     * @param string $accountNumber
     *
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * Get account_number.
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }
}
