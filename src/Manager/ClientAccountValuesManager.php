<?php

namespace App\Manager;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use App\Entity\Job;
use App\Entity\RebalancerAction;
use App\Entity\ClientAccountValue;
use App\Entity\ClientPortfolioValue;
use App\Entity\SystemAccount;
use App\Repository\ClientAccountValueRepository;
use App\Entity\RiaCompanyInformation;
use App\Entity\User;
use App\Manager\UserManager;

class ClientAccountValuesManager
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \App\Repository\ClientAccountValueRepository */
    private $repo;

    public function getLatestClientAccountValuesByPortfolioValue(ClientPortfolioValue $clientPortfolioValue)
    {
        return $this->repo->findLatestClientAccountValuesByPortfolioValue($clientPortfolioValue);
    }

    /** @var \App\Manager\UserManager */
    private $userManager;

    public function __construct(EntityManager $em, UserManager $userManager)
    {
        $this->em = $em;
        $this->repo = $em->getRepository('App\Entity\ClientAccountValue');
        $this->userManager = $userManager;
    }

    public function getLatestClientAccountValuesForAdminQuery(ClientPortfolioManager $clientPortfolioManager)
    {
        $clients = $this->userManager->findClientsByRelationsType(RiaCompanyInformation::RELATIONSHIP_TYPE_TAMP);

        $clientPortfolios = [];
        foreach ($clients as $client) {
            $activePortfolio = $clientPortfolioManager->getCurrentPortfolio($client);
            if ($activePortfolio) {
                $clientPortfolios[] = $activePortfolio;
            }
        }

        return $this->repo->findLatestValuesForClientsQuery($clientPortfolios);
    }

    public function getHistoryForAdminQuery($filters = [])
    {
        return $this->repo->findHistoryForAdminQuery($filters);
    }

    /**
     * @param User                   $ria
     * @param ClientPortfolioManager $clientPortfolioManager
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getLatestClientAccountValuesForClientsQuery(User $ria, ClientPortfolioManager $clientPortfolioManager)
    {
        $clients = $this->userManager->findClientsByRia($ria);

        $clientPortfolios = [];
        foreach ($clients as $client) {
            $activePortfolio = $clientPortfolioManager->getCurrentPortfolio($client);
            if ($activePortfolio) {
                $clientPortfolios[] = $activePortfolio;
            }
        }

        return $this->repo->findLatestValuesForClientsQuery($clientPortfolios);
    }

    /**
     * @param ClientPortfolioManager $clientPortfolioManager
     *
     * @return ClientAccountValue[]
     */
    public function getLatestClientAccountValuesForAdmin(ClientPortfolioManager $clientPortfolioManager)
    {
        $qb = $this->getLatestClientAccountValuesForAdminQuery($clientPortfolioManager);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User                   $ria
     * @param ClientPortfolioManager $clientPortfolioManager
     *
     * @return ClientAccountValue[]
     */
    public function getLatestClientAccountValuesForClients(User $ria, ClientPortfolioManager $clientPortfolioManager)
    {
        $qb = $this->getLatestClientAccountValuesForClientsQuery($ria, $clientPortfolioManager);

        return $qb->getQuery()->getResult();
    }

    public function getHistoryForRiaClientsQuery(User $ria, $filters = [])
    {
        return $this->repo->findHistoryForRiaClientsQuery($ria, $filters);
    }

    public function getLatestValuesForJobQuery(Job $job)
    {
        $systemClientAccountIds = [];

        /** @var RebalancerAction $rebalancerAction */
        foreach ($job->getRebalancerActions() as $rebalancerAction) {
            $systemClientAccountIds[] = $rebalancerAction->getClientAccountValue()->getSystemClientAccount()->getId();
        }

        return $this->repo->findLatestValuesForSystemClientAccountIdsQuery($systemClientAccountIds);
    }

    /**
     * @param SystemAccount $account
     *
     * @return ClientAccountValue|null
     */
    public function getLatestValueBySystemAccount(SystemAccount $account)
    {
        return $this->repo->getLatestValueForSystemClientAccountId($account->getId());
    }

    /**
     * Get total value for system client account.
     *
     * @param SystemAccount $account
     *
     * @return float
     */
    public function getTotalValue(SystemAccount $account)
    {
        $accountValue = $this->getLatestValueBySystemAccount($account);
        if ($accountValue) {
            $totalValue = $accountValue->getTotalValue();
        } else {
            $totalValue = $account->getClientAccount()->getValueSum();
        }

        return round($totalValue, 2);
    }

    /**
     * Finds an entity by its primary key / identifier.
     *
     * @param mixed    $id          the identifier
     * @param int      $lockMode    the lock mode
     * @param int|null $lockVersion the lock version
     *
     * @return object|null the entity instance or NULL if the entity can not be found
     */
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        return $this->repo->find($id, $lockMode, $lockVersion);
    }
}
