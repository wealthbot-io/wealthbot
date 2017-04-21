<?php

namespace Pas;

use Pas\TwrCalculator\Account;
use Model\Pas\Repository\SystemClientAccountRepository as SystemClientAccountRepo;

class TwrCalculator
{
    public function getAllAccounts()
    {
        $repository = new SystemClientAccountRepo();
        return $repository->getAllGroupByClient();
    }

    public function run($date)
    {
        // Calculate account values
        $twr = new Account($date);
        $twr->process($this->getAllAccounts());
    }
}