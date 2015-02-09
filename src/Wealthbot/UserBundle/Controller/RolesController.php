<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 12.09.12
 * Time: 16:06
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\UserBundle\Controller;

use FOS\UserBundle\Model\UserInterface;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\SecurityContextInterface;

class RolesController extends Controller
{
    public function afterLoginAction()
    {
        $loginManager = $this->container->get('wealthbot_user.subdomain_manager');
        if ($loginManager->hasSubDomain()) {
            return $this->redirect($loginManager->generateUrl('rx_after_login'));
        }

        $securityContext = $this->container->get('security.context');
        $user = $this->getCurrentUser($securityContext);

        if ($securityContext->isGranted('ROLE_ADMIN')) {
            $redirectUrl = $this->generateUrl('rx_admin_homepage');
        } else {
            $this->checkIsPasswordExpired($user);

            if (($securityContext->isGranted('ROLE_RIA') || $securityContext->isGranted('ROLE_RIA_USER'))) {

                $session = $this->get('session');
                if ($session->has('wealthbot.ria_view.client_id')) {
                    $redirectUrl = $this->generateUrl('rx_ria_dashboard_show_client', array(
                        'client_id' => $session->get('wealthbot.ria_view.client_id')
                    ));
                } else {
                    $redirectUrl = $this->getRouteForRia($user);
                }
            } elseif ($securityContext->isGranted('ROLE_CLIENT')) {
                $redirectUrl = $this->getRouteForClient($user);
            } elseif ($securityContext->isGranted('ROLE_SLAVE_CLIENT')) {
                $redirectUrl = $this->getSessionRedirectUrl();

                if ($redirectUrl) {
                    $this->removeSessionRedirectUrl();
                } else {
                    $redirectUrl = $this->generateUrl('rx_client_dashboard');
                }

            } else {
                $redirectUrl = $this->generateUrl('rx_user_homepage');
            }
        }

        return $this->redirect($redirectUrl);
    }

    /**
     * Get route for client user
     *
     * @param User $user
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

    /**
     * Get route for ria user
     *
     * @param User $user
     * @return string
     */
    protected function getRouteForRia(User $user)
    {
        $clientAclManager = $this->get('wealthbot_client.acl');
        if ($clientAclManager->isRiaClientView()) {
            return $this->getRouteForClient($clientAclManager->getClientForRiaClientView($user));
        }

        if (is_object($user->getProfile())) {
            $registrationStep = $user->getProfile()->getRegistrationStep();

            if ($registrationStep >= 5) {
                if ($user->getIsPasswordReset()) {
                    /** @var \Doctrine\Orm\EntityManager $em  */
                    $em = $this->container->get('doctrine.orm.entity_manager');
                    $user->setIsPasswordReset(false);
                    $em->persist($user);
                    $em->flush();

                    $redirectUrl = $this->generateUrl('rx_ria_profile');
                } else {
                    /** @var RiaCompanyInformation $companyInformation */
                    $companyInformation = $user->getRiaCompanyInformation();

                    if ($companyInformation && $companyInformation->getPortfolioModel() && !$companyInformation->getActivated()) {
                        $modelCompletion = $user->getRiaModelCompletion();

                        if ($companyInformation->getPortfolioModel()->isCustom()) {

                            if (!$modelCompletion || !$modelCompletion->getUsersAndUserGroups()) {
                                $redirectUrl = $this->generateUrl('rx_ria_user_management');

                            } elseif (!$modelCompletion->getSelectCustodians()) {
                                $redirectUrl = $this->generateUrl('rx_ria_change_profile_custodians');

                            } elseif (!$modelCompletion->getRebalancingSettings()) {
                                $redirectUrl = $this->generateUrl('rx_ria_change_profile_rebalancing');

                            } elseif (!$modelCompletion->getCreateSecurities()) {
                                $redirectUrl = $this->generateUrl('rx_ria_dashboard_models_tab', array('tab' => 'categories'));

                            } elseif (!$modelCompletion->getAssignSecurities()) {
                                $redirectUrl = $this->generateUrl('rx_ria_dashboard_models_tab', array('tab' => 'securities'));

                            } elseif (!$modelCompletion->getModelsCreated()) {
                                $redirectUrl = $this->generateUrl('rx_ria_dashboard_models_tab', array('tab' => 'models'));

                            } elseif (!$modelCompletion->getCustomizeProposals()) {
                                $redirectUrl = $this->generateUrl('rx_ria_risk_profiling');

                            } else {
                                $redirectUrl = $this->generateUrl('rx_ria_billing_tab', array('tab' => 'specs'));
                            }

                        } else {
                            if (!$modelCompletion || !$modelCompletion->getUsersAndUserGroups()) {
                                $redirectUrl = $this->generateUrl('rx_ria_user_management');

                            } elseif (!$modelCompletion->getSelectCustodians()) {
                                $redirectUrl = $this->generateUrl('rx_ria_change_profile_custodians');

                            } elseif (!$modelCompletion->getRebalancingSettings()) {
                                $redirectUrl = $this->generateUrl('rx_ria_change_profile_rebalancing');

                            } elseif (!$modelCompletion->getCustomizeProposals()) {
                                $redirectUrl = $this->generateUrl('rx_ria_risk_profiling');

                            } else {
                                $redirectUrl = $this->generateUrl('rx_ria_billing_tab', array('tab' => 'specs'));
                            }
                        }
                    } else {
                        $redirectUrl = $this->generateUrl('rx_ria_dashboard');
                    }
                }

                return $redirectUrl;
            }
        }

        return $this->generateUrl('rx_ria_company_profile');
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

//    /**
//     * Get route by client user questionnaire step
//     *
//     * @param User $client
//     * @return null|string
//     */
//    //TODO: is it necessary?
//    protected function getRouteByClientQuestionnaireStep(User $client)
//    {
//        $step = $client->getProfile()->getQuestionnaireStep();
//
//        /** @var \Doctrine\Orm\EntityManager $em  */
//        $em = $this->container->get('doctrine.orm.entity_manager');
//
//        if ($step) {
//            $question = $em->getRepository('WealthbotRiaBundle:RiskQuestion')->getOneQuestionOrderedBySequence($step);
//
//            if ($question) {
//                return $this->generateUrl('rx_client_profile_step_two', array('question_nb' => $step));
//            }
//        } else {
//            return $this->generateUrl('rx_client_profile_step_two');
//        }
//
//        return null;
//    }

    /**
     * Check if user password is expired
     *
     * @param User $user
     * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function checkIsPasswordExpired(User $user)
    {
        if ($user->isPasswordExpired()) {
            $this->get('session')->setFlash('error', 'Your password has expired.');

            return $this->redirect($this->generateUrl("fos_user_change_password"));
        }

        return null;
    }

    /**
     * Check if there are 'login as' user token in session then returns user for this token.
     * Otherwise get a user from the Security Context.
     *
     * @param SecurityContextInterface $securityContext
     * @return \FOS\UserBundle\Model\UserInterface|mixed
     *
     * @see Wealthbot\AdminBundle\Controller\SecuredController::loginAsAction()
     */
    private function getCurrentUser(SecurityContextInterface $securityContext = null)
    {
        if (null === $securityContext) {
            $securityContext = $this->get('security.context');
        }

        $user = $this->getUser();

        $request = $this->get('request');
        $session = $request->getSession();

        if ($session->has('rx_admin.login_as_token')) {
            $token = unserialize($session->get('rx_admin.login_as_token'));

            $userManager = $this->get('fos_user.user_manager');
            $user = $userManager->refreshUser($token->getUser());
            $userManager->updateUser($user);

            $token->setUser($user);
            $securityContext->setToken($token);

            $session->remove('rx_admin.login_as_token');
        }

        return $user;
    }
}
