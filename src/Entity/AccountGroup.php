<?php

namespace App\Entity;

use App\Model\AccountGroup as BaseAccountGroup;
use App\Entity\AccountGroupType as AccountGroupType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class AccountGroup
 * @package App\Entity
 */
class AccountGroup extends BaseAccountGroup
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    protected $name;


    private $types;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->types = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return AccountGroup
     */
    public function setName($name)
    {
        return parent::setName($name);
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return parent::getName();
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
     * @return AccountGroup
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
