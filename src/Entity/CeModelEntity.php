<?php

namespace App\Entity;

use App\Model\CeModelEntity as BaseCeModelEntity;
use App\Model\CeModelInterface;

/**
 * Class CeModelEntity
 * @package App\Entity
 */
class CeModelEntity extends BaseCeModelEntity
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $modelId;

    /**
     * @var int
     */
    protected $assetClassId;

    /**
     * @var int
     */
    protected $subclassId;

    /**
     * @var int
     */
    protected $securityAssignmentId;

    /**
     * @var int
     */
    protected $muniSubstitutionId;

    /**
     * @var int
     */
    protected $taxLossHarvestingId;

    /**
     * @var float
     */
    protected $percent;

    /**
     * @var \DateTime
     */
    protected $updated;

    /**
     * @var int
     */
    protected $nbEdits;

    /**
     * @var bool
     */
    protected $isQualified;

    /**
     * @param \App\Entity\CeModel
     */
    protected $model;

    /**
     * @param \App\Entity\AssetClass
     */
    protected $assetClass;

    /**
     * @param \App\Entity\Subclass
     */
    protected $subclass;

    /**
     * @param \App\Entity\SecurityAssignment
     */
    protected $securityAssignment;

    /**
     * @param \App\Entity\SecurityAssignment
     */
    protected $muniSubstitution;

    /**
     * @param \App\Entity\SecurityAssignment
     */
    protected $taxLossHarvesting;

    public function __construct()
    {
        parent::__construct();

        $this->nbEdits = 0;
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
     * Set modelId.
     *
     * @param int $modelId
     *
     * @return CeModelEntity
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;

        return $this;
    }

    /**
     * Get modelId.
     *
     * @return int
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * Set assetClassId.
     *
     * @param int $assetClassId
     *
     * @return CeModelEntity
     */
    public function setAssetClassId($assetClassId)
    {
        parent::setAssetClassId($assetClassId);

        return $this;
    }

    /**
     * Get assetClassId.
     *
     * @return int
     */
    public function getAssetClassId()
    {
        return parent::getAssetClassId();
    }

    /**
     * Set subclassId.
     *
     * @param int $subclassId
     *
     * @return CeModelEntity
     */
    public function setSubclassId($subclassId)
    {
        parent::setSubclassId($subclassId);

        return $this;
    }

    /**
     * Get subclassId.
     *
     * @return int
     */
    public function getSubclassId()
    {
        return parent::getSubclassId();
    }

    /**
     * Set securityId.
     *
     * @param int $securityId
     *
     * @return CeModelEntity
     */
    public function setSecurityAssignmentId($securityId)
    {
        $this->securityAssignmentId = $securityId;

        return $this;
    }

    /**
     * Get securityId.
     *
     * @return int
     */
    public function getSecurityAssignmentId()
    {
        return $this->securityAssignmentId;
    }

    /**
     * Set muniSubstitutionId.
     *
     * @param int $muniSubstitutionId
     *
     * @return CeModelEntity
     */
    public function setMuniSubstitutionId($muniSubstitutionId)
    {
        parent::setMuniSubstitutionId($muniSubstitutionId);

        return $this;
    }

    /**
     * Get muniSubstitutionId.
     *
     * @return int
     */
    public function getMuniSubstitutionId()
    {
        return parent::getMuniSubstitutionId();
    }

    /**
     * Set taxLossHarvestingId.
     *
     * @param int $taxLossHarvestingId
     *
     * @return CeModelEntity
     */
    public function setTaxLossHarvestingId($taxLossHarvestingId)
    {
        parent::setTaxLossHarvestingId($taxLossHarvestingId);

        return $this;
    }

    /**
     * Get taxLossHarvestingId.
     *
     * @return int
     */
    public function getTaxLossHarvestingId()
    {
        return parent::getTaxLossHarvestingId();
    }

    /**
     * Set percent.
     *
     * @param float $percent
     *
     * @return CeModelEntity
     */
    public function setPercent($percent)
    {
        parent::setPercent($percent);

        return $this;
    }

    /**
     * Get percent.
     *
     * @return float
     */
    public function getPercent()
    {
        return parent::getPercent();
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return CeModelEntity
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set nbEdits.
     *
     * @param int $nbEdits
     *
     * @return CeModelEntity
     */
    public function setNbEdits($nbEdits)
    {
        $this->nbEdits = $nbEdits;

        return $this;
    }

    /**
     * Get nbEdits.
     *
     * @return int
     */
    public function getNbEdits()
    {
        return $this->nbEdits;
    }

    /**
     * Set isQualified.
     *
     * @param bool $isQualified
     *
     * @return CeModelEntity
     */
    public function setIsQualified($isQualified)
    {
        parent::setIsQualified($isQualified);

        return $this;
    }

    /**
     * Get isQualified.
     *
     * @return bool
     */
    public function getIsQualified()
    {
        return parent::getIsQualified();
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
        parent::setModel($model);

        return $this;
    }

    /**
     * Get model.
     *
     * @return \App\Entity\CeModel
     */
    public function getModel()
    {
        return parent::getModel();
    }

    /**
     * Set assetClass.
     *
     * @param \App\Entity\AssetClass $assetClass
     *
     * @return CeModelEntity
     */
    public function setAssetClass(AssetClass $assetClass = null)
    {
        parent::setAssetClass($assetClass);

        return $this;
    }

    /**
     * Get assetClass.
     *
     * @return \App\Entity\AssetClass
     */
    public function getAssetClass()
    {
        return parent::getAssetClass();
    }

    /**
     * Set subclass.
     *
     * @param \App\Entity\Subclass $subclass
     *
     * @return CeModelEntity
     */
    public function setSubclass(Subclass $subclass = null)
    {
        parent::setSubclass($subclass);

        return $this;
    }

    /**
     * Get subclass.
     *
     * @return \App\Entity\Subclass
     */
    public function getSubclass()
    {
        return parent::getSubclass();
    }

    /**
     * Set securityAssignment.
     *
     * @param \App\Entity\SecurityAssignment $securityAssignment
     *
     * @return CeModelEntity
     */
    public function setSecurityAssignment(SecurityAssignment $securityAssignment = null)
    {
        parent::setSecurityAssignment($securityAssignment);

        return $this;
    }

    /**
     * Get securityAssignment.
     *
     * @return \App\Entity\SecurityAssignment
     */
    public function getSecurityAssignment()
    {
        return parent::getSecurityAssignment();
    }

    /**
     * Set muniSubstitution.
     *
     * @param \App\Entity\SecurityAssignment $muniSubstitution
     *
     * @return CeModelEntity
     */
    public function setMuniSubstitution(SecurityAssignment $muniSubstitution = null)
    {
        parent::setMuniSubstitution($muniSubstitution);

        return $this;
    }

    /**
     * Get muniSubstitution.
     *
     * @return \App\Entity\SecurityAssignment
     */
    public function getMuniSubstitution()
    {
        return parent::getMuniSubstitution();
    }

    /**
     * Set taxLossHarvesting.
     *
     * @param \App\Entity\SecurityAssignment $taxLossHarvesting
     *
     * @return CeModelEntity
     */
    public function setTaxLossHarvesting(SecurityAssignment $taxLossHarvesting = null)
    {
        parent::setTaxLossHarvesting($taxLossHarvesting);

        return $this;
    }

    /**
     * Get taxLossHarvesting.
     *
     * @return \App\Entity\SecurityAssignment
     */
    public function getTaxLossHarvesting()
    {
        return parent::getTaxLossHarvesting();
    }

    public function __toString()
    {
        return (string) $this->id;
    }
}
