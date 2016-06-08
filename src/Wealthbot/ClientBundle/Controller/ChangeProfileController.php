<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.09.12
 * Time: 14:04
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\AdminEvents;
use Wealthbot\AdminBundle\Event\UserHistoryEvent;
use Wealthbot\ClientBundle\Document\TempPortfolio;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\ClientBundle\Form\Handler\ClientChangeProfileTransferPersonalFormHandler;
use Wealthbot\ClientBundle\Form\Type\ClientChangeProfileTransferPersonalFormType;
use Wealthbot\ClientBundle\Form\Type\ClientQuestionsFormType;
use Wealthbot\ClientBundle\Form\Type\SlaveClientFormType;
use Wealthbot\ClientBundle\Manager\BreadcrumbsManager;
use Wealthbot\ClientBundle\Manager\ClientPortfolioManager;
use Wealthbot\ClientBundle\Model\UserAccountOwnerAdapter;
use Wealthbot\RiaBundle\Form\Type\ChooseClientPortfolioFormType;
use Wealthbot\UserBundle\Entity\User;

class ChangeProfileController extends Controller
{
    use AclController;

    public function indexAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        /** @var ClientPortfolioManager $clientPortfolioManager */
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        /** @var User $user */
        $user = $this->getUser();

        $tempPortfolios = $dm->getRepository('WealthbotClientBundle:TempPortfolio')->findBy(['clientUserId' => $user->getId()]);

        $riskToleranceForm = $this->createForm(new ClientQuestionsFormType($em, $user));
        $updatePasswordForm = $this->get('wealthbot_user.update_password.form');
        $manageUserForm = $this->get('wealthbot_client.slave_client.form');
        $clients = $em->getRepository('WealthbotUserBundle:User')->getClientsByMasterClientId($user->getId());
        $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($user);

        $chooseClientPortfolioForm = $this->createForm(new ChooseClientPortfolioFormType($suggestedClientPortfolio), $user);

        $parameters = [
            'risk_tolerance_form' => $riskToleranceForm->createView(),
            'update_password_form' => $updatePasswordForm->createView(),
            'manage_user_form' => $manageUserForm->createView(),
            'layout_variables' => $this->getLayoutVariables('Overview', 'rx_client_dashboard'),
            'active_tab' => $request->get('tab'),
            'is_ajax' => $request->isXmlHttpRequest(),
            'is_ria_client_view' => $this->isRiaClientView(),
            'temp_portfolios' => $tempPortfolios,
            'client' => $user,
            'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
            'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($user),
            'clients' => $clients,
            'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getPortfolio() : null,
        ];

        $partial = $this->renderView('WealthbotClientBundle:ChangeProfile:index.html.twig', $parameters);

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $partial,
            ]);
        }

        //return $this->render('WealthbotClientBundle:ChangeProfile:index.html.twig', $parameters);

        return $this->redirect(
            $this->generateUrl(
                'rx_client_dashboard_account_management',
                ['active_tab' => $parameters['active_tab']]
            )
        );
    }

    public function informationAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $this->getUser();

        $accountUserAdapter = new UserAccountOwnerAdapter($user);

        $form = $this->createForm(new ClientChangeProfileTransferPersonalFormType($em, $accountUserAdapter), $accountUserAdapter);
        if ($request->isMethod('post')) {
            $formHandler = new ClientChangeProfileTransferPersonalFormHandler($form, $request, $em);

            if ($formHandler->process()) {
                $this->get('session')->getFlashBag()->add('success', 'Information successfully updated.');

                $this->dispatchHistoryEvent($user, 'Updated personal information');

                return $this->getJsonResponse([
                    'status' => 'success',
                    'form' => $this->renderView(
                        'WealthbotClientBundle:ChangeProfile:information.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ]);
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'form' => $this->renderView(
                    'WealthbotClientBundle:ChangeProfile:information.html.twig', [
                    'form' => $form->createView(),
                ]),
            ]);
        }

        return $this->render(
            'WealthbotClientBundle:ChangeProfile:information.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function portfolioAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        $accountsRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        $clientPortfolioId = $request->get('client_portfolio_id');

        /** @var $client User */
        $client = $this->getUser();
        $ria = $client->getRia();

        $companyInformation = $ria->getRiaCompanyInformation();
        $portfolioInformationManager = $this->get('wealthbot_client.portfolio_information_manager');
        $clientAccounts = $accountsRepo->findConsolidatedAccountsByClientId($client->getId());
        $retirementAccounts = $accountsRepo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);

        $clientPortfolio = $clientPortfolioId ? $clientPortfolioManager->find($clientPortfolioId) : null;

        if (!$clientPortfolio || $clientPortfolio->getClient() !== $client) {
            $clientPortfolio = $clientPortfolioManager->getCurrentPortfolio($client);
        }

        $portfolio = $clientPortfolio ? $clientPortfolio->getPortfolio() : null;
        $portfolioInformation = $portfolio ? $portfolioInformationManager->getPortfolioInformation($client, $portfolio) : null;

        $layout = 'WealthbotClientBundle:ChangeProfile:portfolio.html.twig';
        $params = [
            'client' => $client,
            'client_accounts' => $clientAccounts,
            'total' => $accountsRepo->getTotalScoreByClientId($client->getId()),
            'ria_company_information' => $companyInformation,
            'has_retirement_account' => count($retirementAccounts) ? true : false,
            'portfolio_information' => $portfolioInformation,
            'show_sas_cash' => $accountsRepo->containsSasCash($clientAccounts),
            'action' => 'client_portfolio',
        ];

        $dataType = $request->get('data_type', 'html');
        if ($dataType === 'json') {
            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $this->renderView($layout, $params),
            ]);
        }

        return $this->render($layout, $params);
    }

    public function updatePasswordAction()
    {
        $user = $this->getOriginalUser();

        $form = $this->get('wealthbot_user.update_password.form');
        $formHandler = $this->get('wealthbot_user.update_password.form.handler');

        $process = $formHandler->process($user);
        if ($process) {
            $this->dispatchHistoryEvent($user, 'Updated password');
            $this->get('session')->getFlashBag()->add('success', 'Password successfully updated.');

            $this->get('wealthbot.mailer')->sendClientResetPasswordEmail($user);

            return $this->redirect($this->generateUrl('rx_client_change_profile_update_password'));
        }

        return $this->render('WealthbotClientBundle:ChangeProfile:_update_password.html.twig', ['form' => $form->createView()]);
    }

    public function manageUsersAction()
    {
        $user = $this->getUser();
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->get('wealthbot_client.slave_client.form');
        $formHandler = $this->get('wealthbot_client.slave_client.form.handler');

        $clients = $em->getRepository('WealthbotUserBundle:User')->getClientsByMasterClientId($user->getId());

        $process = $formHandler->process($user);
        if ($process) {
            $this->dispatchHistoryEvent($user, 'Created new user');

            $this->get('session')->getFlashBag()->add('success', 'User was created successfully.');
            $form = $this->get('wealthbot_client.slave_client.form');

            return $this->redirect($this->generateUrl('rx_client_change_profile_manage_users'));
        }

        return $this->render('WealthbotClientBundle:ChangeProfile:_manage_users.html.twig',
            [
                'form' => $form->createView(),
                'clients' => $clients,
            ]
        );
    }

    public function editUserAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->get('doctrine.orm.entity_manager');

        $clients = $em->getRepository('WealthbotUserBundle:User')->getClientsByMasterClientId($user->getId());
        $client = $em->getRepository('WealthbotUserBundle:User')
            ->getClientByIdAndMasterClientId($request->get('client_id'), $user->getId());

        $form = $this->createForm(new SlaveClientFormType(), $client);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $client = $form->getData();

                $em->persist($client);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'User was edited successfully.');

                return $this->redirect($this->generateUrl('rx_client_change_profile_manage_users'));
            }
        }

        return $this->render('WealthbotClientBundle:ChangeProfile:edit_user.html.twig',
            [
                'form' => $form->createView(),
                'clients' => $clients,
                'client' => $client,
            ]
        );
    }

    public function deleteUserAction($client_id)
    {
        $masterClient = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $client = $em->getRepository('WealthbotUserBundle:User')
            ->getClientByIdAndMasterClientId($client_id, $masterClient->getId());

        if (!$client) {
            $this->get('session')->getFlashBag()->add('error', 'User was not found');
        } else {
            $em->remove($client);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'User deleted successfully');
        }

        return $this->redirect($this->generateUrl('rx_client_change_profile_manage_users'));
    }

    public function submitAnotherPortfolioAction(Request $request)
    {
        if (!$this->isRiaClientView() || !$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
//        $modelManager = $this->get('wealthbot_admin.ce_model_manager');
        /** @var ClientPortfolioManager $clientPortfolioManager */
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        /** @var User $client */
        $client = $this->getUser();

        $chooseClientPortfolioForm = $this->createForm(new ChooseClientPortfolioFormType($clientPortfolioManager->getProposedClientPortfolio($client)), $client);

        $chooseClientPortfolioForm->handleRequest($request);
        if ($chooseClientPortfolioForm->isValid()) {
            /** @var ClientPortfolio $newPortfolioData */
            $newPortfolio = $chooseClientPortfolioForm->get('portfolio')->getData();

            $approvedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);
            if ($approvedClientPortfolio) {
                $approvedClientPortfolio->setPortfolio($newPortfolio);
                $em->persist($approvedClientPortfolio);
                $em->flush();
            } else {
                $clientPortfolioManager->proposePortfolio($client, $newPortfolio);
                $clientPortfolioManager->approveProposedPortfolio($client);
            }
        }

        $tempPortfolios = $dm->getRepository('WealthbotClientBundle:TempPortfolio')->findBy(['clientUserId' => $client->getId()]);

        $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);

        return $this->render('WealthbotClientBundle:ChangeProfile:_your_portfolio.html.twig', [
            'is_ria_client_view' => $this->isRiaClientView(),
            'temp_portfolios' => $tempPortfolios,
            'client' => $client,
            'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
            'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($client),
            'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getPortfolio() : null,
        ]);
    }

    public function tempPortfolioRebalanceAction(Request $request)
    {
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');
        /** @var ClientPortfolioManager $clientPortfolioManager */
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $client = $this->getUser();

        /** @var TempPortfolio $tempPortfolio */
        $tempPortfolio = $dm->getRepository('WealthbotClientBundle:TempPortfolio')->findOneBy([
            'id' => $request->get('id'),
            'clientUserId' => $client->getId(),
        ]);

        if (!$tempPortfolio) {
            return $this->getJsonResponse([
                'status' => 'error',
                'content' => 'You have not temp portfolio with id: '.$request->get('id'),
            ]);
        }

        $model = $modelManager->findCeModelBy([
            'id' => $tempPortfolio->getModelId(),
            'isDeleted' => false,
            'ownerId' => $client->getRia()->getId(),
        ]);

        if (!$model) {
            return $this->getJsonResponse([
                'status' => 'error',
                'content' => 'You have not model portfolio with id: '.$tempPortfolio->getModelId(),
            ]);
        }

        //$modelManager->setClientPortfolio($model, $client, $clientPortfolioManager);

        $clientPortfolioManager->proposePortfolio($client, $model);
        $clientPortfolioManager->approveProposedPortfolio($client);
        $clientPortfolioManager->acceptApprovedPortfolio($client);

        $dm->remove($tempPortfolio);
        $dm->flush();

        $tempPortfolios = $dm->getRepository('WealthbotClientBundle:TempPortfolio')->findBy(['clientUserId' => $client->getId()]);
        $chooseClientPortfolioForm = $this->createForm(new ChooseClientPortfolioFormType($clientPortfolioManager->getProposedClientPortfolio($client)), $client);

        $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:ChangeProfile:_your_portfolio.html.twig', [
                'is_ria_client_view' => $this->isRiaClientView(),
                'temp_portfolios' => $tempPortfolios,
                'client' => $client,
                'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
                'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($client),
                'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getPortfolio() : null,
            ]),
        ]);
    }

    public function showClientPortfolioAction(Request $request)
    {
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $client = $this->getUser();
        $clientPortfolio = $clientPortfolioManager->find($request->get('id'));

        if (!$clientPortfolio || ($clientPortfolio->getClient()->getId() !== $client->getId())) {
            throw $this->createNotFoundException('Client Portfolio does not exist');
        }

        $tempPortfolios = $dm->getRepository('WealthbotClientBundle:TempPortfolio')->findBy(['clientUserId' => $client->getId()]);
        $chooseClientPortfolioForm = $this->createForm(new ChooseClientPortfolioFormType($clientPortfolioManager->getProposedClientPortfolio($client)), $client);

        $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:ChangeProfile:_your_portfolio.html.twig', [
                'is_ria_client_view' => $this->isRiaClientView(),
                'temp_portfolios' => $tempPortfolios,
                'client' => $client,
                'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
                'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($client),
                'client_portfolio_id' => $clientPortfolio->getId(),
                'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getPortfolio() : null,
            ]),
        ]);
    }

    public function approveAnotherPortfolioAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        $portfolioInformationManager = $this->get('wealthbot_client.portfolio_information_manager');

        /** @var User $client */
        $client = $this->getUser();

        $clientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);
        if (!$clientPortfolio) {
            throw $this->createNotFoundException();
        }

        /** @var User $ria */
        $ria = $client->getRia();
        $companyInformation = $ria->getRiaCompanyInformation();

        $isUseQualified = $companyInformation->getIsUseQualifiedModels();
        $isQualified = false;

        if ($isUseQualified) {
            $isQualified = (bool) $request->get('is_qualified');
        }

        $portfolio = $clientPortfolio->getPortfolio();
        $portfolioInformation = $portfolioInformationManager->getPortfolioInformation($client, $portfolio, $isQualified);

        if ($request->isMethod('post')) {
            //$modelManager->setClientPortfolio($client->getProfile()->getSuggestedPortfolio(), $client, $clientPortfolioManager, $clientPortfolio);
            $clientPortfolioManager->acceptApprovedPortfolio($client);

            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $tempPortfolios = $dm->getRepository('WealthbotClientBundle:TempPortfolio')->findBy(['clientUserId' => $client->getId()]);
            $chooseClientPortfolioForm = $this->createForm(new ChooseClientPortfolioFormType($clientPortfolioManager->getProposedClientPortfolio($client)), $client);

            $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);

            return $this->render('WealthbotClientBundle:ChangeProfile:_your_portfolio.html.twig', [
                    'is_ria_client_view' => $this->isRiaClientView(),
                    'temp_portfolios' => $tempPortfolios,
                    'client' => $client,
                    'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
                    'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($client),
                    'client_portfolio_id' => $clientPortfolio->getId(),
                    'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getProfile() : null,
            ]);
        }

        $accountsRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');
        $clientAccounts = $accountsRepo->findConsolidatedAccountsByClientId($client->getId());
        $retirementAccounts = $accountsRepo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);
        $form = $this->createFormBuilder()->add('name', 'text')->getForm();

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:approve_portfolio.html.twig', [
                'client' => $client,
                'client_accounts' => $clientAccounts,
                'total' => $accountsRepo->getTotalScoreByClientId($client->getId()),
                'ria_company_information' => $companyInformation,
                'has_retirement_account' => count($retirementAccounts) ? true : false,
                'portfolio_information' => $portfolioInformation,
                'show_sas_cash' => $accountsRepo->containsSasCash($clientAccounts),
                'is_approved' => $clientPortfolio->isClientAccepted(),
                'is_use_qualified_models' => $isUseQualified,
                'form' => $form->createView(),
                'signing_date' => new \DateTime('now'),
                'action' => 'client_approve_portfolio',
                'approve_url' => 'rx_client_change_profile_approve_another_portfolio',
            ]),
        ]);
    }

    private function getLayoutVariables($action, $url)
    {
        /** @var $breadcrumbsManager BreadcrumbsManager */
        $breadcrumbsManager = $this->get('wealthbot_client.breadcrumbs_manager');
        $breadcrumbsManager->addCrumb($action, $url);

        $variables = [
            'breadcrumbs' => $breadcrumbsManager->getBreadcrumbs(),
            'action' => $action,
            'ria_logo' => $this->get('router')->generate('rx_file_download', ['ria_id' => $this->getUser()->getRia()->getId()], true),
        ];

        return $variables;
    }

    private function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }

    /**
     * Dispatch new UserHistoryEvent event.
     *
     * @param User $user
     * @param $description
     */
    private function dispatchHistoryEvent(User $user, $description)
    {
        $event = new UserHistoryEvent($user, $description);
        $this->get('event_dispatcher')->dispatch(AdminEvents::USER_HISTORY, $event);
    }
}
