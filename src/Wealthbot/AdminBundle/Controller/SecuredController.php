<?php

namespace Wealthbot\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\Security;
use Wealthbot\AdminBundle\Model\Acl;

class SecuredController extends AclController
{
    public function loginAction(Request $request)
    {
        /* @var $session \Symfony\Component\HttpFoundation\Session */
        $session = $request->getSession();

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(Security::AUTHENTICATION_ERROR)) {
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        } else {
            $error = '';
        }

        if ($error) {
            // TODO: this is a potential security risk (see http://trac.symfony-project.org/ticket/9523)
            $error = $error->getMessage();
        }
        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get(Security::LAST_USERNAME);

        $csrfToken = $this->container->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        return $this->renderLogin([
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ]);
    }

    public function loginAsAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $username = $request->get('username');
        $this->checkAccess(Acl::PERMISSION_LOGIN_AS);

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByUsername($username);

        if (!$user) {
            throw $this->createNotFoundException(sprintf('User with username: "%s" does not exist.'));
        }

        if (!$user->hasRole('ROLE_RIA') && !$user->hasRole('ROLE_CLIENT')) {
            throw new AccessDeniedException(sprintf('Access Denied. You cannot login as "%s"', $username));
        }

        $tokenStorage = $this->get('security.token_storage');
        $roles = $user->getRoles();
        $roles[] = new SwitchUserRole('ROLE_PREVIOUS_ADMIN', $tokenStorage->getToken());

        $token = new UsernamePasswordToken($user, null, 'main', $roles);
        $session = $request->getSession();
        $session->set('rx_admin.login_as_token',  serialize($token));

        $riaClient = $em->getRepository('WealthbotUserBundle:User')->getClientByIdAndRiaId(
            $request->get('client_id', null),
            $user->getId()
        );

        if ($riaClient) {
            $session->set('wealthbot.ria_view.client_id', $riaClient->getId());
        }

        return $this->redirect($this->generateUrl('rx_after_login'));
    }

    public function checkAction()
    {
        throw new \RuntimeException('You must configure the check path to be handled by the firewall using form_login in your security firewall configuration.');
    }

    public function logoutAction()
    {
        throw new \RuntimeException('You must activate the logout in your security firewall configuration.');
    }

    protected function renderLogin(array $data)
    {
        $template = sprintf('WealthbotAdminBundle:Dashboard:login.html.%s', $this->container->getParameter('fos_user.template.engine'));

        return $this->container->get('templating')->renderResponse($template, $data);
    }
}
