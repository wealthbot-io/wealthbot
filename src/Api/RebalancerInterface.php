<?php


namespace App\Api;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface RebalancerInterface
{

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, \Symfony\Component\Security\Core\Security $security);

    public function rebalance();

    public function updateSecurities();
}