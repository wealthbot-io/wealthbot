<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.09.12
 * Time: 12:38
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SecuredController extends Controller
{
    public function loginAsAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');

        $username = $request->get('username');

        $ria = $this->getUser();

        $client = $userManager->findUserByUsername($username);

        if (!$client) {
            throw $this->createNotFoundException(sprintf('User with username: "%s" does not exist.'));
        }

        if (!$client->hasRole('ROLE_CLIENT') || $client->getRia()->getId() !== $this->getUser()->getId()) {
            throw new AccessDeniedException(sprintf('Access Denied. You cannot login as "%s"', $username));
        }

        $acl = $this->get('wealthbot_client.acl');
        $acl->resetRiaClientView($ria);
        $acl->setClientForRiaClientView($ria, $client->getId());

        return $this->redirect($this->generateUrl('rx_after_login'));
    }
}
