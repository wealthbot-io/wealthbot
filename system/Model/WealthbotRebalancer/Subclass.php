<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Subclass extends Base
{
    const ACCOUNT_TYPE_ROTH_IRA        = 'Roth IRA';
    const ACCOUNT_TYPE_TRADITIONAL_IRA = 'Traditional IRA';
    const ACCOUNT_TYPE_TAXABLE         = 'Taxable';

    /** @var  float */
    private $currentAllocation;

    /** @var  float */
    private $targetAllocation;

    /** @var  float */
    private $toleranceBand;

    private $accountType;

    /** @var  int */
    private $priority;

    /** @var  Security */
    private $security;

    /** @var  Security */
    private $muniSecurity;

    /** @var Security */
    private $taxLossHarvesting;


    public function __construct()
    {
        $this->currentAllocation = 0;
    }

    /**
     * @param float $currentAllocation
     * @return $this
     */
    public function setCurrentAllocation($currentAllocation)
    {
        $this->currentAllocation = $currentAllocation;

        return $this;
    }

    /**
     * @return float
     */
    public function getCurrentAllocation()
    {
        return $this->currentAllocation;
    }

    /**
     * @param float $targetAllocation
     * @return $this
     */
    public function setTargetAllocation($targetAllocation)
    {
        $this->targetAllocation = $targetAllocation;

        return $this;
    }

    /**
     * @return float
     */
    public function getTargetAllocation()
    {
        return $this->targetAllocation;
    }

    /**
     * @param float $toleranceBand
     * @return $this
     */
    public function setToleranceBand($toleranceBand)
    {
        $this->toleranceBand = $toleranceBand;

        return $this;
    }

    /**
     * @return float
     */
    public function getToleranceBand()
    {
        return $this->toleranceBand;
    }

    /**
     * @param string $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * @return string
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @return bool
     */
    public function isRothIraAccountType()
    {
        return ($this->accountType == self::ACCOUNT_TYPE_ROTH_IRA);
    }

    /**
     * @return bool
     */
    public function isTraditionalIraAccountType()
    {
        return ($this->accountType == self::ACCOUNT_TYPE_TRADITIONAL_IRA);
    }

    /**
     * @return bool
     */
    public function isTaxableAccountType()
    {
        return ($this->accountType == self::ACCOUNT_TYPE_TAXABLE);
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param Security $security
     * @return $this
     */
    public function setSecurity(Security $security)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * @return Security
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * @param Security $muniSecurity
     * @return $this
     */
    public function setMuniSecurity(Security $muniSecurity)
    {
        $this->muniSecurity = $muniSecurity;

        return $this;
    }

    /**
     * @return Security
     */
    public function getMuniSecurity()
    {
        return $this->muniSecurity;
    }

    /**
     * @param Security $taxLossHarvesting
     * @return $this
     */
    public function setTaxLossHarvesting($taxLossHarvesting)
    {
        $this->taxLossHarvesting = $taxLossHarvesting;

        return $this;
    }

    /**
     * @return Security
     */
    public function getTaxLossHarvesting()
    {
        return $this->taxLossHarvesting;
    }

    /**
     * @return bool
     */
    public function hasTlhFund()
    {
        return (null !== $this->taxLossHarvesting);
    }

    /**
     * @return bool
     */
    public function hasMuniFund()
    {
        return (null !== $this->muniSecurity);
    }

    /**
     * @return float|int
     */
    public function getTotalAmount()
    {
        $totalAmount = 0;
        if ($this->getSecurity()) {
            $totalAmount += $this->getSecurity()->getAmount();
        }

        if ($this->getMuniSecurity()) {
            $totalAmount += $this->getMuniSecurity()->getAmount();
        }

        return $totalAmount;
    }

    /**
     * @return float
     */
    public function calcOOB()
    {
        return $this->currentAllocation - $this->targetAllocation;
    }

    public function loadFromArray(array $data = array())
    {
        $securityKeys = array(
            'security',
            'tax_loss_harvesting',
            'taxLossHarvesting',
            'muni_security',
            'muniSecurity'
        );

        foreach ($data as $key => $value) {
            if (in_array($key, $securityKeys)) {
                $class = 'Model\WealthbotRebalancer\Security';
                $security = new $class;
                $security->loadFromArray($value);

                $this->$key = $security;

            } else {
                $this->$key = $value;
            }
        }
    }

}