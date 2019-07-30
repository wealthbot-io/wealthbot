<?php

namespace App\Entity;

/**
 * Class Fee
 * @package App\Entity
 */
class Fee
{
    const INFINITY = 1000000000000;

    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $fee_with_retirement;

    /**
     * @var float
     */
    private $fee_without_retirement;

    /**
     * @var float
     */
    private $tier_top;

    /**
     * @var float
     */
    private $tier_bottom;

    /**
     * @var float
     */
    private $is_final_tier;

    /**
     * @param \App\Entity\BillingSpec
     */
    private $billingSpec;

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
     * Set fee_with_retirement.
     *
     * @param float $feeWithRetirement
     *
     * @return Fee
     */
    public function setFeeWithRetirement($feeWithRetirement)
    {
        $this->fee_with_retirement = $feeWithRetirement;

        return $this;
    }

    /**
     * Get fee_with_retirement.
     *
     * @return float
     */
    public function getFeeWithRetirement()
    {
        return $this->fee_with_retirement;
    }

    /**
     * Set fee_without_retirement.
     *
     * @param float $feeWithoutRetirement
     *
     * @return Fee
     */
    public function setFeeWithoutRetirement($feeWithoutRetirement)
    {
        $this->fee_without_retirement = $feeWithoutRetirement;

        return $this;
    }

    /**
     * Get fee_without_retirement.
     *
     * @return float
     */
    public function getFeeWithoutRetirement()
    {
        return $this->fee_without_retirement;
    }

    /**
     * Set tier_top.
     *
     * @param float $tierTop
     *
     * @return Fee
     */
    public function setTierTop($tierTop)
    {
        $this->tier_top = $tierTop;

        return $this;
    }

    /**
     * Get tier_top.
     *
     * @return float
     */
    public function getTierTop()
    {
        return $this->tier_top;
    }

    /**
     * Set tier_bottom.
     *
     * @param float $tierBottom
     *
     * @return Fee
     */
    public function setTierBottom($tierBottom)
    {
        $this->tier_bottom = $tierBottom;

        return $this;
    }

    /**
     * Get tier_bottom.
     *
     * @return float
     */
    public function getTierBottom()
    {
        return $this->tier_bottom;
    }

    /**
     * Set billingSpec.
     *
     * @param \App\Entity\BillingSpec $billingSpec
     *
     * @return Fee
     */
    public function setBillingSpec(BillingSpec $billingSpec = null)
    {
        $this->billingSpec = $billingSpec;

        return $this;
    }

    /**
     * Get billingSpec.
     *
     * @return \App\Entity\BillingSpec
     */
    public function getBillingSpec()
    {
        return $this->billingSpec;
    }

    /**
     * Set is_final_tier.
     *
     * @param float $isFinalTier
     *
     * @return Fee
     */
    public function setIsFinalTier($isFinalTier)
    {
        $this->is_final_tier = $isFinalTier;

        return $this;
    }

    /**
     * Get is_final_tier.
     *
     * @return float
     */
    public function getIsFinalTier()
    {
        return $this->is_final_tier;
    }

    /**
     * @var App\Entity\User
     */
   // private $appointedUser;
}
