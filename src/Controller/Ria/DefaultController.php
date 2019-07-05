<?php

namespace App\Controller\Ria;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;

class DefaultController extends Controller
{
    public function index($name)
    {
        return $this->render(':ria:views:default:index.html.twig', ['name' => $name]);
    }
}
