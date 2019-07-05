<?php

namespace App\Collection;

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
     * @param \App\Entity\SecurityAssignment $item
     *
     * @return \App\Collection\AdminSecuritiesCollection
     */
    public function addItem(\App\Entity\SecurityAssignment $item)
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Remove item.
     *
     * @param \App\Entity\SecurityAssignment $item
     */
    public function removeItem(\App\Entity\SecurityAssignment $item)
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
