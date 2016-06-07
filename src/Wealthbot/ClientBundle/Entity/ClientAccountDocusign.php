<?php

namespace Wealthbot\ClientBundle\Entity;

/**
 * ClientAccountDocusign.
 */
class ClientAccountDocusign
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client_account_id;

    /**
     * @var bool
     */
    private $is_used;

    /**
     * @var \Wealthbot\ClientBundle\Model\ClientAccount
     */
    private $clientAccount;

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
     * Set client_account_id.
     *
     * @param int $clientAccountId
     *
     * @return ClientAccountDocusign
     */
    public function setClientAccountId($clientAccountId)
    {
        $this->client_account_id = $clientAccountId;

        return $this;
    }

    /**
     * Get client_account_id.
     *
     * @return int
     */
    public function getClientAccountId()
    {
        return $this->client_account_id;
    }

    /**
     * Set is_used.
     *
     * @param bool $isUsed
     *
     * @return ClientAccountDocusign
     */
    public function setIsUsed($isUsed)
    {
        $this->is_used = $isUsed;

        return $this;
    }

    /**
     * Get is_used.
     *
     * @return bool
     */
    public function getIsUsed()
    {
        return $this->is_used;
    }

    /**
     * Set clientAccount.
     *
     * @param \Wealthbot\ClientBundle\Model\ClientAccount $clientAccount
     *
     * @return ClientAccountDocusign
     */
    public function setClientAccount(\Wealthbot\ClientBundle\Model\ClientAccount $clientAccount = null)
    {
        $this->clientAccount = $clientAccount;

        return $this;
    }

    /**
     * Get clientAccount.
     *
     * @return \Wealthbot\ClientBundle\Model\ClientAccount
     */
    public function getClientAccount()
    {
        return $this->clientAccount;
    }
}
