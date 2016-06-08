<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.06.13
 * Time: 17:00
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Model;

use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Entity\Subclass;

class CeModelEntity implements CeModelEntityInterface
{
    /** @var  int */
    protected $id;

    /** @var  bool */
    protected $isQualified;

    /** @var  float */
    protected $percent;

    /** @var int */
    protected $muniSubstitutionId;

    /** @var int */
    protected $taxLossHarvestingId;

    /** @var \Wealthbot\AdminBundle\Entity\SecurityAssignment */
    protected $taxLossHarvesting;

    /**
     * @var \Wealthbot\AdminBundle\Entity\CeModel
     */
    protected $model;

    /** @var  SecurityAssignment */
    protected $securityAssignment;

    /** @var  int */
    protected $subclassId;

    /** @var \Wealthbot\AdminBundle\Entity\Subclass */
    protected $subclass;

    /** @var int */
    protected $assetClassId;

    /** @var \Wealthbot\AdminBundle\Entity\AssetClass */
    protected $assetClass;

    /** @var \Wealthbot\AdminBundle\Entity\SecurityAssignment */
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
        return $this->getAssetClass()->getType() === 'Bonds';
    }

    public function isStocks()
    {
        return $this->getAssetClass()->getType() === 'Stocks';
    }

    public function isMunicipal()
    {
        return $this->getAssetClass()->getType() === 'Bonds' && $this->getAssetClass()->getName() === 'Domestic Bonds';
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

    public function setAssetClass(\Wealthbot\AdminBundle\Entity\AssetClass $assetClass = null)
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
     * @return \Wealthbot\AdminBundle\Entity\CeModel
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

    public function setMuniSubstitution(\Wealthbot\AdminBundle\Entity\SecurityAssignment $muniSubstitution = null)
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

    public function setTaxLossHarvesting(\Wealthbot\AdminBundle\Entity\SecurityAssignment $taxLossHarvesting = null)
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
