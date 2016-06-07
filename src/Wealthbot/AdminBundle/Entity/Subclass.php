<?php

namespace Wealthbot\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Wealthbot\AdminBundle\Entity\Subclass.
 */
class Subclass
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
     * @var float
     */
    private $expected_performance;

    /**
     * @var int
     */
    private $asset_class_id;

    /**
     * @var AssetClass
     */
    private $assetClass;

    /**
     * @var int
     */
    private $account_type_id;

    /**
     * @var \Wealthbot\RiaBundle\Entity\SubclassAccountType
     */
    private $accountType;

    /**
     * @var int
     */
    private $owner_id;

    /**
     * @var int
     */
    private $source_id;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    private $owner;

    /**
     * @var \Wealthbot\AdminBundle\Entity\Subclass
     */
    private $source;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $targets;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $securityAssignments;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ceModelEntities;

    /**
     * @var int
     */
    private $tolerance_band;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->targets = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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
     * @return Subclass
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
     * Set expected_performance.
     *
     * @param float $expectedPerformance
     *
     * @return Subclass
     */
    public function setExpectedPerformance($expectedPerformance)
    {
        $this->expected_performance = $expectedPerformance;

        return $this;
    }

    /**
     * Get expected_performance.
     *
     * @return float
     */
    public function getExpectedPerformance()
    {
        return $this->expected_performance;
    }

    /**
     * Set asset_class_id.
     *
     * @param int $assetClassId
     *
     * @return Subclass
     */
    public function setAssetClassId($assetClassId)
    {
        $this->asset_class_id = $assetClassId;

        return $this;
    }

    /**
     * Get asset_class_id.
     *
     * @return int
     */
    public function getAssetClassId()
    {
        return $this->asset_class_id;
    }

    /**
     * Set assetClass.
     *
     * @param \Wealthbot\AdminBundle\Entity\AssetClass $assetClass
     *
     * @return Subclass
     */
    public function setAssetClass(\Wealthbot\AdminBundle\Entity\AssetClass $assetClass = null)
    {
        $this->assetClass = $assetClass;

        return $this;
    }

    /**
     * Get assetClass.
     *
     * @return \Wealthbot\AdminBundle\Entity\AssetClass
     */
    public function getAssetClass()
    {
        return $this->assetClass;
    }

    /**
     * Set account_type_id.
     *
     * @param int $accountTypeId
     *
     * @return Subclass
     */
    public function setAccountTypeId($accountTypeId)
    {
        $this->account_type_id = $accountTypeId;

        return $this;
    }

    /**
     * Get account_type_id.
     *
     * @return int
     */
    public function getAccountTypeId()
    {
        return $this->account_type_id;
    }

    /**
     * Set accountType.
     *
     * @param \Wealthbot\RiaBundle\Entity\SubclassAccountType $accountType
     *
     * @return Subclass
     */
    public function setAccountType(\Wealthbot\RiaBundle\Entity\SubclassAccountType $accountType = null)
    {
        $this->accountType = $accountType;

        return $this;
    }

    /**
     * Get accountType.
     *
     * @return \Wealthbot\RiaBundle\Entity\SubclassAccountType
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * Set owner_id.
     *
     * @param int $ownerId
     *
     * @return Subclass
     */
    public function setOwnerId($ownerId)
    {
        $this->owner_id = $ownerId;

        return $this;
    }

    /**
     * Get owner_id.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * Set source_id.
     *
     * @param int $sourceId
     *
     * @return Subclass
     */
    public function setSourceId($sourceId)
    {
        $this->source_id = $sourceId;

        return $this;
    }

    /**
     * Get source_id.
     *
     * @return int
     */
    public function getSourceId()
    {
        return $this->source_id;
    }

    /**
     * Set owner.
     *
     * @param \Wealthbot\UserBundle\Entity\User $owner
     *
     * @return Subclass
     */
    public function setOwner(\Wealthbot\UserBundle\Entity\User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return \Wealthbot\UserBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set source.
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $source
     *
     * @return Subclass
     */
    public function setSource(\Wealthbot\AdminBundle\Entity\Subclass $source = null)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return \Wealthbot\AdminBundle\Entity\Subclass
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Add targets.
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $targets
     *
     * @return Subclass
     */
    public function addTarget(\Wealthbot\AdminBundle\Entity\Subclass $targets)
    {
        $this->targets[] = $targets;

        return $this;
    }

    /**
     * Remove targets.
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $targets
     */
    public function removeTarget(\Wealthbot\AdminBundle\Entity\Subclass $targets)
    {
        $this->targets->removeElement($targets);
    }

    /**
     * Get targets.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * Add security assignment.
     *
     * @param \Wealthbot\AdminBundle\Entity\SecurityAssignment $securities
     *
     * @return Subclass
     */
    public function addSecurityAssignment(\Wealthbot\AdminBundle\Entity\SecurityAssignment $securities)
    {
        $this->securityAssignments[] = $securities;

        return $this;
    }

    /**
     * Remove security assignment.
     *
     * @param \Wealthbot\AdminBundle\Entity\SecurityAssignment $securities
     */
    public function removeSecurityAssignment(\Wealthbot\AdminBundle\Entity\SecurityAssignment $securities)
    {
        $this->securityAssignments->removeElement($securities);
    }

    /**
     * Get securityAssignments.
     *
     * @return ArrayCollection|SecurityAssignment[]
     */
    public function getSecurityAssignments()
    {
        return $this->securityAssignments;
    }

    /**
     * Set priority.
     *
     * @param int $priority
     *
     * @return Subclass
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get priority.
     *
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Add ceModelEntities.
     *
     * @param \Wealthbot\AdminBundle\Entity\CeModelEntity $ceModelEntities
     *
     * @return Subclass
     */
    public function addCeModelEntitie(\Wealthbot\AdminBundle\Entity\CeModelEntity $ceModelEntities)
    {
        $this->ceModelEntities[] = $ceModelEntities;

        return $this;
    }

    /**
     * Remove ceModelEntities.
     *
     * @param \Wealthbot\AdminBundle\Entity\CeModelEntity $ceModelEntities
     */
    public function removeCeModelEntitie(\Wealthbot\AdminBundle\Entity\CeModelEntity $ceModelEntities)
    {
        $this->ceModelEntities->removeElement($ceModelEntities);
    }

    /**
     * Get ceModelEntities.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCeModelEntities()
    {
        return $this->ceModelEntities;
    }

    /**
     * Returns copied instance of Subclass.
     *
     * @return self
     */
    public function getCopy()
    {
        $clone = clone $this;

        $clone->id = null;
        $clone->targets = new \Doctrine\Common\Collections\ArrayCollection();

        $clone->setAssetClass($this->getAssetClass());
        $clone->setAccountType($this->getAccountType());
        $clone->setSource($this);
        $clone->setName($this->getName());
        $clone->setExpectedPerformance($this->getExpectedPerformance());
        $clone->setPriority($this->getPriority());

        return $clone;
    }

    /**
     * Add ceModelEntities.
     *
     * @param \Wealthbot\AdminBundle\Entity\CeModelEntity $ceModelEntities
     *
     * @return Subclass
     */
    public function addCeModelEntity(\Wealthbot\AdminBundle\Entity\CeModelEntity $ceModelEntities)
    {
        $this->ceModelEntities[] = $ceModelEntities;

        return $this;
    }

    /**
     * Remove ceModelEntities.
     *
     * @param \Wealthbot\AdminBundle\Entity\CeModelEntity $ceModelEntities
     */
    public function removeCeModelEntity(\Wealthbot\AdminBundle\Entity\CeModelEntity $ceModelEntities)
    {
        $this->ceModelEntities->removeElement($ceModelEntities);
    }

    /**
     * Set tolerance_band.
     *
     * @param int $toleranceBand
     *
     * @return Subclass
     */
    public function setToleranceBand($toleranceBand)
    {
        $this->tolerance_band = $toleranceBand;

        return $this;
    }

    /**
     * Get tolerance_band.
     *
     * @return int
     */
    public function getToleranceBand()
    {
        return $this->tolerance_band;
    }
}
