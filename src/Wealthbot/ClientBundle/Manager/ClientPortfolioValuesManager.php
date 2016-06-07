<?php

namespace Wealthbot\ClientBundle\Manager;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Wealthbot\AdminBundle\Entity\Job;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\ClientBundle\Model\ClientPortfolioValuesInformation;
use Wealthbot\ClientBundle\Repository\ClientPortfolioValueRepository;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Manager\UserManager;

class ClientPortfolioValuesManager
{
    private $em;

    /** @var ClientPortfolioValueRepository  */
    private $repo;

    private $userManager;

    public function __construct(EntityManager $em, UserManager $userManager)
    {
        $this->em = $em;
        $this->repo = $em->getRepository('WealthbotClientBundle:ClientPortfolioValue');
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
     * @param mixed    $id          The identifier.
     * @param int      $lockMode    The lock mode.
     * @param int|null $lockVersion The lock version.
     *
     * @return object|null The entity instance or NULL if the entity can not be found.
     */
    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        return $this->repo->find($id, $lockMode, $lockVersion);
    }
}
