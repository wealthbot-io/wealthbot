<?php

namespace App\Manager;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use App\Entity\Job;
use App\Entity\ClientPortfolio;
use App\Model\ClientPortfolioValuesInformation;
use App\Repository\ClientPortfolioValueRepository;
use App\Entity\User;
use App\Manager\UserManager;

class ClientPortfolioValuesManager
{
    private $em;

    /** @var ClientPortfolioValueRepository */
    private $repo;

    private $userManager;

    public function __construct(EntityManager $em, UserManager $userManager)
    {
        $this->em = $em;
        $this->repo = $em->getRepository('App\Entity\ClientPortfolioValue');
        $this->userManager = $userManager;
    }

    public function prepareClientPortfolioValuesInformation(User $client)
    {
        $clientPortfolioValuesCollection = $this->repo->findOrderedByDateForClient($client->getId());
        $clientPortfolioValuesInformation = new ClientPortfolioValuesInformation($clientPortfolioValuesCollection);

        return $clientPortfolioValuesInformation;
    }

    public function getLatestClientPortfolioValuesForClientsQuery(User $ria)
    {
        $clients = $this->userManager->findClientsByRia($ria);

        return $this->repo->findLatestValuesForRiaClientsQuery($clients);
    }

    public function getLatestClientPortfolioValuesForClients(User $ria)
    {
        $clients = $this->userManager->findClientsByRia($ria);

        return $this->repo->findLatestValuesForRiaClients($clients);
    }

    public function getValuesByIdsQuery(array $ids)
    {
        return $this->repo->findValuesByIdsQuery($ids);
    }

    public function getHistoryForRiaClients(User $ria)
    {
        return $this->repo->findHistoryForRiaClients($ria);
    }

    public function getHistoryForRiaClientsQuery(User $ria, $filters = [])
    {
        return $this->repo->findHistoryForRiaClientsQuery($ria, $filters);
    }

    public function getLatestValuesForJob(Job $job)
    {
        return $this->repo->findLatestValueForJob($job);
    }

    public function getLatestValuesForJobQuery(Job $job)
    {
        $values = $this->getLatestValuesForJob($job);

        $ids = [];
        foreach ($values as $value) {
            $ids[] = $value->getId();
        }

        return $this->repo->findValuesByIdsQuery($ids);
    }

    public function getLatestValuesForPortfolio(ClientPortfolio $clientPortfolio)
    {
        return $this->repo->getLastValueByClient($clientPortfolio->getClient());
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
