<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class AccountType
 * @package App\Entity
 */
class AccountType
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $clientInvestmentAccounts;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->clientInvestmentAccounts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->groupTypes = new ArrayCollection();
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
     * @return AccountType
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
     * Set type.
     *
     * @param string $type
     *
     * @return AccountType
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add clientInvestmentAccounts.
     *
     * @param \App\Entity\ClientAccount $clientInvestmentAccounts
     *
     * @return AccountType
     */
    public function addClientInvestmentAccount(ClientAccount $clientInvestmentAccounts)
    {
        $this->clientInvestmentAccounts[] = $clientInvestmentAccounts;

        return $this;
    }

    /**
     * Remove clientInvestmentAccounts.
     *
     * @param \App\Entity\ClientAccount $clientInvestmentAccounts
     */
    public function removeClientInvestmentAccount(ClientAccount $clientInvestmentAccounts)
    {
        $this->clientInvestmentAccounts->removeElement($clientInvestmentAccounts);
    }

    /**
     * Get clientInvestmentAccounts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClientInvestmentAccounts()
    {
        return $this->clientInvestmentAccounts;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $groupTypes;

    /**
     * Add groupTypes.
     *
     * @param \App\Entity\AccountGroupType $groupTypes
     *
     * @return AccountType
     */
    public function addGroupType(AccountGroupType $groupTypes)
    {
        $this->groupTypes[] = $groupTypes;

        return $this;
    }

    /**
     * Remove groupTypes.
     *
     * @param \App\Entity\AccountGroupType $groupTypes
     */
    public function removeGroupType(AccountGroupType $groupTypes)
    {
        $this->groupTypes->removeElement($groupTypes);
    }

    /**
     * Get groupTypes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroupTypes()
    {
        return $this->groupTypes;
    }
}
