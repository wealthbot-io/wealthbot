<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class Security
 * @package App\Entity
 */
class Security
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $security_type_id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $symbol;

    /**
     * @var float
     */
    private $expense_ratio;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $securityAssignments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $securityPrices;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->securityAssignments = new ArrayCollection();
        $this->securityPrices = new ArrayCollection();
    }

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
     * Set name.
     *
     * @param string $name
     *
     * @return Security
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set symbol.
     *
     * @param string $symbol
     *
     * @return Security
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol.
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Add security assignment.
     *
     * @param \App\Entity\AccountOutsideFund $securities
     *
     * @return Security
     */
    public function addSecurityAssignment(AccountOutsideFund $securities)
    {
        $this->securityAssignments[] = $securities;

        return $this;
    }

    /**
     * Remove security assignment.
     *
     * @param \App\Entity\AccountOutsideFund $securities
     */
    public function removeSecurityAssignment(AccountOutsideFund $securities)
    {
        $this->securityAssignments->removeElement($securities);
    }

    /**
     * Get securityAssignments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSecurityAssignments()
    {
        return $this->securityAssignments;
    }

    /**
     * Set security_type_id.
     *
     * @param int $securityTypeId
     *
     * @return Security
     */
    public function setSecurityTypeId($securityTypeId)
    {
        $this->security_type_id = $securityTypeId;

        return $this;
    }

    /**
     * Get security_type_id.
     *
     * @return int
     */
    public function getSecurityTypeId()
    {
        return $this->security_type_id;
    }

    /**
     * Set expense_ratio.
     *
     * @param float $expenseRatio
     *
     * @return Security
     */
    public function setExpenseRatio($expenseRatio)
    {
        $this->expense_ratio = $expenseRatio;

        return $this;
    }

    /**
     * Get expense_ratio.
     *
     * @return float
     */
    public function getExpenseRatio()
    {
        return $this->expense_ratio;
    }

    /**
     * @param \App\Entity\SecurityType
     */
    private $securityType;

    /**
     * Set securityType.
     *
     * @param \App\Entity\SecurityType $securityType
     *
     * @return Security
     */
    public function setSecurityType(SecurityType $securityType = null)
    {
        $this->securityType = $securityType;

        return $this;
    }

    /**
     * Get securityType.
     *
     * @return \App\Entity\SecurityType
     */
    public function getSecurityType()
    {
        return $this->securityType;
    }

    /**
     * Add securityPrices.
     *
     * @param SecurityPrice $securityPrices
     *
     * @return Security
     */
    public function addSecurityPrice(SecurityPrice $securityPrices)
    {
        $this->securityPrices[] = $securityPrices;

        return $this;
    }

    /**
     * Remove securityPrices.
     *
     * @param SecurityPrice $securityPrices
     */
    public function removeSecurityPrice(SecurityPrice $securityPrices)
    {
        $this->securityPrices->removeElement($securityPrices);
    }

    /**
     * Get securityPrices.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSecurityPrices()
    {
        return $this->securityPrices;
    }

    /**
     * Get security type description.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->getSecurityType() ? $this->getSecurityType()->getDescription() : null;
    }

    /**
     * Get security type code.
     *
     * @return string|null
     */
    public function getTypeCode()
    {
        return $this->getSecurityType() ? $this->getSecurityType()->getName() : null;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
