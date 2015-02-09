<?php

namespace Wealthbot\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientAccountDocusign
 */
class ClientAccountDocusign
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $client_account_id;

    /**
     * @var boolean
     */
    private $is_used;

    /**
     * @var \Wealthbot\ClientBundle\Model\ClientAccount
     */
    private $clientAccount;


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
     * Set client_account_id
     *
     * @param integer $clientAccountId
     * @return ClientAccountDocusign
     */
    public function setClientAccountId($clientAccountId)
    {
        $this->client_account_id = $clientAccountId;
    
        return $this;
    }

    /**
     * Get client_account_id
     *
     * @return integer 
     */
    public function getClientAccountId()
    {
        return $this->client_account_id;
    }

    /**
     * Set is_used
     *
     * @param boolean $isUsed
     * @return ClientAccountDocusign
     */
    public function setIsUsed($isUsed)
    {
        $this->is_used = $isUsed;
    
        return $this;
    }

    /**
     * Get is_used
     *
     * @return boolean 
     */
    public function getIsUsed()
    {
        return $this->is_used;
    }

    /**
     * Set clientAccount
     *
     * @param \Wealthbot\ClientBundle\Model\ClientAccount $clientAccount
     * @return ClientAccountDocusign
     */
    public function setClientAccount(\Wealthbot\ClientBundle\Model\ClientAccount $clientAccount = null)
    {
        $this->clientAccount = $clientAccount;
    
        return $this;
    }

    /**
     * Get clientAccount
     *
     * @return \Wealthbot\ClientBundle\Model\ClientAccount
     */
    public function getClientAccount()
    {
        return $this->clientAccount;
    }
}
