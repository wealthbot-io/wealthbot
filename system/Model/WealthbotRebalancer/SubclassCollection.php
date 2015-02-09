<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class SubclassCollection extends ArrayCollection
{

    public function isOutOfBalance()
    {
        /** @var Subclass $element */
        foreach ($this->_elements as $element) {
            if (abs($element->calcOOB()) > $element->getToleranceBand()) {
                return true;
            }
        }

        return false;
    }

    public function rebuildAllocations()
    {
        $totalAmount = 0;
        /** @var Subclass $subclass */
        foreach ($this->_elements as $subclass) {
            $totalAmount += $subclass->getTotalAmount();
        }

        foreach ($this->_elements as $subclass) {
            /** @var Subclass $subclass */

            $amount = $subclass->getTotalAmount();
            if (0 === $amount) {
                $currentAllocation = 0;
            } else {
                $currentAllocation = ($amount * 100) / $totalAmount;
            }

            $subclass->setCurrentAllocation($currentAllocation);
        }
    }

    public function findMinAndMaxOob()
    {
        $max = array(
            'percent' => -101,
            'subclass' => null
        );

        $min = array(
            'percent' => 101,
            'subclass' => null
        );

        /** @var Subclass $subclass */
        foreach ($this->_elements as $subclass) {
            $oob = $subclass->calcOOB();

            if ($oob > $max['percent']) {
                $max['percent'] = $oob;
                $max['subclass'] = $subclass;
            }

            if ($oob < $min['percent']) {
                $min['percent'] = $oob;
                $min['subclass'] = $subclass;
            }
        }

        return array('min' => $min, 'max' => $max);
    }

    /**
     * Find subclass with max OOB
     *
     * @return array
     */
    public function getMaxOobSubclass()
    {
        return $this->findMinAndMaxOob()['max'];
    }


    public function sortByPriority()
    {
        $iterator = $this->getIterator();
        $iterator->uasort(function ($a, $b) {
            return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
        });

        $collection = new SubclassCollection(iterator_to_array($iterator));

        $this->_elements = $collection->toArray();
    }

    /**
     * Return collection of subclasses sorted by OOB
     *
     * @return SubclassCollection
     */
    public function sortByOob()
    {
        $iterator = $this->getIterator();
        $iterator->uasort(function($a, $b) {
            $aOob = $a->calcOOB();
            $bOob = $b->calcOOB();

            return ($aOob > $bOob) ? -1 : 1;
        });

        return new SubclassCollection(iterator_to_array($iterator));
    }

    /**
     * Returns collection of diff elements
     *
     * @param SubclassCollection $subclasses
     * @return SubclassCollection
     */
    public function diff(SubclassCollection $subclasses)
    {
        $collection = new SubclassCollection();
        foreach ($this->_elements as $element) {
            if (!$subclasses->contains($element)) {
                $collection->add($element);
            }
        }

        return $collection;
    }

    /**
     * Returns one percent amount
     *
     * @return float
     */
    public function getOnePercentAmount()
    {
        $maxOobSubclass = $this->getMaxOobSubclass();
        $subclass = $maxOobSubclass['subclass'];

        return ($subclass->getTotalAmount() / $subclass->getTargetAllocation());
    }
}
