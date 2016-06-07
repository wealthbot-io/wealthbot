<?php

namespace Wealthbot\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Wealthbot\AdminBundle\Model\Acl;

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
