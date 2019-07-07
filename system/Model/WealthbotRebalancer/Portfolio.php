<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Portfolio extends Base {

    /** @var  SecurityCollection */
    private $securities;

    /** @var float */
    private $totalValue;

    /** @var float */
    private $totalInSecurities;

    /** @var float */
    private $totalCashInAccounts;

    /** @var float */
    private $totalCashInMoneyMarket;

    /** @var float */
    private $sasCash;

    /** @var float */
    private $cashBuffer;

    /** @var float */
    private $billingCash;


    public function __construct()
    {
        $this->securities = new SecurityCollection();
    }

    /**
     * @param SecurityCollection $securities
     * @return $this
     */
    public function setSecurities(SecurityCollection $securities)
    {
        $this->securities = $securities;

        return $this;
    }

    /**
     * @return SecurityCollection
     */
    public function getSecurities()
    {
        return $this->securities;
    }

    /**
     * @param float $billingCash
     * @return $this
     */
    public function setBillingCash($billingCash)
    {
        $this->billingCash = $billingCash;

        return $this;
    }

    /**
     * @return float
     */
    public function getBillingCash()
    {
        return $this->billingCash;
    }

    /**
     * @param float $cashBuffer
     * @return $this
     */
    public function setCashBuffer($cashBuffer)
    {
        $this->cashBuffer = $cashBuffer;

        return $this;
    }

    /**
     * @return float
     */
    public function getCashBuffer()
    {
        return $this->cashBuffer;
    }

    /**
     * @param float $sasCash
     * @return $this
     */
    public function setSasCash($sasCash)
    {
        $this->sasCash = $sasCash;

        return $this;
    }

    /**
     * @return float
     */
    public function getSasCash()
    {
        return $this->sasCash;
    }

    /**
     * @param float $totalCashInAccount
     * @return $this
     */
    public function setTotalCashInAccounts($totalCashInAccount)
    {
        $this->totalCashInAccounts = $totalCashInAccount;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalCashInAccounts()
    {
        return $this->totalCashInAccounts;
    }

    /**
     * @param float $totalCashInMoneyMarket
     * @return $this
     */
    public function setTotalCashInMoneyMarket($totalCashInMoneyMarket)
    {
        $this->totalCashInMoneyMarket = $totalCashInMoneyMarket;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalCashInMoneyMarket()
    {
        return $this->totalCashInMoneyMarket;
    }

    /**
     * @param float $totalInSecurities
     * @return $this
     */
    public function setTotalInSecurities($totalInSecurities)
    {
        $this->totalInSecurities = $totalInSecurities;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalInSecurities()
    {
        return $this->totalInSecurities;
    }

    /**
     * @param float $totalValue
     * @return $this
     */
    public function setTotalValue($totalValue)
    {
        $this->totalValue = $totalValue;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalValue()
    {
        return $this->totalValue;
    }


    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($key === 'securities') {
                $securities = new SecurityCollection();
                foreach ($value as $clientData) {
                    $class = 'Model\WealthbotRebalancer\Security';

                    $security = new $class;
                    $security->loadFromArray($clientData);

                    $securities->add($security, $security->getId());
                }

                $this->setSecurities($securities);
            } else {
                $this->$key = $value;
            }
        }
    }

}