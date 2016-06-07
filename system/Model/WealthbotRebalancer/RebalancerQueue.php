<?php

namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RebalancerQueue extends ArrayCollection
{
    public function addItem(QueueItem $item)
    {
        $exists = $this->existsInQueue($item);
        if ($exists) {
            $exists->setQuantity($exists->getQuantity() + $item->getQuantity());
            $exists->setAmount($exists->getAmount() + $item->getAmount());
        } else {
            $this->add($item);
        }
    }

    public function existsInQueue(QueueItem $item)
    {
        /** @var QueueItem $element */
        foreach ($this->_elements as $element) {
            if ($element->getRebalancerActionId() == $item->getRebalancerActionId() &&
                $element->getAccount()->getId() == $item->getAccount()->getId() &&
                $element->getSecurity()->getId() == $item->getSecurity()->getId() &&
                $element->getStatus() == $item->getStatus()
            ) {
                if ((!$element->getLot() && !$item->getLot()) ||
                    ($element->getLot()->getId() == $item->getLot()->getId())
                ) {
                    return $element;
                }
            }
        }

        return null;
    }
} 