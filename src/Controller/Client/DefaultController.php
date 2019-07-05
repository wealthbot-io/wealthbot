<?php

namespace App\Controller\Client;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;

class DefaultController extends Controller
{
    public function index($name)
    {
        return $this->render('/Client/Default/index.html.twig', ['name' => $name]);
    }
}
