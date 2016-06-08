<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Transaction extends Base {

    const TYPE_SELL = 'sell';
    const TYPE_BUY  = 'buy';

    /** @var TransactionType */
	private $transactionType;

	// is transaction a gain or loss (for "sell" tx only)
    /** @var  bool */
	private $isGain;

	//total amount of transaction buy or sell
    /** @var  float */
	private $grossAmount;

    /** @var Account */
    private $account;

    /** @var Security */
    private $security;

    /** @var Lot */
    private $lot;

	/**
     * @param TransactionType $transactionType
     * @return $this
     */
    public function setTransactionType(TransactionType $transactionType)
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * @return TransactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Is transaction type is sell
     *
     * @return bool
     */
    public function isTypeSell()
    {
        return ($this->transactionType->getName() === self::TYPE_SELL);
    }

    /**
     * Is transaction type is buy
     *
     * @return bool
     */
    public function isTypeBuy()
    {
        return ($this->transactionType->getName() === self::TYPE_BUY);
    }

    /**
     * @param bool $isGain
     * @return $this
     */
    public function setIsGain($isGain) {
    	$this->isGain = $isGain;

    	return $this;
    }

    /**
     * @return bool isGain
     */
    public function getIsGain()
    {
        return $this->isGain;
    }

    /**
     * @param float $grossAmount
     * @return $this
     */
    public function setGrossAmount($grossAmount) {
    	$this->grossAmount = $grossAmount;

    	return $this;
    }

    /**
     * @return float grossAmount
     */
    public function getGrossAmount()
    {
        return $this->grossAmount;
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
     * @param \Model\WealthbotRebalancer\Security $security
     * @return $this
     */
    public function setSecurity($security)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * @return \Model\WealthbotRebalancer\Security
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * @param \Model\WealthbotRebalancer\Lot $lot
     * @return $this
     */
    public function setLot($lot)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * @return \Model\WealthbotRebalancer\Lot
     */
    public function getLot()
    {
        return $this->lot;
    }


    protected function getRelations()
    {
        return array(
            'transactionType' => 'Model\WealthbotRebalancer\TransactionType',
            'account' => 'Model\WealthbotRebalancer\Account',
            'security' => 'Model\WealthbotRebalancer\Security',
            'lot' => 'Model\WealthbotRebalancer\Lot'
        );
    }

}