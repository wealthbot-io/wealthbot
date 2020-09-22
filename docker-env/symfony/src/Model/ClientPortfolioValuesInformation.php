<?php

namespace App\Model;

use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use App\Entity\ClientPortfolioValue;

class ClientPortfolioValuesInformation
{
    /** @var float */
    private $minTotalValue;

    /** @var float */
    private $maxTotalValue;

    private $collection;

    private $jsonData;

    public function __construct($collection)
    {
        $jsonData = [];

        foreach ($collection as $item) {
            if (!($item instanceof ClientPortfolioValue)) {
                throw new InvalidTypeException();
            }

            $totalValue = $item->getTotalValue();

            if (null === $this->minTotalValue) {
                $this->minTotalValue = $totalValue;
            } elseif ($this->minTotalValue > $totalValue) {
                $this->minTotalValue = $totalValue;
            }

            if (null === $this->maxTotalValue) {
                $this->maxTotalValue = $totalValue;
            } elseif ($this->maxTotalValue < $totalValue) {
                $this->maxTotalValue = $totalValue;
            }

            $jsonData[] = [
                $item->getDate()->getTimestamp() * 1000,
                $item->getTotalValue(),
            ];
        }

        $this->jsonData = json_encode($jsonData);

        $this->collection = $collection;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return float
     */
    public function getMinTotalValue()
    {
        return $this->minTotalValue;
    }

    /**
     * @return float
     */
    public function getMinTotalValueForGraph()
    {
        return round($this->getMinTotalValue() * 0.95, 2);
    }

    /**
     * @return float
     */
    public function getMaxTotalValue()
    {
        return $this->maxTotalValue;
    }

    /**
     * @return float
     */
    public function getMaxTotalValueForGraph()
    {
        return round($this->getMaxTotalValue() * 0.95, 2);
    }

    public function getJsonData()
    {
        return $this->jsonData;
    }

    public function getLastPortfolioValues()
    {
        $lastIndex = count($this->collection) - 1;

        return isset($this->collection[$lastIndex]) ? $this->collection[$lastIndex] : null;
    }

    public function getFirstPortfolioValues()
    {
        return isset($this->collection[0]) ? $this->collection[0] : null;
    }
}
