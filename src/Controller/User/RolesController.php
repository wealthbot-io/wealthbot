<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 12.09.12
 * Time: 16:06
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use App\Entity\RiaCompanyInformation;
use App\Entity\User;

class RolesController extends Controller
{
    public function afterLogin()
    {
        $loginManager = $this->container->get('wealthbot_user.subdomain_manager');
        if ($loginManager->hasSubDomain()) {
            return $this->redirect($loginManager->generateUrl('rx_after_login'));
        }

        $authorizationChecker = $this->container->get('security.authorization_checker');
        $tokenStorage = $this->container->get('security.token_storage');

        $user = $this->getCurrentUser($tokenStorage);

        if ($authorizationChecker->isGranted('ROLE_ADMIN')) {
            $redirectUrl = $this->generateUrl('rx_admin_homepage');
        } else {
            $this->checkIsPasswordExpired($user);

            if (($authorizationChecker->isGranted('ROLE_RIA') || $authorizationChecker->isGranted('ROLE_RIA_USER'))) {
                $redirectUrl = $this->generateUrl('rx_ria_dashboard');
            } elseif ($authorizationChecker->isGranted('ROLE_CLIENT')) {
                $redirectUrl = $this->generateUrl('rx_client_dashboard');
            } else {
                $redirectUrl = $this->generateUrl('rx_user_homepage');
            }
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Get route for client user.
     *
     * @param User $user
     *
     * @return string
     */
    protected function getRouteForClient(User $user)
    {
        $redirectUrl = $this->getSessionRedirectUrl();

        if ($redirectUrl) {
            $this->removeSessionRedirectUrl();

            return $redirectUrl;
        }

        if (is_object($user->getProfile())) {
            $registrationStep = $user->getProfile()->getRegistrationStep();

            switch ($registrationStep) {
                case 0:
                    return $this->generateUrl('rx_client_profile_step_one');
                    break;
                case 1:
                    return $this->generateUrl('rx_client_profile_step_two');
                    break;
                case 2:
                    return $this->generateUrl('rx_client_profile_step_three');
                    break;
                case 3:
                    return $this->generateUrl('rx_client_portfolio');
                    break;
                case 4:
                    return $this->generateUrl('rx_client_portfolio');
                    break;
                case 5:
                    return $this->generateUrl('rx_client_transfer');
                    break;
                case 6:
                    return $this->generateUrl('rx_client_transfer');
                    break;
                case 7:
                    return $this->generateUrl('rx_client_dashboard');
                    break;
                default:
                    return $this->generateUrl('rx_user_homepage');
                    break;
            }
        }

        return $this->generateUrl('rx_client_profile_step_one');
    }



    private function getSessionRedirectUrl()
    {
        $session = $this->container->get('session');

        return $session->get('redirect_url', null);
    }

    private function removeSessionRedirectUrl()
    {
        $session = $this->container->get('session');
        $session->remove('redirect_url');
    }

    /**
     * Check if user password is expired.
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
     */
    private function checkIsPasswordExpired(User $user)
    {
        if ($user->isPasswordExpired()) {
            $this->get('session')->getFlashBag()->add('error', 'Your password has expired.');

            return $this->redirect($this->generateUrl('fos_user_change_password'));
        }

        return;
    }

    /**
     * Check if there are 'login as' user token in session then returns user for this token.
     * Otherwise get a user from the Token Storage.
     *
     * @param TokenStorage $tokenStorage
     *
     * @return \FOS\UserBundle\Model\UserInterface|mixed
     *
     * @see Controller\SecuredController::loginAs()
     */
    private function getCurrentUser(TokenStorage $tokenStorage = null)
    {
        if (null === $tokenStorage) {
            $tokenStorage = $this->get('security.token_storage');
        }

        $user = $this->getUser();

        $request = $this->get('request_stack')->getCurrentRequest();
        $session = $request->getSession();

        if ($session->has('rx_admin.login_as_token')) {
            $token = unserialize($session->get('rx_admin.login_as_token'));

            $userManager = $this->get('fos_user.user_manager');
            $user = $token->getUser();
            //$userManager->updateUser($token);

            $token->setUser($user);
            $tokenStorage->setToken($token);

            $session->remove('rx_admin.login_as_token');
        }

        return $user;
    }
}
