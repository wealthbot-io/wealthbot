<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;

class DashboardController extends Controller
{
    public function index()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var \Repository\UserRepository $repository */
        $repository = $em->getRepository('App\Entity\User');

        $rias = $repository->getRiasOrderedById(5);
        $clients = $repository->getClientsOrderedById(10);

        return $this->render('/Admin/Dashboard/index.html.twig', ['rias' => $rias, 'clients' => $clients]);
    }
}
