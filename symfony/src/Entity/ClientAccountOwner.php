<?php

namespace App\Entity;

use App\Model\AccountOwnerInterface;
use App\Model\ClientAccountOwner as BaseClientAccountOwner;
use App\Model\UserAccountOwnerAdapter;

/**
 * Class ClientAccountOwner
 * @package App\Entity
 */
class ClientAccountOwner extends BaseClientAccountOwner
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
     * @var string
     */
    protected $owner_type;

    /**
     * @param \App\Entity\ClientAccount
     */
    private $account;

    /**
     * @var int
     */
    private $owner_contact_id;

    /**
     * @param \App\Entity\ClientAdditionalContact
     */
    private $contact;

    /**
     * @var int
     */
    private $owner_client_id;

    /**
     * @param \App\Entity\User
     */
    private $client;

    /**
     * Constructor.
     */
    public function __construct()
    {
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
     * Set account_id.
     *
     * @param int $accountId
     *
     * @return ClientAccountOwner
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
     * @param \App\Entity\ClientAccount $account
     *
     * @return ClientAccountOwner
     */
    public function setAccount(ClientAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \App\Entity\ClientAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set contact.
     *
     * @param \App\Entity\ClientAdditionalContact $contact
     *
     * @return ClientAccountOwner
     */
    public function setContact(ClientAdditionalContact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact.
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set owner_type.
     *
     * @param string $ownerType
     *
     * @return ClientAccountOwner
     */
    public function setOwnerType($ownerType)
    {
        parent::setOwnerType($ownerType);

        return $this;
    }

    /**
     * Get owner_type.
     *
     * @return string
     */
    public function getOwnerType()
    {
        return parent::getOwnerType();
    }

    /**
     * Set owner_contact_id.
     *
     * @param int $ownerContactId
     *
     * @return ClientAccountOwner
     */
    public function setOwnerContactId($ownerContactId)
    {
        $this->owner_contact_id = $ownerContactId;

        return $this;
    }

    /**
     * Get owner_contact_id.
     *
     * @return int
     */
    public function getOwnerContactId()
    {
        return $this->owner_contact_id;
    }

    /**
     * Set owner_client_id.
     *
     * @param int $ownerClientId
     *
     * @return ClientAccountOwner
     */
    public function setOwnerClientId($ownerClientId)
    {
        $this->owner_client_id = $ownerClientId;

        return $this;
    }

    /**
     * Get owner_client_id.
     *
     * @return int
     */
    public function getOwnerClientId()
    {
        return $this->owner_client_id;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return ClientAccountOwner
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get owner.
     *
     * @return AccountOwnerInterface
     */
    public function getOwner()
    {
        $type = $this->getOwnerType();
        if (self::OWNER_TYPE_SELF === $type) {
            $owner = new UserAccountOwnerAdapter($this->getClient());
        } else {
            $owner = $this->getContact();
        }

        /*if (null !== $this->getOwnerClientId()) {
            $owner = new UserAccountOwnerAdapter($this->getClient());
        } else {
            $owner = $this->getContact();
        }*/

        return $owner;
    }
}
