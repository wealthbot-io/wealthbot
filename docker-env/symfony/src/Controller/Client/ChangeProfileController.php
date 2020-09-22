<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.09.12
 * Time: 14:04
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Client;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Event\AdminEvents;
use App\Event\UserHistoryEvent;
use App\Entity\AccountGroup;
use App\Entity\ClientPortfolio;
use App\Form\Handler\ClientChangeProfileTransferPersonalFormHandler;
use App\Form\Type\ClientChangeProfileTransferPersonalFormType;
use App\Form\Type\ClientQuestionsFormType;
use App\Form\Type\SlaveClientFormType;
use App\Manager\BreadcrumbsManager;
use App\Manager\ClientPortfolioManager;
use App\Model\UserAccountOwnerAdapter;
use App\Form\Type\ChooseClientPortfolioFormType;
use App\Entity\User;

class ChangeProfileController extends Controller
{
    use AclController;

    public function index(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        //  $dm = $this->get('doctrine.odm.mongodb.document_manager');
        /** @var ClientPortfolioManager $clientPortfolioManager */
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        /** @var User $user */
        $user = $this->getUser();

        //// $tempPortfolios = $dm->getRepository('App\Entity\TempPortfolio')->findBy(['clientUserId' => $user->getId()]);

        $riskToleranceForm = $this->createForm(ClientQuestionsFormType::class, null, ['em'=>$em, 'user'=>$user]);
        $updatePasswordForm = $this->createForm(\App\Form\Type\UpdatePasswordFormType::class, $user, [

        ]);
        $manageUserForm = $this->createForm(\App\Form\Type\SlaveClientFormType::class);
        $clients = $em->getRepository('App\Entity\User')->getClientsByMasterClientId($user->getId());
        $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($user);

        $chooseClientPortfolioForm = $this->createForm(ChooseClientPortfolioFormType::class, $user);

        $parameters = [
            'risk_tolerance_form' => $riskToleranceForm->createView(),
            'update_password_form' => $updatePasswordForm->createView(),
            'manage_user_form' => $manageUserForm->createView(),
            'layout_variables' => $this->getLayoutVariables('Overview', 'rx_client_dashboard'),
            'active_tab' => $request->get('tab'),
            'is_ajax' => $request->isXmlHttpRequest(),
            'is_ria_client_view' => $this->isRiaClientView(),
            'temp_portfolios' => [],
            'client' => $user,
            'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
            'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($user),
            'clients' => $clients,
            'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getPortfolio() : null,
        ];

        $partial = $this->renderView('/Client/ChangeProfile/index.html.twig', $parameters);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $partial,
            ]);
        }

        //return $this->render('/Client/ChangeProfile/index.html.twig', $parameters);

        return $this->redirect(
            $this->generateUrl(
                'rx_client_dashboard_account_management',
                ['active_tab' => $parameters['active_tab']]
            )
        );
    }

    public function information(Request $request)
    {

        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $this->getUser();

        $accountUserAdapter = new UserAccountOwnerAdapter($user);



        $form = $this->createForm(ClientChangeProfileTransferPersonalFormType::class, $accountUserAdapter, [
            'em' => $em,
            'owner' => $user,
            'isPreSaved' => true,
            'class' => 'App\Entity\User'
        ]);
        



        if ($request->isMethod('post')) {
            $formHandler = new ClientChangeProfileTransferPersonalFormHandler($form, $request, $em);

            if ($formHandler->process()) {
                $this->get('session')->getFlashBag()->add('success', 'Information successfully updated.');

                $this->dispatchHistoryEvent($user, 'Updated personal information');

                return $this->json([
                    'status' => 'success',
                    'form' => $this->renderView(
                        '/Client/ChangeProfile/information.html.twig',
                        [
                        'form' => $form->createView(),
                    ]
                    ),
                ]);
            }

            return $this->json([
                'status' => 'error',
                'form' => $this->renderView(
                    '/Client/ChangeProfile/information.html.twig',
                    [
                    'form' => $form->createView(),
                ]
                ),
            ]);
        }
        return $this->render(
            '/Client/ChangeProfile/information.html.twig',
            ['form' => $form->createView()]
        );
    }

    public function portfolio(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        $accountsRepo = $em->getRepository('App\Entity\ClientAccount');

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

        $layout = '/Client/ChangeProfile/portfolio.html.twig';
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
        if ('json' === $dataType) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView($layout, $params),
            ]);
        }

        return $this->render($layout, $params);
    }

    public function updatePassword()
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

        return $this->render('/Client/ChangeProfile/_update_password.html.twig', ['form' => $form->createView()]);
    }

    public function manageUsers()
    {
        $user = $this->getUser();
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->get('wealthbot_client.slave_client.form');
        $formHandler = $this->get('wealthbot_client.slave_client.form.handler');

        $clients = $em->getRepository('App\Entity\User')->getClientsByMasterClientId($user->getId());

        $process = $formHandler->process($user);
        if ($process) {
            $this->dispatchHistoryEvent($user, 'Created new user');

            $this->get('session')->getFlashBag()->add('success', 'User was created successfully.');
            $form = $this->get('wealthbot_client.slave_client.form');

            return $this->redirect($this->generateUrl('rx_client_change_profile_manage_users'));
        }

        return $this->render(
            '/Client/ChangeProfile/_manage_users.html.twig',
            [
                'form' => $form->createView(),
                'clients' => $clients,
            ]
        );
    }

    public function editUser(Request $request)
    {
        $user = $this->getUser();
        $em = $this->get('doctrine.orm.entity_manager');

        $clients = $em->getRepository('App\Entity\User')->getClientsByMasterClientId($user->getId());
        $client = $em->getRepository('App\Entity\User')
            ->getClientByIdAndMasterClientId($request->get('client_id'), $user->getId());

        $form = $this->createForm(SlaveClientFormType::class, $client);

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

        return $this->render(
            '/Client/ChangeProfile/edit_user.html.twig',
            [
                'form' => $form->createView(),
                'clients' => $clients,
                'client' => $client,
            ]
        );
    }

    public function deleteUser($client_id)
    {
        $masterClient = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $client = $em->getRepository('App\Entity\User')
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

    public function submitAnotherPortfolio(Request $request)
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

        $chooseClientPortfolioForm = $this->createForm(ChooseClientPortfolioFormType::class, $client, ['proposed_portfolio' => $clientPortfolioManager->getProposedClientPortfolio($client)]);

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

        $tempPortfolios = $dm->getRepository('App\Entity\TempPortfolio')->findBy(['clientUserId' => $client->getId()]);

        $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);

        return $this->render('/Client/ChangeProfile/_your_portfolio.html.twig', [
            'is_ria_client_view' => $this->isRiaClientView(),
            'temp_portfolios' => $tempPortfolios,
            'client' => $client,
            'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
            'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($client),
            'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getPortfolio() : null,
        ]);
    }

    public function tempPortfolioRebalance(Request $request)
    {
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $modelManager = $this->get('wealthbot_admin.ce_model_manager');
        /** @var ClientPortfolioManager $clientPortfolioManager */
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $client = $this->getUser();

        /** @var TempPortfolio $tempPortfolio */
        $tempPortfolio = $dm->getRepository('App\Entity\TempPortfolio')->findOneBy([
            'id' => $request->get('id'),
            'clientUserId' => $client->getId(),
        ]);

        if (!$tempPortfolio) {
            return $this->json([
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
            return $this->json([
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

        $tempPortfolios = $dm->getRepository('App\Entity\TempPortfolio')->findBy(['clientUserId' => $client->getId()]);
        $chooseClientPortfolioForm = $this->createForm(ChooseClientPortfolioFormType::class, $client, ['proposed_portfolio'=>$clientPortfolioManager->getProposedClientPortfolio($client)]);

        $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/ChangeProfile/_your_portfolio.html.twig', [
                'is_ria_client_view' => $this->isRiaClientView(),
                'temp_portfolios' => $tempPortfolios,
                'client' => $client,
                'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
                'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($client),
                'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getPortfolio() : null,
            ]),
        ]);
    }

    public function showClientPortfolio(Request $request)
    {
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $client = $this->getUser();
        $clientPortfolio = $clientPortfolioManager->find($request->get('id'));

        if (!$clientPortfolio || ($clientPortfolio->getClient()->getId() !== $client->getId())) {
            throw $this->createNotFoundException('Client Portfolio does not exist');
        }

        $tempPortfolios = $dm->getRepository('App\Entity\TempPortfolio')->findBy(['clientUserId' => $client->getId()]);
        $chooseClientPortfolioForm = $this->createForm(ChooseClientPortfolioFormType::class, $client, ['proposed_portfolio'=>$clientPortfolioManager->getProposedClientPortfolio($client)]);

        $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/ChangeProfile/_your_portfolio.html.twig', [
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

    public function approveAnotherPortfolio(Request $request)
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
            $tempPortfolios = $dm->getRepository('App\Entity\TempPortfolio')->findBy(['clientUserId' => $client->getId()]);
            $chooseClientPortfolioForm = $this->createForm(ChooseClientPortfolioFormType::class, $client, ['proposed_portfolio'=> $clientPortfolioManager->getProposedClientPortfolio($client)]);

            $suggestedClientPortfolio = $clientPortfolioManager->getApprovedClientPortfolio($client);

            return $this->render('/Client/ChangeProfile/_your_portfolio.html.twig', [
                    'is_ria_client_view' => $this->isRiaClientView(),
                    'temp_portfolios' => $tempPortfolios,
                    'client' => $client,
                    'choose_client_portfolio_form' => $chooseClientPortfolioForm->createView(),
                    'client_portfolio_history' => $clientPortfolioManager->getNotActivePortfolios($client),
                    'client_portfolio_id' => $clientPortfolio->getId(),
                    'suggested_portfolio' => $suggestedClientPortfolio ? $suggestedClientPortfolio->getProfile() : null,
            ]);
        }

        $accountsRepo = $em->getRepository('App\Entity\ClientAccount');
        $clientAccounts = $accountsRepo->findConsolidatedAccountsByClientId($client->getId());
        $retirementAccounts = $accountsRepo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);
        $form = $this->createFormBuilder()->add('name', 'text')->getForm();

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/Dashboard/approve_portfolio.html.twig', [
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
