<?php

namespace Wealthbot\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Validator\ExecutionContext;

/**
 * Wealthbot\AdminBundle\Entity\Fee
 */
class Fee
{
    const INFINITY = 1000000000000;
    
    /**
     * @var integer
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
     * @var \Wealthbot\AdminBundle\Entity\BillingSpec
     */
    private $billingSpec;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fee_with_retirement
     *
     * @param float $feeWithRetirement
     * @return Fee
     */
    public function setFeeWithRetirement($feeWithRetirement)
    {
        $this->fee_with_retirement = $feeWithRetirement;

        return $this;
    }

    /**
     * Get fee_with_retirement
     *
     * @return float 
     */
    public function getFeeWithRetirement()
    {
        return $this->fee_with_retirement;
    }

    /**
     * Set fee_without_retirement
     *
     * @param float $feeWithoutRetirement
     * @return Fee
     */
    public function setFeeWithoutRetirement($feeWithoutRetirement)
    {
        $this->fee_without_retirement = $feeWithoutRetirement;

        return $this;
    }

    /**
     * Get fee_without_retirement
     *
     * @return float 
     */
    public function getFeeWithoutRetirement()
    {
        return $this->fee_without_retirement;
    }

    /**
     * Set tier_top
     *
     * @param float $tierTop
     * @return Fee
     */
    public function setTierTop($tierTop)
    {
        $this->tier_top = $tierTop;

        return $this;
    }

    /**
     * Get tier_top
     *
     * @return float 
     */
    public function getTierTop()
    {
        return $this->tier_top;
    }

    /**
     * Set billingSpec
     *
     * @param \Wealthbot\AdminBundle\Entity\BillingSpec $billingSpec
     * @return Fee
     */
    public function setBillingSpec(\Wealthbot\AdminBundle\Entity\BillingSpec $billingSpec = null)
    {
        $this->billingSpec = $billingSpec;

        return $this;
    }

    /**
     * Get billingSpec
     *
     * @return \Wealthbot\AdminBundle\Entity\BillingSpec 
     */
    public function getBillingSpec()
    {
        return $this->billingSpec;
    }
}
