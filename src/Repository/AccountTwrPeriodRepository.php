<?php

namespace App\Repository;

use App\Entity\AccountTwrPeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AccountTwrPeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccountTwrPeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccountTwrPeriod[]    findAll()
 * @method AccountTwrPeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccountTwrPeriodRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AccountTwrPeriod::class);
    }

    // /**
    //  * @return AccountTwrPeriod[] Returns an array of AccountTwrPeriod objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AccountTwrPeriod
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
