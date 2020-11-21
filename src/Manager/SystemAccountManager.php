<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.03.13
 * Time: 14:36
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use App\Entity\ClientAccount;
use App\Entity\SystemAccount;
use App\Entity\User;

class SystemAccountManager implements SystemAccountManagerInterface
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \App\Manager\ClientAccountValuesManager */
    private $accountValuesManager;

    public function __construct(EntityManager $em, ClientAccountValuesManager $accountValuesManager)
    {
        $this->em = $em;
        $this->accountValuesManager = $accountValuesManager;
    }

    /**
     * Create new system account for client account.
     *
     * @param ClientAccount $clientAccount
     *
     * @return SystemAccount|null
     */
    public function createSystemAccountForClientAccount(ClientAccount $clientAccount)
    {
        $systemAccount = $this->createAccount($clientAccount);

        $this->em->persist($systemAccount);
        $this->em->flush();

        return $systemAccount;
    }

    /**
     * @param User $client
     * @param bool $isClientView
     *
     * @return array
     */
    public function getAccountsForClient(User $client, $isClientView = false)
    {
        if ($isClientView) {
            $accounts = $this->em->getRepository('App\Entity\SystemAccount')->findByClientIdAndNotStatus(
                $client->getId(),
                SystemAccount::STATUS_CLOSED
            );
        } else {
            $accounts = $client->getSystemAccounts()->toArray();
        }

        return $accounts;
    }

    /**
     * @param User $client
     *
     * @return SystemAccount[]
     */
    public function getActiveAccountsForClient(User $client)
    {
        $repository = $this->em->getRepository('App\Entity\SystemAccount');
        $criteria = [
            'client' => $client,
            'status' => SystemAccount::STATUS_ACTIVE,
        ];

        return $repository->findBy($criteria);
    }

    public function save(SystemAccount $account)
    {
        $this->em->persist($account);
        $this->em->flush();
    }

    /**
     * @param User $client
     *
     * @return array
     */
    public function getClientAccountsValues(User $client)
    {
        $accounts = $this->getAccountsForClient($client);
        $accountValues = [
            'total' => [
                'value' => 0,
                'projected_value' => 0,
                'contributions' => 0,
                'distributions' => 0,
                'sas_cash' => 0,
            ],
        ];

        /** @var SystemAccount $account */
        foreach ($accounts as $account) {
            $clientAccount = $account->getClientAccount();

            $value = $this->accountValuesManager->getTotalValue($account);
            $projectedValue = $account->getProjectedValue();
            $contributions = $clientAccount->getContributionsSum();
            $distributions = $clientAccount->getDistributionsSum();

            $accountValues[$account->getId()] = [
                'account' => $account,
                'value' => $value,
                'projected_value' => $projectedValue,
                'contributions' => $contributions,
                'distributions' => $distributions,
            ];

            $accountValues['total']['value'] += $value;
            $accountValues['total']['projected_value'] += $projectedValue;
            $accountValues['total']['contributions'] += $contributions;
            $accountValues['total']['distributions'] += $distributions;
            $accountValues['total']['sas_cash'] += $clientAccount->getSasCashSum();
        }

        $fundedPercent = 0;
        if ($accountValues['total']['projected_value'] > 0) {
            $fundedPercent = round(($accountValues['total']['value'] / $accountValues['total']['projected_value'] * 100), 2);
        }

        $accountValues['total']['funded_percent'] = $fundedPercent;

        return $accountValues;
    }

    /**
     * @param User $client
     *
     * @return bool
     */
    public function isClientAccountsHaveInitRebalanceStatus(User $client)
    {
        $accounts = $this->getAccountsForClient($client);

        /** @var SystemAccount $account */
        foreach ($accounts as $account) {
            if (!$account->isInitRebalance()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return new system account with client, client account, account number and account description.
     *
     * @param ClientAccount $clientAccount
     *
     * @return SystemAccount
     */
    private function createAccount(ClientAccount $clientAccount)
    {
        // If system account is exist then update it
        $systemAccount = $clientAccount->getSystemAccount();
        if (!$systemAccount) {
            $systemAccount = new SystemAccount();
        }

        $systemAccount->setClient($clientAccount->getClient());
        $systemAccount->setClientAccount($clientAccount);
        $systemAccount->setType($clientAccount->getSystemType());
        $systemAccount->setAccountNumber('CE-000-'.rand(100000000, 999999999));
        $systemAccount->setAccountDescription($clientAccount->getOwnersAsString().' '.$clientAccount->getTypeName());

        return $systemAccount;
    }
}
