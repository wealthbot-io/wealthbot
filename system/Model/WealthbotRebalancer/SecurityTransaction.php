<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class SecurityTransaction extends Base
{
    /** @var float */
    private $redemptionFee;

    /** @var int */
    private $redemptionPenaltyInterval;

    private $minimumBuy;

    private $minimumSell;

    /** @var float */
    private $initMinBuyAmount;

    /**
     * @param float $redemptionFee
     * @return $this
     */
    public function setRedemptionFee($redemptionFee)
    {
        $this->redemptionFee = $redemptionFee;

        return $this;
    }

    /**
     * @return float
     */
    public function getRedemptionFee()
    {
        return $this->redemptionFee;
    }

    /**
     * @return bool
     */
    public function isRedemptionFeeSpecified()
    {
        return ($this->redemptionFee && $this->redemptionFee > 0);
    }

    /**
     * @param int $redemptionPenaltyInterval
     * @return $this
     */
    public function setRedemptionPenaltyInterval($redemptionPenaltyInterval)
    {
        $this->redemptionPenaltyInterval = $redemptionPenaltyInterval;

        return $this;
    }

    /**
     * @return int
     */
    public function getRedemptionPenaltyInterval()
    {
        return $this->redemptionPenaltyInterval;
    }

    /**
     * @param double $minimumBuy
     * @return $this
     */
    public function setMinimumBuy($minimumBuy)
    {
        $this->minimumBuy = $minimumBuy;

        return $this;
    }

    /**
     * @return double
     */
    public function getMinimumBuy()
    {
        return $this->minimumBuy;
    }

    /**
     * @param double $minimumSell
     * @return $this
     */
    public function setMinimumSell($minimumSell)
    {
        $this->minimumSell = $minimumSell;

        return $this;
    }

    /**
     * @return double
     */
    public function getMinimumSell()
    {
        return $this->minimumSell;
    }

    /**
     * @param float $initMinBuyAmount
     * @return $this
     */
    public function setInitMinBuyAmount($initMinBuyAmount)
    {
        $this->initMinBuyAmount = $initMinBuyAmount;

        return $this;
    }

    /**
     * @return float
     */
    public function getInitMinBuyAmount()
    {
        return $this->initMinBuyAmount;
    }

}