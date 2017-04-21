<?php

namespace Wealthbot\ClientBundle\Entity;

/**
 * AccountGroupType.
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
     * @var \Wealthbot\ClientBundle\Entity\AccountGroup
     */
    private $group;

    /**
     * @var \Wealthbot\ClientBundle\Entity\AccountType
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
     * @param \Wealthbot\ClientBundle\Entity\ClientAccount $clientAccounts
     *
     * @return AccountGroupType
     */
    public function addClientAccount(\Wealthbot\ClientBundle\Entity\ClientAccount $clientAccounts)
    {
        $this->clientAccounts[] = $clientAccounts;

        return $this;
    }

    /**
     * Remove clientAccounts.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientAccount $clientAccounts
     */
    public function removeClientAccount(\Wealthbot\ClientBundle\Entity\ClientAccount $clientAccounts)
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
     * @param \Wealthbot\ClientBundle\Entity\AccountGroup $group
     *
     * @return AccountGroupType
     */
    public function setGroup(\Wealthbot\ClientBundle\Entity\AccountGroup $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return \Wealthbot\ClientBundle\Entity\AccountGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set type.
     *
     * @param \Wealthbot\ClientBundle\Entity\AccountType $type
     *
     * @return AccountGroupType
     */
    public function setType(\Wealthbot\ClientBundle\Entity\AccountType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \Wealthbot\ClientBundle\Entity\AccountType
     */
    public function getType()
    {
        return $this->type;
    }
}
