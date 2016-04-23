<?php

namespace Pas\TwrCalculator\Actual;

use Pas\TwrCalculator\Functions;

class Rule implements IRule
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @var \DateTime
     */
    protected $dateTo;

    /**
     * @var \DateTime
     */
    protected $dateFrom;

    /**
     * @var array
     */
    protected $dataHash = array();

    /**
     * @var ClientTwrValueRepo|PortfolioTwrValueRepo
     */
    protected $valueRepo;

    /**
     * @var int
     */
    protected $identificator;

    /**
     * @param int $identificator
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     */
    public function __construct($identificator, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $this->dateTo = $dateTo;
        $this->dateFrom = $dateFrom;
        $this->identificator = $identificator;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return \DateTime
     */
    public function getDateTo()
    {
        return $this->dateTo;
    }

    /**
     * @return \DateTime
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @return int
     */
    public function getIdentificator()
    {
        return $this->identificator;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function normalizeData($data)
    {
        $results = array();

        foreach ($data as $row) {
            $getter = 'get' . ucfirst($this->getKey()) . 'Value';
            $results[] = array('value' => $row->$getter());
        }

        return $results;
    }

    /**
     * @return float
     */
    public function calculate()
    {
        $dateFrom = $this->getDateFrom();

        $index = $dateFrom instanceof \DateTime ? $dateFrom->format('dmY') : 'index';
        $identificator = $this->getIdentificator();

        if (!isset($this->dataHash[$identificator][$index])) {
            $this->dataHash[$identificator][$index] = $this->valueRepo->findAllByPeriod($identificator, $dateFrom, $this->getDateTo());
        }

        $values = $this->normalizeData($this->dataHash[$identificator][$index]);

        return Functions::calculateActualTwr($values);
    }
}