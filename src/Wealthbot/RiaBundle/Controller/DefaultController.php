<?php

namespace Wealthbot\RiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WealthbotRiaBundle:Default:index.html.twig', ['name' => $name]);
    }
}
