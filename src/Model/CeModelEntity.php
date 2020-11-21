<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.06.13
 * Time: 17:00
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Entity\SecurityAssignment;
use App\Entity\Subclass;

class CeModelEntity implements CeModelEntityInterface
{
    /** @var int */
    protected $id;

    /** @var bool */
    protected $isQualified;

    /** @var float */
    protected $percent;

    /** @var int */
    protected $muniSubstitutionId;

    /** @var int */
    protected $taxLossHarvestingId;

    /** @param \App\Entity\SecurityAssignment */
    protected $taxLossHarvesting;

    /**
     * @param \App\Entity\CeModel
     */
    protected $model;

    /** @var SecurityAssignment */
    protected $securityAssignment;

    /** @var int */
    protected $subclassId;

    /** @param \App\Entity\Subclass */
    protected $subclass;

    /** @var int */
    protected $assetClassId;

    /** @param \App\Entity\AssetClass */
    protected $assetClass;

    /** @param \App\Entity\SecurityAssignment */
    protected $muniSubstitution;

    public function __construct()
    {
        $this->isQualified = false;
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

    public function isBonds()
    {
        return 'Bonds' === $this->getAssetClass()->getType();
    }

    public function isStocks()
    {
        return 'Stocks' === $this->getAssetClass()->getType();
    }

    public function isMunicipal()
    {
        return 'Bonds' === $this->getAssetClass()->getType() && 'Domestic Bonds' === $this->getAssetClass()->getName();
    }

    public function setIsQualified($isQualified)
    {
        $this->isQualified = $isQualified;

        return $this;
    }

    public function setAssetClassId($assetClassId)
    {
        $this->assetClassId = $assetClassId;

        return $this;
    }

    public function getAssetClassId()
    {
        return $this->assetClassId;
    }

    public function setAssetClass(\App\Entity\AssetClass $assetClass = null)
    {
        $this->assetClass = $assetClass;

        return $this;
    }

    public function getAssetClass()
    {
        return $this->assetClass;
    }

    public function getIsQualified()
    {
        return $this->isQualified;
    }

    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * Set model.
     *
     * @param CeModelInterface $model
     *
     * @return CeModelEntity
     */
    public function setModel(CeModelInterface $model = null)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model.
     *
     * @return \App\Entity\CeModel
     */
    public function getModel()
    {
        return $this->model;
    }

    public function setSecurityAssignment(SecurityAssignment $securityAssignment = null)
    {
        $this->securityAssignment = $securityAssignment;

        return $this;
    }

    public function getSecurityAssignment()
    {
        return $this->securityAssignment;
    }

    public function setSubclassId($subclassId)
    {
        $this->subclassId = $subclassId;

        return $this;
    }

    public function getSubclassId()
    {
        return $this->subclassId;
    }

    public function setSubclass(Subclass $subclass = null)
    {
        $this->subclass = $subclass;

        return $this;
    }

    public function getSubclass()
    {
        return $this->subclass;
    }

    public function setMuniSubstitutionId($muniSubstitutionId)
    {
        $this->muniSubstitutionId = $muniSubstitutionId;

        return $this;
    }

    public function getMuniSubstitutionId()
    {
        return $this->muniSubstitutionId;
    }

    public function setMuniSubstitution(\App\Entity\SecurityAssignment $muniSubstitution = null)
    {
        $this->muniSubstitution = $muniSubstitution;

        return $this;
    }

    public function getMuniSubstitution()
    {
        return $this->muniSubstitution;
    }

    public function setTaxLossHarvestingId($taxLossHarvestingId)
    {
        $this->taxLossHarvestingId = $taxLossHarvestingId;

        return $this;
    }

    public function getTaxLossHarvestingId()
    {
        return $this->taxLossHarvestingId;
    }

    public function setTaxLossHarvesting(\App\Entity\SecurityAssignment $taxLossHarvesting = null)
    {
        $this->taxLossHarvesting = $taxLossHarvesting;

        return $this;
    }

    public function getTaxLossHarvesting()
    {
        return $this->taxLossHarvesting;
    }

    public function toArray()
    {
        return [
            'label' => $this->getSecurityAssignment()->getSubclass()->getName(),
            'data' => $this->getPercent(),
        ];
    }

    public function getCopy()
    {
        //$class = get_class($this);

        /** @var self $clone */
        //$clone = new $class();

        $clone = clone $this;

        $clone->id = null;
        $clone->isQualified = false;

        $clone->setAssetClass($this->getAssetClass());
        $clone->setSecurityAssignment($this->getSecurityAssignment());
        $clone->setMuniSubstitution($this->getMuniSubstitution());
        $clone->setTaxLossHarvesting($this->getTaxLossHarvesting());
        $clone->setPercent($this->getPercent());
        $clone->setIsQualified($this->getIsQualified());
        $clone->setSubclass($this->getSubclass());

        return $clone;
    }
}
