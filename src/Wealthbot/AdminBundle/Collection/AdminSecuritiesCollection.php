<?php

namespace Wealthbot\AdminBundle\Collection;

class AdminSecuritiesCollection
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $items;

    public function __construct(array $items = [])
    {
        if (!empty($items)) {
            $this->items = new \Doctrine\Common\Collections\ArrayCollection($items);
        }
    }

    /**
     * Add item.
     *
     * @param \Wealthbot\AdminBundle\Entity\SecurityAssignment $item
     *
     * @return \Wealthbot\AdminBundle\Collection\AdminSecuritiesCollection
     */
    public function addItem(\Wealthbot\AdminBundle\Entity\SecurityAssignment $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Remove item.
     *
     * @param \Wealthbot\AdminBundle\Entity\SecurityAssignment $item
     */
    public function removeItem(\Wealthbot\AdminBundle\Entity\SecurityAssignment $item)
    {
        $this->items->removeElement($item);
    }

    /**
     * Get fees.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }
}
