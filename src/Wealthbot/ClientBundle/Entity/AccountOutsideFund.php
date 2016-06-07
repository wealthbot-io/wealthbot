<?php

namespace Wealthbot\ClientBundle\Entity;

/**
 * AccountOutsideFund.
 *
 * @deprecated
 */
class AccountOutsideFund
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $account_id;

    /**
     * @var \Wealthbot\ClientBundle\Entity\ClientAccount
     */
    private $account;

    /**
     * @var int
     */
    private $security_assignment_id;

    /**
     * @var \Wealthbot\AdminBundle\Entity\SecurityAssignment
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
     * Set account_id.
     *
     * @param int $accountId
     *
     * @return AccountOutsideFund
     */
    public function setAccountId($accountId)
    {
        $this->account_id = $accountId;

        return $this;
    }

    /**
     * Get account_id.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Set account.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientAccount $account
     *
     * @return AccountOutsideFund
     */
    public function setAccount(\Wealthbot\ClientBundle\Entity\ClientAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \Wealthbot\ClientBundle\Entity\ClientAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set security_assignment_id.
     *
     * @param int $securityId
     *
     * @return AccountOutsideFund
     */
    public function setSecurityAssignmentId($securityId)
    {
        $this->security_assignment_id = $securityId;

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
     * Set securityAssignment.
     *
     * @param \Wealthbot\AdminBundle\Entity\SecurityAssignment $securityAssignment
     *
     * @return AccountOutsideFund
     */
    public function setSecurityAssignment(\Wealthbot\AdminBundle\Entity\SecurityAssignment $securityAssignment = null)
    {
        $this->securityAssignment = $securityAssignment;

        return $this;
    }

    /**
     * Get securityAssignment.
     *
     * @return \Wealthbot\AdminBundle\Entity\SecurityAssignment
     */
    public function getSecurityAssignment()
    {
        return $this->securityAssignment;
    }
    /**
     * @var bool
     */
    private $is_preferred;

    /**
     * Set is_preferred.
     *
     * @param bool $isPreferred
     *
     * @return AccountOutsideFund
     */
    public function setIsPreferred($isPreferred)
    {
        $this->is_preferred = $isPreferred;

        return $this;
    }

    /**
     * Get is_preferred.
     *
     * @return bool
     */
    public function getIsPreferred()
    {
        return $this->is_preferred;
    }
}
