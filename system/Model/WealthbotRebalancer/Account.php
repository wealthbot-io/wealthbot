<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Account extends Base {

    const TYPE_PERSONAL_INVESTMENT = 1;
    const TYPE_JOINT_INVESTMENT    = 2;
    const TYPE_ROTH_IRA            = 3;
    const TYPE_TRADITIONAL_IRA     = 4;

    const PRIORITY_TRADITIONAL_IRA = 1;
    const PRIORITY_ROTH_IRA        = 2;
    const PRIORITY_TAXABLE         = 3;

    /** @var string */
    private $type;

    /** @var  string */
    protected $status;

    /** @var  SecurityCollection */
    protected $securities;

    /** @var  bool */
    protected $isFirstTime;

    /** @var  bool */
    protected $isActiveEmployer;

    /** @var  bool */
    protected $isReadyToRebalance;

    /** @var  float */
    protected $scheduledDistribution;

    /** @var  float */
    protected $oneTimeDistribution;

    /** @var  float */
    protected $cashBuffer;

    /** @var  float */
    protected $sasCash;

    /** @var  float */
    protected $billingCash;

    /** @var  float */
    protected $totalCash;

    /** @var  Client */
    protected $client;

    /** @var float */
    private $cashForBuy;

    const STATUS_REGISTERED              = 'registered';
    const STATUS_ACTIVE                  = 'active';
    const STATUS_INIT_REBALANCE          = 'init rebalance';
    const STATUS_INIT_REBALANCE_COMPLETE = 'init rebalance complete';
    const STATUS_REBALANCED              = 'rebalanced';
    const STATUS_ANALYZED                = 'account analyzed';
    const STATUS_CLOSED                  = 'account closed';
    const STATUS_WAITING_ACTIVATION      = 'waiting activation';


    public function __construct()
    {
        $this->isReadyToRebalance = false;
        $this->securities = new SecurityCollection();
        $this->cashForBuy = 0;
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

    /**
     * @param bool $isFirstTime
     * @return $this
     */
    public function setIsFirstTime($isFirstTime)
    {
        $this->isFirstTime = $isFirstTime;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFirstTime()
    {
        return $this->isFirstTime;
    }

    /**
     * @param bool $isActiveEmployer
     * @return $this
     */
    public function setIsActiveEmployer($isActiveEmployer)
    {
        $this->isActiveEmployer = $isActiveEmployer;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActiveEmployer()
    {
        return $this->isActiveEmployer;
    }

    /**
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @return bool
     */
    public function hasPreferredBuySecurities()
    {
        /** @var Security $security */
        foreach ($this->securities as $security) {
            if ($security->getIsPreferredBuy()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return SecurityCollection
     */
    public function findNoPreferredBuySecurities()
    {
        $securityCollection = new SecurityCollection();

        /** @var Security $security */
        foreach ($this->securities as $security) {
            if (!$security->getIsPreferredBuy()) {
                $securityCollection->add($security);
            }
        }

        return $securityCollection;
    }

    /**
     * @return bool
     */
    public function isAllSecuritiesEqualCash()
    {
        if ($this->securities->isEmpty()) {
            return false;
        }

        /** @var Security $security */
        foreach ($this->securities as $security) {
            if (!$security->isTypeCash()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param bool $isReadyToRebalance
     * @return $this
     */
    public function setIsReadyToRebalance($isReadyToRebalance)
    {
        $this->isReadyToRebalance = $isReadyToRebalance;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsReadyToRebalance()
    {
        return $this->isReadyToRebalance;
    }

    /**
     * @param float $sheduledDistribution
     * @return $this
     */
    public function setScheduledDistribution($sheduledDistribution)
    {
        $this->scheduledDistribution = $sheduledDistribution;

        return $this;
    }

    /**
     * @return float
     */
    public function getScheduledDistribution()
    {
        //TODO:
        //Is current date 3 days prior to when
        //distribution is to occur? We must look
        //at start date of transfer and apply the
        //frequency to know when it is to occur.

        return $this->scheduledDistribution;
    }

    /**
     * @param float $oneTimeDistribution
     * @return $this
     */
    public function setOneTimeDistribution($oneTimeDistribution)
    {
        $this->oneTimeDistribution = $oneTimeDistribution;

        return $this;
    }

    /**
     * @return float
     */
    public function getOneTimeDistribution()
    {
        return $this->oneTimeDistribution;
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
     * @param float $totalCash
     * @return $this
     */
    public function setTotalCash($totalCash)
    {
        $this->totalCash = $totalCash;

        return $this;
    }

    /**
     * @return float
     */
    public function getTotalCash()
    {
        return $this->totalCash;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param float $cashForBuy
     * @return $this
     */
    public function setCashForBuy($cashForBuy)
    {
        $this->cashForBuy = $cashForBuy;

        return $this;
    }

    /**
     * @return float
     */
    public function getCashForBuy()
    {
        return $this->cashForBuy;
    }

    /**
     * Update cash for buy
     */
    public function updateCashForBuy()
    {
        $this->cashForBuy = $this->getTotalCash() - $this->calculateCashNeeds();
    }

    /**
     * @return float
     */
    public function calculateDistribution()
    {
        return $this->getScheduledDistribution() + $this->getOneTimeDistribution();
    }

    /**
     * @return bool
     */
    public function isRothIra()
    {
        return $this->type == self::TYPE_ROTH_IRA;
    }

    /**
     * @return bool
     */
    public function isTraditionalIra()
    {
        return $this->type == self::TYPE_TRADITIONAL_IRA;
    }

    /**
     * @return bool
     */
    public function isTaxable()
    {
        return ($this->type == self::TYPE_PERSONAL_INVESTMENT || $this->type == self::TYPE_JOINT_INVESTMENT);
    }

    /**
     * Get priority by account type
     *
     * @return int
     * @throws \Exception
     */
    public function getPriority()
    {
        if ($this->isRothIra()) {
            $priority = self::PRIORITY_ROTH_IRA;
        } elseif ($this->isTraditionalIra()) {
            $priority = self::PRIORITY_TRADITIONAL_IRA;
        } elseif ($this->isTaxable()) {
            $priority = self::PRIORITY_TAXABLE;
        } else {
            throw new \Exception(sprintf('Invalid account type: %s.', $this->type));
        }

        return $priority;
    }

    /**
     * @return float
     */
    public function calculateCashNeeds()
    {
        return ($this->getCashBuffer() + $this->getSasCash() + $this->getBillingCash() + $this->calculateDistribution());
    }

    /**
     * Get total cash needs
     *
     * @return float
     */
    public function getTotalCashNeeds()
    {
        $totalCashNeeds = 0;
        $cashNeeds = $this->calculateCashNeeds();

        if ($cashNeeds > $this->totalCash) {
            $totalCashNeeds = $cashNeeds - $this->totalCash;
        }

        return $totalCashNeeds;
    }

    /**
     * @return float
     */
    public function calculateInvestmentCash()
    {
        return $this->getTotalCash() - $this->calculateCashNeeds();
    }

    public function sellAllSecurities()
    {
        /** @var Security $security */
        foreach ($this->securities as $security) {
            $security->sellAll();
        }
    }

    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($key === 'securities') {
                $securities = new SecurityCollection();
                foreach ($value as $securityData) {
                    $class = 'Model\WealthbotRebalancer\Security';

                    $security = new $class;
                    $security->loadFromArray($securityData);

                    $securities->add($security, $security->getId());
                }

                $this->setSecurities($securities);
            } elseif ($key === 'client') {
                $class = 'Model\WealthbotRebalancer\Client';

                $client = new $class;
                $client->loadFromArray($value);

                $this->setClient($client);
            } else {
                $this->$key = $value;
            }
        }
    }
}