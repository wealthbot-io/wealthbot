<?php

namespace Wealthbot\AdminBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DashboardController extends Controller
{
    public function indexAction()
    {
        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->container->get('doctrine.orm.entity_manager');

        /** @var \Wealthbot\UserBundle\Repository\UserRepository $repository  */
        $repository = $em->getRepository('WealthbotUserBundle:User');

        $rias = $repository->getRiasOrderedById(5);
        $clients = $repository->getClientsOrderedById(10);

        return $this->render('WealthbotAdminBundle:Dashboard:index.html.twig', array('rias' => $rias, 'clients' => $clients));
    }
}
