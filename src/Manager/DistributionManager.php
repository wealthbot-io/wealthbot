<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 18.02.14
 * Time: 18:27.
 */

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use App\Entity\Distribution;
use App\Entity\SystemAccount;

class DistributionManager
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \Doctrine\ORM\EntityRepository */
    private $repository;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('App\Entity\Distribution');
    }

    /**
     * Get scheduled distribution for account.
     *
     * @param SystemAccount $account
     *
     * @return Distribution|null
     */
    public function getScheduledDistribution(SystemAccount $account)
    {
        return $this->findOneBy(['systemClientAccount' => $account, 'type' => Distribution::TYPE_SCHEDULED]);
    }

    /**
     * Get one-time distributions for account.
     *
     * @param SystemAccount $account
     *
     * @return Distribution[]
     */
    public function getOneTimeDistributions(SystemAccount $account)
    {
        return $this->findBy(['systemClientAccount' => $account, 'type' => Distribution::TYPE_ONE_TIME]);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    /**
     * Create new scheduled distribution.
     *
     * @param SystemAccount $account
     *
     * @return Distribution
     */
    public function createScheduledDistribution(SystemAccount $account)
    {
        $distribution = new Distribution();
        $distribution->setSystemClientAccount($account);
        $distribution->setType(Distribution::TYPE_SCHEDULED);
        $distribution->setTransferMethod(Distribution::TRANSFER_METHOD_BANK_TRANSFER);

        return $distribution;
    }

    /**
     * Create new one-time distribution.
     *
     * @param SystemAccount $account
     *
     * @return Distribution
     */
    public function createOneTimeDistribution(SystemAccount $account)
    {
        $distribution = new Distribution();
        $distribution->setSystemClientAccount($account);
        $distribution->setType(Distribution::TYPE_ONE_TIME);

        return $distribution;
    }
}
