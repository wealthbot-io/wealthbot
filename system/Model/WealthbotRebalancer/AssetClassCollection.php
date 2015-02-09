<?php
//namespace Model\WealthbotRebalancer;
//
//require_once(__DIR__ . '/../../AutoLoader.php');
//\AutoLoader::registerAutoloader();
//
//class AssetClassCollection extends ArrayCollection {
//
//
//    public function startRebalanceOob()
//    {
//        if (0 === count($this->_elements)) {
//            return false;
//        }
//
//        $i = 0;
//        while ($i < 1000) {
//
//            $this->rebuildAllocations();
//
//            if ($this->isOutOfBalance()) {
//                $isStopRebalance = $this->rebalanceOob();
//
//                if ($isStopRebalance) {
//                    break;
//                }
//            } else {
//                break;
//            }
//
//            $i++;
//        }
//    }
//
//    public function isOutOfBalance()
//    {
//        /** @var Subclass $element */
//        foreach ($this->_elements as $element) {
//            if (abs($element->calcOOB()) > $element->getToleranceBand()) {
//                return true;
//            }
//        }
//
//        return false;
//    }
//
//    public function rebalanceOob()
//    {
//        //TODO: after beta
//        return true;
//    }
//
//    public function rebuildAllocations()
//    {
//        $totalAmount = 0;
//        /** @var AssetClass $assetClass */
//        foreach ($this->_elements as $assetClass) {
//            foreach ($assetClass->getSubclasses() as $subclass) {
//                $totalAmount += $subclass->getSecurity()->getAmount();
//            }
//        }
//
//        foreach ($this->_elements as $assetClass) {
//
//            $assetClassAllocation = 0;
//
//            foreach ($assetClass->getSubclasses() as $subclass) {
//                /** @var Subclass $subclass */
//                $amount = $subclass->getSecurity()->getAmount();
//                if (0 === $amount) {
//                    $currentAllocation = 0;
//                } else {
//                    $currentAllocation = ($amount * 100) / $totalAmount;
//                }
//
//                $assetClassAllocation += $currentAllocation;
//            }
//
//            $assetClass->setCurrentAllocation($assetClassAllocation);
//        }
//    }
//
//
//}
