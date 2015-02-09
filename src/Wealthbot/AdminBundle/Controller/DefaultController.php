<?php

namespace Wealthbot\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WealthbotAdminBundle:Default:index.html.twig', array('name' => $name));
    }
}
