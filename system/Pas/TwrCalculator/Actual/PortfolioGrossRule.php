<?php

namespace Pas\TwrCalculator\Actual;

use Model\Pas\Repository\PortfolioTwrValueRepository as PortfolioTwrValueRepo;

class PortfolioGrossRule extends Rule
{
    /**
     * @param int $identificator
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     */
    public function __construct($identificator, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $this->key = 'gross';
        $this->valueRepo = new PortfolioTwrValueRepo();

        parent::__construct($identificator, $dateFrom, $dateTo);
    }
}