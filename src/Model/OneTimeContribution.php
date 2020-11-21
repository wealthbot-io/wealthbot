<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 02.04.13
 * Time: 16:47
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

class OneTimeContribution extends BaseContribution
{
    public function getTransactionFrequency()
    {
        return self::TRANSACTION_FREQUENCY_ONE_TIME;
    }

    public function setTransactionFrequency($transactionFrequency)
    {
        $this->transaction_frequency = self::TRANSACTION_FREQUENCY_ONE_TIME;
    }
}
