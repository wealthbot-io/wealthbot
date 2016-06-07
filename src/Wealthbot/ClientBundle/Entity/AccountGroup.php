<?php

namespace Wealthbot\ClientBundle\Entity;

use Wealthbot\ClientBundle\Model\AccountGroup as BaseAccountGroup;

/**
 * AccountGroup.
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

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->types = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \Wealthbot\ClientBundle\Entity\AccountGroupType $groupTypes
     *
     * @return AccountGroup
     */
    public function addGroupType(\Wealthbot\ClientBundle\Entity\AccountGroupType $groupTypes)
    {
        $this->groupTypes[] = $groupTypes;

        return $this;
    }

    /**
     * Remove groupTypes.
     *
     * @param \Wealthbot\ClientBundle\Entity\AccountGroupType $groupTypes
     */
    public function removeGroupType(\Wealthbot\ClientBundle\Entity\AccountGroupType $groupTypes)
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
