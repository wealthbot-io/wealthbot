<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 24.06.13
 * Time: 15:20
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\UserInterface;

interface CeModelInterface
{
    /**
     * Strategy type of model.
     */
    const TYPE_STRATEGY = 1;

    /**
     * Custom type of model.
     */
    const TYPE_CUSTOM = 2;

    /**
     * Set id.
     *
     * @return int
     */
    public function getId();

    /**
     * Set id of model owner.
     *
     * @param int $ownerId
     *
     * @return self
     */
    public function setOwnerId($ownerId);

    /**
     * Get id of model owner.
     *
     * @return int
     */
    public function getOwnerId();

    /**
     * Set owner.
     *
     * @param UserInterface $owner
     *
     * @return self
     */
    public function setOwner(UserInterface $owner);

    /**
     * Get owner.
     *
     * @return UserInterface
     */
    public function getOwner();

    /**
     * Set parent id.
     *
     * @param int $parentId
     *
     * @return self
     */
    public function setParentId($parentId);

    /**
     * Get parent id.
     *
     * @return int
     */
    public function getParentId();

    /**
     * Set parent.
     *
     * @param CeModelInterface $parent
     *
     * @return self
     */
    public function setParent(CeModelInterface $parent);

    /**
     * Get parent.
     *
     * @return CeModelInterface
     */
    public function getParent();

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return self
     */
    public function setSlug($slug);

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug();

    /**
     * Set model risk rating.
     *
     * @param int $riskRating
     *
     * @return self
     */
    public function setRiskRating($riskRating);

    /**
     * Get model risk rating.
     *
     * @return int
     */
    public function getRiskRating();

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return self
     */
    public function setType($type);

    /**
     * Get type.
     *
     * @return int
     */
    public function getType();

    /**
     * Returns true if model has $type type and false otherwise.
     *
     * @param int $type
     *
     * @return bool
     */
    public function hasType($type);

    /**
     * Returns true if model has strategy type and false otherwise.
     *
     * @return bool
     */
    public function isStrategy();

    /**
     * Returns true if model has custom type and false otherwise.
     *
     * @return bool
     */
    public function isCustom();

    /**
     * Set commission min.
     *
     * @param float $commissionMin
     *
     * @return self
     */
    public function setCommissionMin($commissionMin);

    /**
     * Get commission min.
     *
     * @return float
     */
    public function getCommissionMin();

    /**
     * Set commission max.
     *
     * @param float $commissionMax
     *
     * @return self
     */
    public function setCommissionMax($commissionMax);

    /**
     * Get commission max.
     *
     * @return float
     */
    public function getCommissionMax();

    /**
     * Get commissions
     * If commission_max and commission_min specified and more than 0,
     *    then returns array(commissionMin, commissionMax)
     * If specified only commission_min or only commission_max,
     *    then returns array(commissionMin|commissionMax) with one value
     * Otherwise return empty array.
     *
     * @return array
     */
    public function getCommissions();

    /**
     * Set forecast.
     *
     * @param int $forecast
     *
     * @return self
     */
    public function setForecast($forecast);

    /**
     * Get forecast.
     *
     * @return int
     */
    public function getForecast();

    /**
     * Set generous market return.
     *
     * @param float $generousMarketReturn
     *
     * @return self
     */
    public function setGenerousMarketReturn($generousMarketReturn);

    /**
     * Get generous market return.
     *
     * @return float
     */
    public function getGenerousMarketReturn();

    /**
     * Set low market return.
     *
     * @param float $lowMarketReturn
     *
     * @return self
     */
    public function setLowMarketReturn($lowMarketReturn);

    /**
     * Get low market return.
     *
     * @return float
     */
    public function getLowMarketReturn();

    /**
     * Set is assumption locked.
     *
     * @param bool $isAssumptionLocked
     *
     * @return self
     */
    public function setIsAssumptionLocked($isAssumptionLocked);

    /**
     * Get is assumption locked.
     *
     * @return bool
     */
    public function getIsAssumptionLocked();

    /**
     * Add model entity.
     *
     * @param CeModelEntityInterface $ceModelEntity
     *
     * @return self
     */
    public function addModelEntity(CeModelEntityInterface $ceModelEntity);

    /**
     * Remove model entity.
     *
     * @param CeModelEntityInterface $ceModelEntity
     */
    public function removeModelEntity(CeModelEntityInterface $ceModelEntity);

    /**
     * Get model entities.
     *
     * @return Collection
     */
    public function getModelEntities();

    /**
     * Get qualified model entities.
     *
     * @return array
     */
    public function getQualifiedModelEntities();

    /**
     * Get non qualified model entities.
     *
     * @return array
     */
    public function getNonQualifiedModelEntities();

    /**
     * Add children.
     *
     * @param CeModelInterface $ceModel
     *
     * @return self
     */
    public function addChildren(CeModelInterface $ceModel);

    /**
     * Set assumption values.
     *
     * @param array $assumption
     */
    public function setAssumption(array $assumption);

    /**
     * Get array of assumption values.
     *
     * @return array
     */
    public function getAssumption();

    /**
     * Remove children.
     *
     * @param CeModelInterface $ceModel
     */
    public function removeChildren(CeModelInterface $ceModel);

    /**
     * Get children.
     *
     * @return Collection
     */
    public function getChildren();

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return self
     */
    public function setIsDeleted($isDeleted);

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted();

    /**
     * Copy model
     * returns new model with exist model data.
     *
     * @return self
     */
    public function getCopy();

    /**
     * Build group of model entities.
     */
    public function buildGroupModelEntities();
}
