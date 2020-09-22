<?php

namespace App\Manager;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use App\Entity\ClientActivitySummary;
use App\Entity\User;

class ActivitySummaryManager
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function findRiaActivitySummariesQuery($riaId, $limit = null)
    {
        return $this->em->getRepository('App\Entity\ClientActivitySummary')->findVisibleByRiaIdQuery($riaId, $limit);
    }

    public function findRiaActivitySummaries($riaId, $limit = null)
    {
        return $this->findRiaActivitySummariesQuery($riaId, $limit)->getResult();
    }

    public function findClientActivitySummaries($clientId)
    {
        return $this->findBy(['client_id' => $clientId]);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->em->getRepository('App\Entity\ClientActivitySummary')->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function find($id, $lockMode = LockMode::NONE, $lockVersion = null)
    {
        return $this->em->getRepository('App\Entity\ClientActivitySummary')->find($id, $lockMode, $lockVersion);
    }

    public function hasDeleteAccess(User $user, ClientActivitySummary $activitySummary)
    {
        if ($user->hasRole('ROLE_RIA') && $activitySummary->getClient()->getRia()->getId() === $user->getId()) {
            return true;
        }

        return false;
    }
}
