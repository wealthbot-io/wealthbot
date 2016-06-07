<?php

namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class AccountCollection extends ArrayCollection
{
    /**
     * Get account for buy subclass
     *
     * @param Subclass $subclass
     * @return Account|null
     */
    public function getAccountForBuySubclass(Subclass $subclass)
    {
        return $this->getAccountForSubclass($subclass, 'buy');
    }

    /**
     * Get account for sell subclass
     *
     * @param Subclass $subclass
     * @return Account|null
     */
    public function getAccountForSellSubclass(Subclass $subclass)
    {
        return $this->getAccountForSubclass($subclass, 'sell');
    }

    private function getAccountForSubclass(Subclass $subclass, $operation)
    {
        $priorities = array(
            Account::PRIORITY_TRADITIONAL_IRA => 1,
            Account::PRIORITY_ROTH_IRA        => 2,
            Account::PRIORITY_TAXABLE         => 3
        );

        $factor = count($priorities);

        if ($subclass->isRothIraAccountType()) {
            $priorities[Account::PRIORITY_ROTH_IRA] -= $factor;
        } elseif ($subclass->isTraditionalIraAccountType()) {
            $priorities[Account::PRIORITY_TRADITIONAL_IRA] -= $factor;
        } elseif ($subclass->isTaxableAccountType()) {
            $priorities[Account::PRIORITY_TAXABLE] -= $factor;
        }

        $iterator = $this->getIterator();
        $iterator->uasort(function ($a, $b) use ($operation, $priorities) {
            $aPriority = $priorities[$a->getPriority()];
            $bPriority = $priorities[$b->getPriority()];

            if ($operation === 'sell') {
                return  ($aPriority > $bPriority) ? -1 : 1;
            } else {
                return ($aPriority < $bPriority) ? -1 : 1;
            }
        });

        $result = null;

        // If buy - get the first sorted by priority account
        if ($operation === 'buy') {
            $iterator->rewind();

            /** @var Account $result */
            $result = $iterator->current();

        // If sell - get the sorted by priority account which contains subclass
        } elseif ($operation === 'sell') {
            $security = $subclass->getSecurity();

            /** @var Account $account */
            foreach ($iterator as $account) {
                if ($account->getSecurities()->containsKey($security->getId())) {
                    $result = $account;
                    break;
                }
            }
        }


        return $result;
    }
}
