<?php

namespace Wealthbot\AdminBundle\Manager;

use Doctrine\ORM\EntityManager;
use Wealthbot\AdminBundle\Entity\Transaction;
use Wealthbot\AdminBundle\Repository\TransactionRepository;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Repository\ClientAccountValueRepository;
use Wealthbot\UserBundle\Entity\User;

class TwrCalculatorManager
{
    /* @var EntityManager $em */
        protected $em,
        /* @var User $client */
        $client,
        /* @var SystemAccount|null $account */
        $account = null,
        $twrData,
        $startDate,
        $endDate,
        $period
    ;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function setClient($client)
    {
        $this->client = $client;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
    }

    public function getBillingInceptionDate()
    {
        $inceptionDate = new \DateTime();

        /* @var ClientAccountValueRepository $clientAccountValuesRepo */
        $clientAccountValuesRepo = $this->em->getRepository('WealthbotClientBundle:ClientAccountValue');

        /* @var SystemAccount $account */
        foreach ($this->client->getSystemAccounts() as $account) {
            $checkAccount = !$this->account || $this->account === $account->getClientAccount();
            $isSetBillingInception = null !== $account->getBillingInception();

            if ($checkAccount) {
                if ($isSetBillingInception) {
                    $newInceptionDate = $account->getBillingInception();
                } else {
                    $newInceptionDate = $clientAccountValuesRepo->getFirstActivityDate($account->getClientAccount())->getDate();
                }

                $isInceptionDateDeprecated = $inceptionDate > $newInceptionDate;

                if ($isInceptionDateDeprecated) {
                    $inceptionDate = $newInceptionDate;
                }
            }
        }

        return $inceptionDate;
    }

    public function getBeginningValue()
    {
        if ($this->account) {
            $value = $this
                ->em
                ->getRepository('WealthbotClientBundle:ClientAccountValue')
                ->getFirstByDate($this->account->getClientAccount(), $this->startDate, $this->endDate)
            ;
        } else {
            $value = $this
                ->em
                ->getRepository('WealthbotClientBundle:ClientPortfolioValue')
                ->getFirstByDate($this->client, $this->startDate, $this->endDate)
            ;
        }

        return $value ? $value->getTotalValue() : 0;
    }

    public function getEndingValue()
    {
        if ($this->account) {
            $value = $this
                ->em
                ->getRepository('WealthbotClientBundle:ClientAccountValue')
                ->getLastByDate($this->account->getClientAccount(), $this->startDate, $this->endDate)
            ;
        } else {
            $value = $this
                ->em
                ->getRepository('WealthbotClientBundle:ClientPortfolioValue')
                ->getLastByDate($this->client, $this->startDate, $this->endDate)
            ;
        }

        return $value ? $value->getTotalValue() : 0;
    }

    public function getContributions()
    {
        $contributionSumm = 0;

        /* @var TransactionRepository $transactionsRepo */
        $transactionsRepo = $this->em->getRepository('WealthbotAdminBundle:Transaction');
        $contributions = $transactionsRepo->getContributionsByPeriod($this->startDate, $this->endDate, $this->account);

        /* @var Transaction $contribution */
        foreach ($contributions as $contribution) {
            $contributionSumm += $contribution->getNetAmount();
        }

        return $contributionSumm;
    }

    public function getWithdrawals()
    {
        $withdrawalSumm = 0;

        /* @var TransactionRepository $transactionsRepo */
        $transactionsRepo = $this->em->getRepository('WealthbotAdminBundle:Transaction');

        $withdrawals = $transactionsRepo->getDistributionsByPeriod($this->startDate, $this->endDate, $this->account);
        /* @var Transaction $withdrawal */
        foreach ($withdrawals as $withdrawal) {
            $withdrawalSumm += $withdrawal->getNetAmount();
        }

        return $withdrawalSumm;
    }

    public function getInvestmentGain()
    {
        return $this->getEndingValue() - $this->getBeginningValue() - $this->getContributions() + $this->getWithdrawals();
    }

    public function loadTwrData()
    {
        if ($this->account) {
            $this->twrData = $this->em
                ->getRepository('WealthbotClientBundle:ClientTwrPeriod')
                ->findOneBy([
                    'accountNumber' => $this->account->getAccountNumber(),
            ]);
        } else {
            $this->twrData = $this->em
                ->getRepository('WealthbotClientBundle:PortfolioTwrPeriod')
                ->findOneBy([
                    'client' => $this->client,
            ]);
        }
    }

    public function getNetActual()
    {
        if ($this->twrData) {
            switch ($this->period) {
                case 2:
                    return $this->twrData->getNetQtd();
                case 3:
                    return $this->twrData->getNetYtd();
                case 4:
                    return $this->twrData->getNetYr1();
                case 5:
                    return $this->twrData->getNetYr3();
                case 6:
                    return $this->twrData->getNetSinceInception();
                default:
                    return $this->twrData->getNetMtd();
            }
        }

        return 0;
    }

    public function getNetAnnualized()
    {
        $twr = $this->getNetActual();

        return $this->calculateAnnualizedTwr($twr);
    }

    public function getGrossActual()
    {
        if ($this->twrData) {
            switch ($this->period) {
                case 2:
                    return $this->twrData->getGrossQtd();
                case 3:
                    return $this->twrData->getGrossYtd();
                case 4:
                    return $this->twrData->getGrossYr1();
                case 5:
                    return $this->twrData->getGrossYr3();
                case 6:
                    return $this->twrData->getGrossSinceInception();
                default:
                    return $this->twrData->getGrossMtd();
            }
        }

        return 0;
    }

    public function getGrossAnnualized()
    {
        $twr = $this->getGrossActual();

        return $this->calculateAnnualizedTwr($twr);
    }

    /**
     * Calculate annualized TWR.
     *
     * @param $actualTwr
     *
     * @return int
     */
    public function calculateAnnualizedTwr($actualTwr)
    {
        $annualized = 0;
        $interval = $this->endDate->diff($this->startDate)->days;

        if ($interval > 365) {
            $annualized = ((1.0 + (float) $actualTwr) ^ (365 / $interval) - 1.0) * 100.0;
        }

        return $annualized;
    }
}
