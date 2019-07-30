<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\UserInterface;
use App\Model\CeModel as BaseCeModel;
use App\Model\CeModelEntityInterface;
use App\Model\CeModelInterface;

/**
 * Class CeModel
 * @package App\Entity
 */
class CeModel extends BaseCeModel
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $ownerId;

    /**
     * @param \App\Entity\User
     */
    protected $owner;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @param \App\Entity\CeModel
     */
    protected $parent;

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var bool
     */
    protected $isDeleted;

    /**
     * @var int
     */
    protected $riskRating;

    /**
     * @var float
     */
    protected $commissionMin;

    /**
     * @var float
     */
    protected $commissionMax;

    /**
     * @var int
     */
    protected $forecast;

    /**
     * @var float
     */
    protected $generousMarketReturn;

    /**
     * @var float
     */
    protected $lowMarketReturn;

    /**
     * @var bool
     */
    protected $isAssumptionLocked;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $children;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $modelEntities;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->isDeleted = false;
        $this->children = new ArrayCollection();
        $this->modelEntities = new ArrayCollection();
        $this->clientPortfolio = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getId();
    }

    /**
     * Set ownerId.
     *
     * @param int $ownerId
     *
     * @return CeModel
     */
    public function setOwnerId($ownerId)
    {
        parent::setOwnerId($ownerId);

        return $this;
    }

    /**
     * Get ownerId.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return parent::getOwnerId();
    }

    /**
     * Set parentId.
     *
     * @param int $parentId
     *
     * @return CeModel
     */
    public function setParentId($parentId)
    {
        parent::setParentId($parentId);

        return $this;
    }

    /**
     * Get parentId.
     *
     * @return int
     */
    public function getParentId()
    {
        return parent::getParentId();
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return CeModel
     */
    public function setType($type)
    {
        parent::setType($type);

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return parent::getType();
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return CeModel
     */
    public function setName($name)
    {
        parent::setName($name);

        return $this;
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
     * Set slug.
     *
     * @param string $slug
     *
     * @return CeModel
     */
    public function setSlug($slug)
    {
        parent::setSlug($slug);

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return parent::getSlug();
    }

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return CeModel
     */
    public function setIsDeleted($isDeleted)
    {
        parent::setIsDeleted($isDeleted);

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return parent::getIsDeleted();
    }

    /**
     * Set riskRating.
     *
     * @param int $riskRating
     *
     * @return CeModel
     */
    public function setRiskRating($riskRating)
    {
        parent::setRiskRating($riskRating);

        return $this;
    }

    /**
     * Get riskRating.
     *
     * @return int
     */
    public function getRiskRating()
    {
        return parent::getRiskRating();
    }

    /**
     * Set commissionMin.
     *
     * @param float $commissionMin
     *
     * @return CeModel
     */
    public function setCommissionMin($commissionMin)
    {
        parent::setCommissionMin($commissionMin);

        return $this;
    }

    /**
     * Get commissionMin.
     *
     * @return float
     */
    public function getCommissionMin()
    {
        return parent::getCommissionMin();
    }

    /**
     * Set commissionMax.
     *
     * @param float $commissionMax
     *
     * @return CeModel
     */
    public function setCommissionMax($commissionMax)
    {
        parent::setCommissionMax($commissionMax);

        return $this;
    }

    /**
     * Get commissionMax.
     *
     * @return float
     */
    public function getCommissionMax()
    {
        return parent::getCommissionMax();
    }

    /**
     * Set forecast.
     *
     * @param int $forecast
     *
     * @return CeModel
     */
    public function setForecast($forecast)
    {
        parent::setForecast($forecast);

        return $this;
    }

    /**
     * Get forecast.
     *
     * @return int
     */
    public function getForecast()
    {
        return parent::getForecast();
    }

    /**
     * Set generousMarketReturn.
     *
     * @param float $generousMarketReturn
     *
     * @return CeModel
     */
    public function setGenerousMarketReturn($generousMarketReturn)
    {
        parent::setGenerousMarketReturn($generousMarketReturn);

        return $this;
    }

    /**
     * Get generousMarketReturn.
     *
     * @return float
     */
    public function getGenerousMarketReturn()
    {
        return parent::getGenerousMarketReturn();
    }

    /**
     * Set lowMarketReturn.
     *
     * @param float $lowMarketReturn
     *
     * @return CeModel
     */
    public function setLowMarketReturn($lowMarketReturn)
    {
        parent::setLowMarketReturn($lowMarketReturn);

        return $this;
    }

    /**
     * Get lowMarketReturn.
     *
     * @return float
     */
    public function getLowMarketReturn()
    {
        return parent::getLowMarketReturn();
    }

    /**
     * Add children.
     *
     * @param CeModelInterface $children
     *
     * @return CeModel
     */
    public function addChildren(CeModelInterface $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param CeModelInterface $children
     */
    public function removeChildren(CeModelInterface $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Add modelEntities.
     *
     * @param CeModelEntityInterface $modelEntities
     *
     * @return CeModel
     */
    public function addModelEntitie(CeModelEntityInterface $modelEntities)
    {
        parent::addModelEntity($modelEntities);

        return $this;
    }

    /**
     * Remove modelEntities.
     *
     * @param CeModelEntityInterface $modelEntities
     */
    public function removeModelEntitie(CeModelEntityInterface $modelEntities)
    {
        parent::removeModelEntity($modelEntities);
    }

    /**
     * Get modelEntities.
     *
     * @return ArrayCollection|CeModelEntity[]
     */
    public function getModelEntities()
    {
        return parent::getModelEntities();
    }

    /**
     * Set owner.
     *
     * @param UserInterface $owner
     *
     * @return CeModel
     */
    public function setOwner(UserInterface $owner = null)
    {
        parent::setOwner($owner);

        return $this;
    }

    /**
     * Get owner.
     *
     * @return \App\Entity\User
     */
    public function getOwner()
    {
        return parent::getOwner();
    }

    /**
     * Set parent.
     *
     * @param CeModelInterface $parent
     *
     * @return CeModel
     */
    public function setParent(CeModelInterface $parent = null)
    {
        parent::setParent($parent);

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \App\Entity\CeModel
     */
    public function getParent()
    {
        return parent::getParent();
    }

    /**
     * @var bool
     */

    /**
     * Set isAssumptionLocked.
     *
     * @param bool $isAssumptionLocked
     *
     * @return CeModel
     */
    public function setIsAssumptionLocked($isAssumptionLocked)
    {
        parent::setIsAssumptionLocked($isAssumptionLocked);

        return $this;
    }

    /**
     * Get isAssumptionLocked.
     *
     * @return bool
     */
    public function getIsAssumptionLocked()
    {
        return parent::getIsAssumptionLocked();
    }

    /**
     * Add children.
     *
     * @param \App\Entity\CeModel $children
     *
     * @return CeModel
     */
    public function addChild(CeModel $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param \App\Entity\CeModel $children
     */
    public function removeChild(CeModel $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Add modelEntities.
     *
     * @param CeModelEntityInterface $modelEntities
     *
     * @return $this
     */
    public function addModelEntity(CeModelEntityInterface $modelEntities)
    {
        $this->modelEntities[] = $modelEntities;

        return $this;
    }

    /**
     * Remove model entity.
     *
     * @param CeModelEntityInterface $modelEntities
     */
    public function removeModelEntity(CeModelEntityInterface $modelEntities)
    {
        $this->modelEntities->removeElement($modelEntities);
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $clientPortfolio;

    /**
     * Add clientPortfolio.
     *
     * @param \App\Entity\ClientPortfolio $clientPortfolio
     *
     * @return CeModel
     */
    public function addClientPortfolio(ClientPortfolio $clientPortfolio)
    {
        $this->clientPortfolio[] = $clientPortfolio;

        return $this;
    }

    /**
     * Remove clientPortfolio.
     *
     * @param \App\Entity\ClientPortfolio $clientPortfolio
     */
    public function removeClientPortfolio(ClientPortfolio $clientPortfolio)
    {
        $this->clientPortfolio->removeElement($clientPortfolio);
    }

    /**
     * Get clientPortfolio.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClientPortfolio()
    {
        return $this->clientPortfolio;
    }


    public function __toString()
    {
        return (string) $this->name;
    }
}
