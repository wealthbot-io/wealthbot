<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class AccountGroupType
 * @package App\Entity
 */
class AccountGroupType
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $group_id;

    /**
     * @var int
     */
    private $type_id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $clientAccounts;

    /**
     * @param \App\Entity\AccountGroup
     */
    private $group;

    /**
     * @param \App\Entity\AccountType
     */
    private $type;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clientAccounts = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set group_id.
     *
     * @param int $groupId
     *
     * @return AccountGroupType
     */
    public function setGroupId($groupId)
    {
        $this->group_id = $groupId;

        return $this;
    }

    /**
     * Get group_id.
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->group_id;
    }

    /**
     * Set type_id.
     *
     * @param int $typeId
     *
     * @return AccountGroupType
     */
    public function setTypeId($typeId)
    {
        $this->type_id = $typeId;

        return $this;
    }

    /**
     * Get type_id.
     *
     * @return int
     */
    public function getTypeId()
    {
        return $this->type_id;
    }

    /**
     * Add clientAccounts.
     *
     * @param \App\Entity\ClientAccount $clientAccounts
     *
     * @return AccountGroupType
     */
    public function addClientAccount(ClientAccount $clientAccounts)
    {
        $this->clientAccounts[] = $clientAccounts;

        return $this;
    }

    /**
     * Remove clientAccounts.
     *
     * @param \App\Entity\ClientAccount $clientAccounts
     */
    public function removeClientAccount(ClientAccount $clientAccounts)
    {
        $this->clientAccounts->removeElement($clientAccounts);
    }

    /**
     * Get clientAccounts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClientAccounts()
    {
        return $this->clientAccounts;
    }

    /**
     * Set group.
     *
     * @param \App\Entity\AccountGroup $group
     *
     * @return AccountGroupType
     */
    public function setGroup(AccountGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return \App\Entity\AccountGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set type.
     *
     * @param \App\Entity\AccountType $type
     *
     * @return AccountGroupType
     */
    public function setType(AccountType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \App\Entity\AccountType
     */
    public function getType()
    {
        return $this->type;
    }


    public function __toString()
    {
        return $this->type ? $this->getType()->getName() : '____';
    }
}
