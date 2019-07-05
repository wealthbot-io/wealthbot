<?php

namespace App\Repository;

use App\Entity\PortfolioTwrValue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PortfolioTwrValue|null find($id, $lockMode = null, $lockVersion = null)
 * @method PortfolioTwrValue|null findOneBy(array $criteria, array $orderBy = null)
 * @method PortfolioTwrValue[]    findAll()
 * @method PortfolioTwrValue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PortfolioTwrValueRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PortfolioTwrValue::class);
    }

    // /**
    //  * @return PortfolioTwrValue[] Returns an array of PortfolioTwrValue objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PortfolioTwrValue
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
