<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.02.13
 * Time: 17:16
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Model;

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
        $choices = self::getTransactionFrequencyChoices();
        if (!is_null($transactionFrequency) && !array_key_exists($transactionFrequency, $choices)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for account_transfer_types.transaction_frequency : %s.', $transactionFrequency)
            );
        }

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
        return ($this->getTransactionFrequency() === self::TRANSACTION_FREQUENCY_ONE_TIME) ? true : false;
    }
}
