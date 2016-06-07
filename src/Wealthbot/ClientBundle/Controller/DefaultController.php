<?php

namespace Wealthbot\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WealthbotClientBundle:Default:index.html.twig', ['name' => $name]);
    }
}
