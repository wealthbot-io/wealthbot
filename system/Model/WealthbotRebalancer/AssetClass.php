<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class AssetClass extends Base {

    /** @var  float */
    private $targetAllocation;

    /** @var  float */
    private $currentAllocation;

    /** @var  float */
    private $toleranceBand;

    /** @var  SubclassCollection */
    private $subclasses;

    public function __construct()
    {
        $this->targetAllocation = 0;
        $this->currentAllocation = 0;
        $this->subclasses = new SubclassCollection();
    }

    /**
     * @param float $targetAllocation
     * @return $this
     */
    public function setTargetAllocation($targetAllocation)
    {
        $this->targetAllocation = $targetAllocation;

        return $this;
    }

    /**
     * @return float
     */
    public function getTargetAllocation()
    {
        return $this->targetAllocation;
    }

    /**
     * @param float $currentAllocation
     * @return $this
     */
    public function setCurrentAllocation($currentAllocation)
    {
        $this->currentAllocation = $currentAllocation;

        return $this;
    }

    /**
     * @return float
     */
    public function getCurrentAllocation()
    {
        return $this->currentAllocation;
    }

    /**
     * @param float $toleranceBand
     * @return $this
     */
    public function setToleranceBand($toleranceBand)
    {
        $this->toleranceBand = $toleranceBand;

        return $this;
    }

    /**
     * @return float
     */
    public function getToleranceBand()
    {
        return $this->toleranceBand;
    }

    /**
     * @return float
     */
    public function calcOOB()
    {
        return $this->currentAllocation - $this->targetAllocation;
    }

    /**
     * @return SubclassCollection
     */
    public function getSubclasses()
    {
        return $this->subclasses;
    }

    /**
     * @param SubclassCollection $subclasses
     * @return $this
     */
    public function setSubclasses(SubclassCollection $subclasses)
    {
        $this->subclasses = $subclasses;

        return $this;
    }

    /**
     * @param Subclass $subclass
     * @return $this
     */
    public function addSubclass(Subclass $subclass)
    {
        $this->subclasses->add($subclass);
        $this->setTargetAllocation($this->getTargetAllocation() + $subclass->getTargetAllocation());

        return $this;
    }

    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($key === 'subclasses') {
                $subclasses = new SubclassCollection();
                foreach ($value as $subclassData) {
                    $class = 'Model\WealthbotRebalancer\Subclass';

                    $subclass = new $class;
                    $subclass->loadFromArray($subclassData);

                    $subclasses->add($subclass);
                }

                $this->setSubclasses($subclasses);
            } else {
                $this->$key = $value;
            }
        }
    }
}