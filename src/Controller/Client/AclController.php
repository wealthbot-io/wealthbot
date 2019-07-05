<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 30.07.13
 * Time: 11:57
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Client;

use App\Model\Acl;

trait AclController
{
    public function getUser()
    {
        $user = $this->getOriginalUser();
        if (null === $user) {
            return;
        }

        /** @var Acl $acl */
        $acl = $this->get('wealthbot_client.acl');

        if ($acl->isSlaveClient()) {
            return $user->getMasterClient();
        } elseif ($acl->isRiaClientView()) {
            return $acl->getClientForRiaClientView($user);
        }

        return $user;
    }

    public function getOriginalUser()
    {
        return parent::getUser();
    }

    protected function isRiaClientView()
    {
        $acl = $this->get('wealthbot_client.acl');

        return $acl->isRiaClientView();
    }
}
