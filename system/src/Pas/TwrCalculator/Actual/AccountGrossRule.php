<?php

namespace System\Pas\TwrCalculator\Actual;

use System\Model\Pas\Repository\AccountTwrValueRepository as AccountTwrValueRepo;

class AccountGrossRule extends Rule
{
    /**
     * @param int $identificator
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     */
    public function __construct($identificator, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $this->key = 'gross';
        $this->valueRepo = new AccountTwrValueRepo();

        parent::__construct($identificator, $dateFrom, $dateTo);
    }
}