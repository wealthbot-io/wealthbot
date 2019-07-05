<?php

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use App\Entity\ClientAccount;
use App\Entity\User;

class CashCalculationManager
{
    /** @var EntityManager */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getCashOnDate(User $user, \DateTime $date)
    {
        /** @var ClientAccount[] $accounts */
        $accounts = $user->getClientAccounts();
        $sum = 0;
        foreach ($accounts as $account) {
            $systemAccount = $account->getSystemAccount();
            if ($systemAccount) {
                $sum += $this->em->getRepository('App\Entity\ClientAccountValue')->getFreeCashBeforeDate(
                    $systemAccount,
                    $date
                );
            }
        }

        return $sum;
    }

    public function getAccountCashOnDate(ClientAccount $account, \DateTime $date)
    {
        $systemAccount = $account->getSystemAccount();
        if ($systemAccount) {
            return $this->em->getRepository('App\Entity\ClientAccountValue')->getFreeCashBeforeDate(
                $systemAccount,
                $date
            );
        }

        return 0;
    }

    public function getAccountValueOnDate(ClientAccount $account, \DateTime $date)
    {
        $systemAccount = $account->getSystemAccount();
        $v = 0;
        if ($systemAccount) {
            $v = $this->em->getRepository('App\Entity\ClientAccountValue')->getSumBeforeDate(
                $systemAccount,
                $date
            );
        }
        if (0 === $v) {
            $v = $account->getValue();
        }

        return $v;
    }
}
