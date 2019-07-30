<?php

namespace App\Entity;

/**
 * Class SecurityTransaction
 * @package App\Entity
 */
class SecurityTransaction
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $security_assignment_id;

    /**
     * @var float
     */
    private $transaction_fee;

    /**
     * @var float
     */
    private $transaction_fee_percent;

    /**
     * @var float
     */
    private $minimum_buy;

    /**
     * @var float
     */
    private $minimum_initial_buy;

    /**
     * @var float
     */
    private $minimum_sell;

    /**
     * @var int
     */
    private $redemption_penalty_interval;

    /**
     * @var float
     */
    private $redemption_fee;

    /**
     * @var float
     */
    private $redemption_percent;

    /**
     * @param \App\Entity\SecurityAssignment
     */
    private $securityAssignment;

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
     * Set security_assignment_id.
     *
     * @param int $securityAssignmentId
     *
     * @return SecurityTransaction
     */
    public function setSecurityAssignmentId($securityAssignmentId)
    {
        $this->security_assignment_id = $securityAssignmentId;

        return $this;
    }

    /**
     * Get security_assignment_id.
     *
     * @return int
     */
    public function getSecurityAssignmentId()
    {
        return $this->security_assignment_id;
    }

    /**
     * Set transaction_fee.
     *
     * @param float $transactionFee
     *
     * @return SecurityTransaction
     */
    public function setTransactionFee($transactionFee)
    {
        $this->transaction_fee = $transactionFee;

        return $this;
    }

    /**
     * Get transaction_fee.
     *
     * @return float
     */
    public function getTransactionFee()
    {
        return $this->transaction_fee;
    }

    /**
     * Set transaction_fee_percent.
     *
     * @param float $transactionFeePercent
     *
     * @return SecurityTransaction
     */
    public function setTransactionFeePercent($transactionFeePercent)
    {
        $this->transaction_fee_percent = $transactionFeePercent;

        return $this;
    }

    /**
     * Get transaction_fee_percent.
     *
     * @return float
     */
    public function getTransactionFeePercent()
    {
        return $this->transaction_fee_percent;
    }

    /**
     * Set minimum_buy.
     *
     * @param float $minimumBuy
     *
     * @return SecurityTransaction
     */
    public function setMinimumBuy($minimumBuy)
    {
        $this->minimum_buy = $minimumBuy;

        return $this;
    }

    /**
     * Get minimum_buy.
     *
     * @return float
     */
    public function getMinimumBuy()
    {
        return $this->minimum_buy;
    }

    /**
     * Set minimum_initial_buy.
     *
     * @param float $minimumInitialBuy
     *
     * @return SecurityTransaction
     */
    public function setMinimumInitialBuy($minimumInitialBuy)
    {
        $this->minimum_initial_buy = $minimumInitialBuy;

        return $this;
    }

    /**
     * Get minimum_initial_buy.
     *
     * @return float
     */
    public function getMinimumInitialBuy()
    {
        return $this->minimum_initial_buy;
    }

    /**
     * Set minimum_sell.
     *
     * @param float $minimumSell
     *
     * @return SecurityTransaction
     */
    public function setMinimumSell($minimumSell)
    {
        $this->minimum_sell = $minimumSell;

        return $this;
    }

    /**
     * Get minimum_sell.
     *
     * @return float
     */
    public function getMinimumSell()
    {
        return $this->minimum_sell;
    }

    /**
     * Set redemption_penalty_interval.
     *
     * @param int $redemptionPenaltyInterval
     *
     * @return SecurityTransaction
     */
    public function setRedemptionPenaltyInterval($redemptionPenaltyInterval)
    {
        $this->redemption_penalty_interval = $redemptionPenaltyInterval;

        return $this;
    }

    /**
     * Get redemption_penalty_interval.
     *
     * @return int
     */
    public function getRedemptionPenaltyInterval()
    {
        return $this->redemption_penalty_interval;
    }

    /**
     * Set redemption_fee.
     *
     * @param float $redemptionFee
     *
     * @return SecurityTransaction
     */
    public function setRedemptionFee($redemptionFee)
    {
        $this->redemption_fee = $redemptionFee;

        return $this;
    }

    /**
     * Get redemption_fee.
     *
     * @return float
     */
    public function getRedemptionFee()
    {
        return $this->redemption_fee;
    }

    /**
     * Set redemption_percent.
     *
     * @param float $redemptionPercent
     *
     * @return SecurityTransaction
     */
    public function setRedemptionPercent($redemptionPercent)
    {
        $this->redemption_percent = $redemptionPercent;

        return $this;
    }

    /**
     * Get redemption_percent.
     *
     * @return float
     */
    public function getRedemptionPercent()
    {
        return $this->redemption_percent;
    }

    /**
     * Set securityAssignment.
     *
     * @param \App\Entity\SecurityAssignment $securityAssignment
     *
     * @return SecurityTransaction
     */
    public function setSecurityAssignment(SecurityAssignment $securityAssignment = null)
    {
        $this->securityAssignment = $securityAssignment;

        return $this;
    }

    /**
     * Get securityAssignment.
     *
     * @return \App\Entity\SecurityAssignment
     */
    public function getSecurityAssignment()
    {
        return $this->securityAssignment;
    }
}
