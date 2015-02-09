<?php

namespace Wealthbot\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wealthbot\AdminBundle\Model\CeModelEntity as BaseCeModelEntity;
use Wealthbot\AdminBundle\Model\CeModelInterface;

/**
 * CeModelEntity
 */
class CeModelEntity extends BaseCeModelEntity
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $modelId;

    /**
     * @var integer
     */
    protected $assetClassId;

    /**
     * @var integer
     */
    protected $subclassId;

    /**
     * @var integer
     */
    protected $securityAssignmentId;

    /**
     * @var integer
     */
    protected $muniSubstitutionId;

    /**
     * @var integer
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
     * @var integer
     */
    protected $nbEdits;

    /**
     * @var boolean
     */
    protected $isQualified;

    /**
     * @var \Wealthbot\AdminBundle\Entity\CeModel
     */
    protected $model;

    /**
     * @var \Wealthbot\AdminBundle\Entity\AssetClass
     */
    protected $assetClass;

    /**
     * @var \Wealthbot\AdminBundle\Entity\Subclass
     */
    protected $subclass;

    /**
     * @var \Wealthbot\AdminBundle\Entity\SecurityAssignment
     */
    protected $securityAssignment;

    /**
     * @var \Wealthbot\AdminBundle\Entity\SecurityAssignment
     */
    protected $muniSubstitution;

    /**
     * @var \Wealthbot\AdminBundle\Entity\SecurityAssignment
     */
    protected $taxLossHarvesting;


    public function __construct()
    {
        parent::__construct();

        $this->nbEdits = 0;
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return parent::getId();
    }

    /**
     * Set modelId
     *
     * @param integer $modelId
     * @return CeModelEntity
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
    
        return $this;
    }

    /**
     * Get modelId
     *
     * @return integer 
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * Set assetClassId
     *
     * @param integer $assetClassId
     * @return CeModelEntity
     */
    public function setAssetClassId($assetClassId)
    {
        parent::setAssetClassId($assetClassId);
    
        return $this;
    }

    /**
     * Get assetClassId
     *
     * @return integer 
     */
    public function getAssetClassId()
    {
        return parent::getAssetClassId();
    }

    /**
     * Set subclassId
     *
     * @param integer $subclassId
     * @return CeModelEntity
     */
    public function setSubclassId($subclassId)
    {
        parent::setSubclassId($subclassId);
    
        return $this;
    }

    /**
     * Get subclassId
     *
     * @return integer 
     */
    public function getSubclassId()
    {
        return parent::getSubclassId();
    }

    /**
     * Set securityId
     *
     * @param integer $securityId
     * @return CeModelEntity
     */
    public function setSecurityAssignmentId($securityId)
    {
        $this->securityAssignmentId = $securityId;
    
        return $this;
    }

    /**
     * Get securityId
     *
     * @return integer 
     */
    public function getSecurityAssignmentId()
    {
        return $this->securityAssignmentId;
    }

    /**
     * Set muniSubstitutionId
     *
     * @param integer $muniSubstitutionId
     * @return CeModelEntity
     */
    public function setMuniSubstitutionId($muniSubstitutionId)
    {
        parent::setMuniSubstitutionId($muniSubstitutionId);
    
        return $this;
    }

    /**
     * Get muniSubstitutionId
     *
     * @return integer 
     */
    public function getMuniSubstitutionId()
    {
        return parent::getMuniSubstitutionId();
    }

    /**
     * Set taxLossHarvestingId
     *
     * @param integer $taxLossHarvestingId
     * @return CeModelEntity
     */
    public function setTaxLossHarvestingId($taxLossHarvestingId)
    {
        parent::setTaxLossHarvestingId($taxLossHarvestingId);
    
        return $this;
    }

    /**
     * Get taxLossHarvestingId
     *
     * @return integer 
     */
    public function getTaxLossHarvestingId()
    {
        return parent::getTaxLossHarvestingId();
    }

    /**
     * Set percent
     *
     * @param float $percent
     * @return CeModelEntity
     */
    public function setPercent($percent)
    {
        parent::setPercent($percent);
    
        return $this;
    }

    /**
     * Get percent
     *
     * @return float 
     */
    public function getPercent()
    {
        return parent::getPercent();
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return CeModelEntity
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set nbEdits
     *
     * @param integer $nbEdits
     * @return CeModelEntity
     */
    public function setNbEdits($nbEdits)
    {
        $this->nbEdits = $nbEdits;
    
        return $this;
    }

    /**
     * Get nbEdits
     *
     * @return integer 
     */
    public function getNbEdits()
    {
        return $this->nbEdits;
    }

    /**
     * Set isQualified
     *
     * @param boolean $isQualified
     * @return CeModelEntity
     */
    public function setIsQualified($isQualified)
    {
        parent::setIsQualified($isQualified);
    
        return $this;
    }

    /**
     * Get isQualified
     *
     * @return boolean 
     */
    public function getIsQualified()
    {
        return parent::getIsQualified();
    }

    /**
     * Set model
     *
     * @param CeModelInterface $model
     * @return CeModelEntity
     */
    public function setModel(CeModelInterface $model = null)
    {
        parent::setModel($model);
    
        return $this;
    }

    /**
     * Get model
     *
     * @return \Wealthbot\AdminBundle\Entity\CeModel 
     */
    public function getModel()
    {
        return parent::getModel();
    }

    /**
     * Set assetClass
     *
     * @param \Wealthbot\AdminBundle\Entity\AssetClass $assetClass
     * @return CeModelEntity
     */
    public function setAssetClass(\Wealthbot\AdminBundle\Entity\AssetClass $assetClass = null)
    {
        parent::setAssetClass($assetClass);
    
        return $this;
    }

    /**
     * Get assetClass
     *
     * @return \Wealthbot\AdminBundle\Entity\AssetClass 
     */
    public function getAssetClass()
    {
        return parent::getAssetClass();
    }

    /**
     * Set subclass
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $subclass
     * @return CeModelEntity
     */
    public function setSubclass(\Wealthbot\AdminBundle\Entity\Subclass $subclass = null)
    {
        parent::setSubclass($subclass);
    
        return $this;
    }

    /**
     * Get subclass
     *
     * @return \Wealthbot\AdminBundle\Entity\Subclass 
     */
    public function getSubclass()
    {
        return parent::getSubclass();
    }

    /**
     * Set securityAssignment
     *
     * @param \Wealthbot\AdminBundle\Entity\SecurityAssignment $securityAssignment
     * @return CeModelEntity
     */
    public function setSecurityAssignment(\Wealthbot\AdminBundle\Entity\SecurityAssignment $securityAssignment = null)
    {
        parent::setSecurityAssignment($securityAssignment);
    
        return $this;
    }

    /**
     * Get securityAssignment
     *
     * @return \Wealthbot\AdminBundle\Entity\SecurityAssignment
     */
    public function getSecurityAssignment()
    {
        return parent::getSecurityAssignment();
    }

    /**
     * Set muniSubstitution
     *
     * @param \Wealthbot\AdminBundle\Entity\SecurityAssignment $muniSubstitution
     * @return CeModelEntity
     */
    public function setMuniSubstitution(\Wealthbot\AdminBundle\Entity\SecurityAssignment $muniSubstitution = null)
    {
        parent::setMuniSubstitution($muniSubstitution);
    
        return $this;
    }

    /**
     * Get muniSubstitution
     *
     * @return \Wealthbot\AdminBundle\Entity\SecurityAssignment
     */
    public function getMuniSubstitution()
    {
        return parent::getMuniSubstitution();
    }

    /**
     * Set taxLossHarvesting
     *
     * @param \Wealthbot\AdminBundle\Entity\SecurityAssignment $taxLossHarvesting
     * @return CeModelEntity
     */
    public function setTaxLossHarvesting(\Wealthbot\AdminBundle\Entity\SecurityAssignment $taxLossHarvesting = null)
    {
        parent::setTaxLossHarvesting($taxLossHarvesting);
    
        return $this;
    }

    /**
     * Get taxLossHarvesting
     *
     * @return \Wealthbot\AdminBundle\Entity\SecurityAssignment
     */
    public function getTaxLossHarvesting()
    {
        return parent::getTaxLossHarvesting();
    }
}
