<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 31.10.13
 * Time: 15:45.
 */

namespace App\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use App\Model\AccountGroup;
use App\Model\ClientAccount;

class ConsolidatedAccountsCollection extends ArrayCollection
{
    public function __construct(array $elements)
    {
        foreach ($elements as $element) {
            if (!($element instanceof ClientAccount)) {
                throw new \InvalidArgumentException('Elements must be instance of ClientAccount.');
            }
        }

        parent::__construct(array_values($elements));
    }

    /**
     * Returns a collection of transfer accounts.
     *
     * @return self|static
     */
    public function getTransferAccounts()
    {
        $filter = function (ClientAccount $account) {
            return AccountGroup::GROUP_FINANCIAL_INSTITUTION === $account->getGroupName();
        };

        return $this->filter($filter);
    }

    /**
     * Returns a collection of rollover accounts.
     *
     * @return self|static
     */
    public function getRolloverAccounts()
    {
        $filter = function (ClientAccount $account) {
            return AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT === $account->getGroupName();
        };

        return $this->filter($filter);
    }

    /**
     * Returns a collection of accounts which has funding section.
     *
     * @return self|static
     */
    public function getFundingAccounts()
    {
        $filter = function (ClientAccount $account) {
            return $account->hasMonthlyContributions() || AccountGroup::GROUP_DEPOSIT_MONEY === $account->getGroupName();
        };

        return $this->filter($filter);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|static
     */
    public function getBankTransferredAccounts()
    {
        $filter = function (ClientAccount $account) {
            return $account->getAccountContribution() && $account->getAccountContribution()->getBankInformation();
        };

        return $this->filter($filter);
    }

    public function remove($key)
    {
        $index = $this->prepareAccountIndex($key);

        return parent::remove($index);
    }

    public function indexOf($element)
    {
        $index = parent::indexOf($element);
        if (is_numeric($index)) {
            ++$index;
        }

        return $index;
    }

    public function get($key)
    {
        $index = $this->prepareAccountIndex($key);

        return parent::get($index);
    }

    public function getPrev($key)
    {
        return $this->get($key - 1);
    }

    public function getNext($key)
    {
        return $this->get($key + 1);
    }

    public function set($key, $value)
    {
        $index = $this->prepareAccountIndex($key);

        parent::set($index, $value);
    }

    public function containsKey($key)
    {
        $index = $this->prepareAccountIndex($key);

        return parent::containsKey($index);
    }

    public function containsPrevKey($key)
    {
        return $this->containsKey($key - 1);
    }

    public function containsNextKey($key)
    {
        return $this->containsKey($key + 1);
    }

    /**
     * Prepare account index and check if index is 0 or less.
     *
     * @param int $key
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    private function prepareAccountIndex($key)
    {
        if (!is_numeric($key)) {
            throw new \InvalidArgumentException('Index must be numeric.');
        }

        if ($key < 1) {
            throw new \InvalidArgumentException('Index must be greater than zero.');
        }

        $index = $key - 1;

        return $index;
    }
}
