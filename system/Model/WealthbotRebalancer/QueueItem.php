<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class QueueItem extends Base {

    /** @var  int */
    private $rebalancer_action_id;

    /** @var  Lot */
    private $lot;

    /** @var  Security */
    private $security;

    /** @var  Account */
    private $account;

    /** @var  int */
    private $quantity;

    /** @var  float */
    private $amount;

    /** @var  string */
    private $status;

    /** @var Subclass */
    private $subclass;

    const STATUS_SELL = 'sell';
    const STATUS_BUY = 'buy';

    /**
     * @param Subclass $subclass
     * @return $this
     */
    public function setSubclass(Subclass $subclass)
    {
        $this->subclass = $subclass;

        return $this;
    }

    /**
     * @return Subclass
     */
    public function getSubclass()
    {
        return $this->subclass;
    }

    /**
     * @param int $rebalancerActionId
     * @return $this
     */
    public function setRebalancerActionId($rebalancerActionId)
    {
        $this->rebalancer_action_id = $rebalancerActionId;

        return $this;
    }

    /**
     * @return int
     */
    public function getRebalancerActionId()
    {
        return $this->rebalancer_action_id;
    }

    /**
     * @param Lot $lot
     * @return $this
     */
    public function setLot(Lot $lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * @return Lot
     */
    public function getLot()
    {
        return $this->lot;
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
     * @param float $amount
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
        return $this->amount;
    }

    /**
     * @param Security $security
     * @return $this
     */
    public function setSecurity($security)
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
     * @param \Model\WealthbotRebalancer\Account $account
     * @return $this
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return \Model\WealthbotRebalancer\Account
     */
    public function getAccount()
    {
        return $this->account;
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
     * @return bool
     */
    public function isStatusSell()
    {
        return ($this->status === self::STATUS_SELL);
    }

    /**
     * @return bool
     */
    public function isStatusBuy()
    {
        return ($this->status === self::STATUS_BUY);
    }

    protected function getRelations()
    {
        return array(
            'security' => 'Model\WealthbotRebalancer\Security',
            'lot'      => 'Model\WealthbotRebalancer\Lot',
            'account'  => 'Model\WealthbotRebalancer\Account',
            'subclass' => 'Model\WealthbotRebalancer\Subclass'
        );
    }


}