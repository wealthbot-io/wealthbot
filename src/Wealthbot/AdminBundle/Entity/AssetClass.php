<?php

namespace Wealthbot\AdminBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Wealthbot\AdminBundle\Model\CeModelInterface;

/**
 * Wealthbot\AdminBundle\Entity\AssetClass.
 */
class AssetClass
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    // ENUM values type column
    const TYPE_STOCKS = 'Stocks';
    const TYPE_BONDS = 'Bonds';

    private static $_typeValues = null;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $subclasses;

    /**
     * @var int
     */
    private $model_id;

    /**
     * @var \Wealthbot\AdminBundle\Entity\CeModel
     */
    private $model;

    /**
     * @var int
     */
    private $tolerance_band;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->subclasses = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set type.
     *
     * @param string $type
     *
     * @return AssetClass
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getTypeChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for asset_classes.type : %s.', $type)
            );
        }

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
     * Get array ENUM values type column.
     *
     * @static
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        // Build $_typeValues if this is the first call
        if (self::$_typeValues === null) {
            self::$_typeValues = [];
            $oClass = new \ReflectionClass('\Wealthbot\AdminBundle\Entity\AssetClass');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'TYPE_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_typeValues[$val] = $val;
                }
            }
        }

        return self::$_typeValues;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return AssetClass
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

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get subclasses.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubclasses()
    {
        return $this->subclasses;
    }

    /**
     * Add subclasses.
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $subclasses
     *
     * @return AssetClass
     */
    public function addSubclasse(\Wealthbot\AdminBundle\Entity\Subclass $subclasses)
    {
        $this->subclasses[] = $subclasses;

        return $this;
    }

    /**
     * Remove subclasses.
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $subclasses
     */
    public function removeSubclasse(\Wealthbot\AdminBundle\Entity\Subclass $subclasses)
    {
        $this->subclasses->removeElement($subclasses);
    }

    public function setSubclasses($subclasses)
    {
        foreach ($subclasses as $subclass) {
            $subclass->setAssetClass($this);
        }

        $this->subclasses = $subclasses;
    }

    /**
     * Set model_id.
     *
     * @param int $modelId
     *
     * @return AssetClass
     */
    public function setModelId($modelId)
    {
        $this->model_id = $modelId;

        return $this;
    }

    /**
     * Get model_id.
     *
     * @return int
     */
    public function getModelId()
    {
        return $this->model_id;
    }

    /**
     * Set model.
     *
     * @param CeModelInterface $model
     *
     * @return AssetClass
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

    /**
     * Returns copied instance of AssetClass.
     *
     * @return self
     */
    public function getCopy()
    {
        $clone = clone $this;

        $clone->id = null;
        $clone->subclasses = new ArrayCollection();

        return $clone;
    }

    /**
     * Add subclasses.
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $subclasses
     *
     * @return AssetClass
     */
    public function addSubclass(\Wealthbot\AdminBundle\Entity\Subclass $subclasses)
    {
        $this->subclasses[] = $subclasses;

        return $this;
    }

    /**
     * Remove subclasses.
     *
     * @param \Wealthbot\AdminBundle\Entity\Subclass $subclasses
     */
    public function removeSubclass(\Wealthbot\AdminBundle\Entity\Subclass $subclasses)
    {
        $this->subclasses->removeElement($subclasses);
    }

    /**
     * Set tolerance_band.
     *
     * @param int $toleranceBand
     *
     * @return AssetClass
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
