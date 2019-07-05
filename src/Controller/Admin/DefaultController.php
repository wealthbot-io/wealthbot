<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;

class DefaultController extends Controller
{
    public function index($name)
    {
        return $this->render('/Admin/Default/index.html.twig', ['name' => $name]);
    }
}
