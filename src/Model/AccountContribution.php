<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.02.13
 * Time: 17:16
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

class AccountContribution extends BaseContribution
{
    /**
     * Set transaction_frequency.
     *
     * @param int $transactionFrequency
     *
     * @return AccountContribution
     *
     * @throws \InvalidArgumentException
     */
    public function setTransactionFrequency($transactionFrequency)
    {
        parent::setTransactionFrequency($transactionFrequency);

        return $this;
    }

    /**
     * Get transaction_frequency as string.
     *
     * @return string
     */
    public function getTransactionFrequencyAsString()
    {
        if (null === $this->transaction_frequency) {
            return '';
        }

        $frequencies = self::getTransactionFrequencyChoices();

        return $frequencies[$this->transaction_frequency];
    }

    /**
     * Returns true if transaction_frequency is TRANSACTION_FREQUENCY_ONE_TIME and false otherwise.
     *
     * @return bool
     */
    public function isOneTimeContribution()
    {
        return (self::TRANSACTION_FREQUENCY_ONE_TIME === $this->getTransactionFrequency()) ? true : false;
    }
}
