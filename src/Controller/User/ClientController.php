<?php

namespace App\Controller\User;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use App\Entity\ClientSettings;
use App\Entity\Document;
use App\Entity\Group;
use App\Entity\Profile;
use App\Entity\User;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class ClientController extends Controller
{
    public function registration(Request $request)
    {
        if ($this->getUser()) {
            $redirectUrl = $this->redirectIfUserExist($this->getUser());
            if ($redirectUrl) {
                return $redirectUrl;
            }
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $subDomainManager = $this->container->get('wealthbot_user.subdomain_manager');
        $repository = $em->getRepository('App\Entity\User');
        $group = null;

        if ($subDomainManager->hasSubDomain()) {
            $riaCompanyInformation = $subDomainManager->getRiaCompanyInformation();
            if ($riaCompanyInformation) {
                $ria = $riaCompanyInformation->getRia();
            }
        } else {
            $ria = $repository->find(5);
        }

        if (!$ria || !$ria->hasRole('ROLE_RIA')) {
            throw $this->createNotFoundException('Ria user does not exist.');
        }

        if ($request->get('group')) {
            $group = $em->getRepository('App\Entity\Group')->findOneBy([
                'owner' => $ria,
                'name' => $request->get('group'),
            ]);
        }

        $form = $this->container->get('wealthbot_user.registration.client.form');
        $formHandler = $this->container->get('wealthbot_user.registration.client.form.handler');

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user = $formHandler->process();
                if ($user) {
                    $profile = $user->getProfile();
                    $profile->setRia($ria);
                    $profile->setClientStatus(Profile::CLIENT_STATUS_PROSPECT);

                    if (null === $group) {
                        $group = $em->getRepository('App\Entity\Group')->findOneBy(['name' => Group::GROUP_NAME_ALL]);
                    }

                    $user->addGroup($group);

                    $clientSettings = new ClientSettings();
                    $user->setClientSettings($clientSettings);
                    $clientSettings->setClient($user);

                    $billingSpec = $em->getRepository('App\Entity\BillingSpec')->findOneBy(['master' => true, 'owner' => $ria]);
                    $user->setAppointedBillingSpec($billingSpec);

                    $this->get('wealthbot.manager.user')->updateUser($user);
                    //$this->get('wealthbot.mailer')->sendAdvCopyEmailMessage($user); // @todo: fix this //

                    if ($subDomainManager->hasSubDomain()) {
                        $url = $subDomainManager->generateUrl('rx_client_profile_step_one');
                    } else {
                        $url = $this->generateUrl('rx_client_profile_step_one');
                    }

                    $response = $this->redirect($url);
                    $this->authenticateUser($user, $request);

                    return $response;
                }
            }
        }

        $adminId = $this->get('wealthbot.manager.user')->getAdmin()->getId();
        $documentManager = $this->get('wealthbot_user.document_manager');

        $documents = [
            'admin_privacy_policy' => $documentManager->getUserDocumentLinkByType($adminId, Document::TYPE_PRIVACY_POLICY),
            'admin_user_agreements' => $documentManager->getUserDocumentLinkByType($adminId, Document::TYPE_USER_AGREEMENT),
            'ria_adv' => $documentManager->getUserDocumentLinkByType($ria->getId(), Document::TYPE_ADV),
        ];

        return $this->render('/User/Client/registration.html.twig', [
            'form' => $form->createView(),
            'ria' => $ria,
            'documents' => $documents,
            'group' => $group ? $group->getName() : null,
        ]);
    }

    public function finishRegistration(Request $request)
    {
        $user = $this->getUser();

        if (!$user || !$user->hasRole('ROLE_CLIENT')) {
            throw $this->createNotFoundException('Client does not exist.');
        }

        if ($request->isMethod('post')) {
            $user->getProfile()->setRegistrationStep(3);

            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();

            $this->setFlash('success', 'Client user successfully registered!');
        }

        $portfolio = \App\RiskManagement\BaselinePortfolio::$models[$user->getProfile()->getSuggestedPortfolio()];

        return $this->render('/User/Client/portfolios.html.twig', ['portfolio' => $portfolio]);
    }

    protected function redirectIfUserExist($user)
    {
        if (is_object($user->getProfile())) {
            $registrationStep = $user->getProfile()->getRegistrationStep();

            switch ($registrationStep) {
                case 1:
                    return $this->redirect($this->generateUrl('rx_client_profile_step_two'));
                    break;
                case 2:
                    return $this->redirect($this->generateUrl('rx_client_profile_step_three'));
                    break;
                case 3:
                    return $this->redirect($this->generateUrl('rx_client_finish_registration'));
                    break;
            }
        }

        return $this->redirect($this->generateUrl('rx_client_profile_step_one'));
    }

    /**
     * Authenticate a user with Symfony Security.
     *
     * @param \FOS\UserBundle\Model\UserInterface        $user
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    protected function authenticateUser(UserInterface $user, Request $request)
    {
        try {
            $token = new UsernamePasswordToken($user, null, $this->container->getParameter('fos_user.firewall_name'), $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

            // Fire the login event manually
            $event = new InteractiveLoginEvent($request, $token);
            $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
        } catch (AccountStatusException $ex) {
            // We simply do not authenticate users which do not pass the user
            // checker (not enabled, expired, etc.).
        }
    }

    protected function setFlash($action, $value)
    {
        $this->container->get('session')->getFlashBag()->add($action, $value);
    }
}
