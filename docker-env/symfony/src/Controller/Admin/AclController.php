<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\Security\Core\User\UserInterface;

class AclController extends Controller
{
    protected function isPermitted($permission, UserInterface $user = null)
    {
        return $this->get('wealthbot_admin.acl')->isPermitted($permission, $user);
    }

    protected function checkAccess($permission, UserInterface $user = null)
    {
        $this->get('wealthbot_admin.acl')->checkAccess($permission, $user);
    }
}
