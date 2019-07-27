<?php


namespace App\Api;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface RebalancerInterface
 * @package App\Api
 */
interface RebalancerInterface
{
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, \Symfony\Component\Security\Core\Security $security);

    public function rebalance();

    public function updateSecurities();
}
