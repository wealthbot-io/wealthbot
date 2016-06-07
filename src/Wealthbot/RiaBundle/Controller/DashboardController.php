<?php

namespace Wealthbot\RiaBundle\Controller;

use Doctrine\ORM\EntityManager;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Repository\AssetClassRepository;
use Wealthbot\AdminBundle\Repository\CeModelRepository;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\Distribution;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Model\Acl;
use Wealthbot\RiaBundle\Entity\RiaDashboardBox;
use Wealthbot\RiaBundle\Entity\RiaModelCompletion;
use Wealthbot\RiaBundle\Form\Type\AccountSettingsFormType;
use Wealthbot\RiaBundle\Form\Type\HouseholdBillingSettingsFormType;
use Wealthbot\RiaBundle\Form\Type\HouseholdCloseFormType;
use Wealthbot\RiaBundle\Form\Type\HouseholdContactSettingsFormType;
use Wealthbot\RiaBundle\Form\Type\HouseholdPersonalSettingsFormType;
use Wealthbot\RiaBundle\Form\Type\HouseholdPortfolioSettingsFormType;
use Wealthbot\RiaBundle\Form\Type\HouseholdSpouseFormType;
use Wealthbot\RiaBundle\Form\Type\InviteProspectFormType;
use Wealthbot\RiaBundle\Form\Type\OneTimeDistributionFormType;
use Wealthbot\RiaBundle\Form\Type\RiaModelCompletionFormType;
use Wealthbot\RiaBundle\Form\Type\RiaSearchClientsFormType;
use Wealthbot\RiaBundle\Form\Type\ScheduledDistributionFormType;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Entity\User;

class DashboardController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $workflowRepository = $em->getRepository('WealthbotClientBundle:Workflow');
        $userRepository = $em->getRepository('WealthbotUserBundle:User');
        $activityManager = $this->get('wealthbot.activity.manager');

        $user = $this->getUser();
        $clients = $userRepository->findClientsByRiaId($user->getId());

        $paginator = $this->get('knp_paginator');
        $recentActivityPagination = $paginator->paginate($activityManager->findByRiaQuery($user), 1, 10);

        if ($request->isXmlHttpRequest() && $request->get('block') === 'most_recent_activity') {
            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $this->renderView('WealthbotRiaBundle:Workflow:_workflow_activity_list.html.twig', [
                    'pagination' => $recentActivityPagination,
                    'show_pagination' => false,
                ]),
            ]);
        }

        $riaDashboardBoxes = $em->getRepository('WealthbotRiaBundle:RiaDashboardBox')->findBy([
            'ria_user_id' => $this->getUser()->getId(),
        ]);

        $blocksSequence = [];
        foreach ($riaDashboardBoxes as $riaDashboardBox) {
            /* @var RiaDashboardBox $riaDashboardBox */
            $blocksSequence[] = [
                'template' => $riaDashboardBox->getTemplate(),
                'sequence' => $riaDashboardBox->getSequence(),
            ];
        }

        $prospects = $userRepository->findOrderedProspectsByRia($user);
        $notApprovedPortfolios = $userRepository->findClientsWithNotApprovedPortfolioByRiaId($user->getId());

        $portfoliosCount = [
            'prospects' => count($prospects),
            'suggested_portfolios' => count($notApprovedPortfolios),
            'initial_rebalance' => $workflowRepository->getInitialRebalanceCountByRia($user),
        ];

        $securitiesStatistic = [
            ['label' => 'Vanguard Total Stock Market', 'data' => 50000000],
            ['label' => 'iShares Total Bond', 'data' => 40000000],
            ['label' => 'DFA Large Cap Value', 'data' => 20000000],
            ['label' => 'American Funds Growth Fund', 'data' => 10000000],
            ['label' => 'Vanguard Intermediate Bond', 'data' => 10000000],
        ];

        /*$firmMetrics = $firmMetric = $dm->getRepository('WealthbotRiaBundle:FirmMetric')->findOneBy(array(
            'companyInformationId' => $user->getRiaCompanyInformation()->getId()
        ));*/

        return $this->render('WealthbotRiaBundle:Dashboard:index.html.twig', [
            'user' => $user,
            'clients' => $clients,
            'company_information' => $user->getRiaCompanyInformation(),
            'blocks_sequence' => json_encode($blocksSequence),
            'paperwork_counts' => $workflowRepository->getPaperworkCountsByRiaId($user->getId()),
            'portfolios_counts' => $portfoliosCount,
            'securities_statistic' => json_encode($securitiesStatistic),
            'recent_activity_pagination' => $recentActivityPagination,
            //'firm_metrics' => $firmMetrics
        ]);
    }

    public function ajaxClientsListAction()
    {
        define('HOUSEHOLD_LEVEL', 1);
        define('ACCOUNT_LEVEL', 2);

        $ria = $this->getUser();
        /* @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var \Wealthbot\UserBundle\Repository\UserRepository $userRepo */
        $userRepo = $em->getRepository('WealthbotUserBundle:User');
        /** @var \Wealthbot\ClientBundle\Repository\ClientPortfolioValueRepository $clientPortfolioValuesRepo */
        $clientPortfolioValuesRepo = $em->getRepository('WealthbotClientBundle:ClientPortfolioValue');
        /** @var \Wealthbot\ClientBundle\Repository\ClientAccountValueRepository $clientAccountValuesRepo */
        $clientAccountValuesRepo = $em->getRepository('WealthbotClientBundle:ClientAccountValue');
        /** @var \Wealthbot\UserBundle\Entity\User[] $clients */
        $clients = $userRepo->findClientsByRia($ria);

        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $results = [];
        foreach ($clients as $client) {
            $clientGroup = $client->getGroups()->first();
            $lastPortfolioValue = $clientPortfolioValuesRepo->getLastValueByClient($client);
            $clientPortfolio = $clientPortfolioManager->getCurrentPortfolio($client);

            $clientItem = [
                'id' => $client->getId(),
                'status' => $client->isEnabled() ? 'Active' : 'Closed',
                'lastName' => $client->getLastName(),
                'firstName' => $client->getFirstName(),
                'advisorSet' => $clientGroup ? $clientGroup->getName() : '',
                'custodian' => $client->getCustodian()->getName(),
                'billingSpec' => $client->getAppointedBillingSpec()->getName(),
                'totalValue' => $lastPortfolioValue ? $lastPortfolioValue->getTotalValue() : 0,
                'ceModels' => $client->getProfile()->getClientAccountManaged() === HOUSEHOLD_LEVEL ? $clientPortfolio->getPortfolio()->getName() : '',
                'hasClosedAccounts' => false,
            ];
            /** @var \Wealthbot\ClientBundle\Entity\SystemAccount $account */
            foreach ($client->getSystemAccounts() as $account) {
                $lastSystemClientAccountValue = $clientAccountValuesRepo->getLatestValueForSystemClientAccountId($account->getId());
                $accountItem = [
                    'id' => $account->getClientAccountId(),
                    'status' => ucfirst($account->getStatus()),
                    'lastName' => $account->getClientAccount()->getPrimaryApplicant()->getLastName(),
                    'firstName' => $account->getClientAccount()->getPrimaryApplicant()->getFirstName(),
                    'accountType' => $account->getTypeAsString(),
                    'number' => $account->getAccountNumber(),
                    'ceModels' => $client->getProfile()->getClientAccountManaged() === ACCOUNT_LEVEL ? $clientPortfolio->getPortfolio()->getName() : '',
                    'totalValue' => $lastSystemClientAccountValue ? $lastSystemClientAccountValue->getTotalValue() : 0,
                ];
                $clientItem['accounts'][] = $accountItem;

                if ($account->getStatus() === SystemAccount::STATUS_CLOSED) {
                    $clientItem['hasClosedAccounts'] = true;
                }
            }

            $results[] = $clientItem;
        }

        return $this->getJsonResponse($results);
    }

    public function clientsListAction(Request $request)
    {
        $ria = $this->getUser();
        $activeTab = $request->get('tab') ? $request->get('tab') : 'clients';
        $inviteForm = $this->createForm(new InviteProspectFormType($ria));

        return $this->render('WealthbotRiaBundle:Dashboard:clients_list.html.twig', [
            'inviteForm' => $inviteForm->createView(),
            'activeTab' => $activeTab,
            'searchForm' => $this->createForm(new RiaSearchClientsFormType())->createView(),
        ]);
    }

    /**
     * @SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     */
    public function householdCloseAction(User $riaClient, Request $request)
    {
        $form = $this
            ->createForm(new HouseholdCloseFormType(), $riaClient);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $form->getData();

                /** @var EntityManager $em */
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($formData);
                $em->flush();
            }
        }

        return $this->render('WealthbotRiaBundle:Dashboard:household_close.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     */
    public function householdSettingsPersonalAction(User $riaClient, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $householdForm = $this
            ->createForm(new HouseholdPersonalSettingsFormType(), $riaClient);
        $spouseForm = $this
            ->createForm(new HouseholdSpouseFormType($riaClient), $riaClient->getSpouse());

        if ($request->isMethod('POST')) {
            $householdForm->handleRequest($request);

            $spouseFormValid = true;
            if (Profile::CLIENT_MARITAL_STATUS_MARRIED === $householdForm->get('maritalStatus')->getData()) {
                $spouseForm->handleRequest($request);

                $spouseFormValid = $spouseForm->isValid();
                if ($spouseFormValid) {
                    $spouseFormData = $spouseForm->getData();
                    $em->persist($spouseFormData);
                }
            }

            $householdFormValid = $householdForm->isValid();

            if ($householdFormValid) {
                $householdFormData = $householdForm->getData();
                $em->persist($householdFormData);
            }

            if ($spouseFormValid && $householdFormValid) {
                $em->flush();
            }
        }

        return $this->render('WealthbotRiaBundle:Dashboard:household_settings_personal.html.twig', [
            'householdForm' => $householdForm->createView(),
            'spouseForm' => $spouseForm->createView(),
        ]);
    }

    /**
     * @SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     */
    public function householdSettingsContactAction(User $riaClient, Request $request)
    {
        $form = $this
            ->createForm(new HouseholdContactSettingsFormType(), $riaClient->getProfile());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $form->getData();

                /** @var EntityManager $em */
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($formData);
                $em->flush();
            }
        }

        return $this->render('WealthbotRiaBundle:Dashboard:household_settings_contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     */
    public function householdSettingsBillingAction(User $riaClient, Request $request)
    {
        $form = $this
            ->createForm(new HouseholdBillingSettingsFormType(), $riaClient);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $form->getData();

                /** @var EntityManager $em */
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($formData);
                $em->flush();
            }
        }

        return $this->render('WealthbotRiaBundle:Dashboard:household_settings_billing.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="WealthbotUserBundle:User", options={"id" = "client_id"})
     */
    public function householdSettingsPortfolioAction(User $riaClient, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this
            ->createForm(new HouseholdPortfolioSettingsFormType($em), $riaClient);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $formData = $form->getData();

                /** @var EntityManager $em */
                $em = $this->get('doctrine.orm.entity_manager');
                $em->persist($formData);
                $em->flush();
            }
        }

        return $this->render('WealthbotRiaBundle:Dashboard:household_settings_portfolio.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @SecureParam(name="account", permissions="EDIT")
     * @ParamConverter("account", class="WealthbotClientBundle:ClientAccount", options={"id" = "account_id"})
     */
    public function accountSettingsAction(ClientAccount $account, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $systemAccount = $account->getSystemAccount();
        $form = $this->createForm(new AccountSettingsFormType($em), $account);

        $oneTimeDistribution = new Distribution();
        $oneTimeDistribution->setType(Distribution::TYPE_ONE_TIME);
        $oneTimeDistribution->setSystemClientAccount($systemAccount);
        $oneTimeDistributionForm = $this->createForm(new OneTimeDistributionFormType(), $oneTimeDistribution);

        $scheduledDistribution = $em
            ->getRepository('WealthbotClientBundle:Distribution')
            ->findOneBy(['systemClientAccount' => $systemAccount, 'type' => Distribution::TYPE_SCHEDULED]);
        if (null === $scheduledDistribution) {
            $scheduledDistribution = new Distribution();
            $scheduledDistribution->setType(Distribution::TYPE_SCHEDULED);
            $scheduledDistribution->setSystemClientAccount($systemAccount);
        }
        $scheduledDistributionForm = $this->createForm(new ScheduledDistributionFormType(), $scheduledDistribution);

        if ($request->isMethod('POST')) {
            $scheduledDistributionForm->handleRequest($request);
            if ($scheduledDistributionForm->isValid()) {
                $scheduledDistributionFormData = $scheduledDistributionForm->getData();
                if ($scheduledDistributionFormData->getAmount() > 0) {
                    $em->persist($scheduledDistributionFormData);
                    $em->flush();
                }
            }

            $oneTimeDistributionForm->handleRequest($request);
            if ($oneTimeDistributionForm->isValid()) {
                $oneTimeDistributionFormData = $oneTimeDistributionForm->getData();
                if ($oneTimeDistributionFormData->getAmount() > 0) {
                    $em->persist($oneTimeDistributionFormData);
                    $em->flush();
                }
            }

            $form->handleRequest($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $em->persist($formData);
                $em->flush();
            }
        }

        return $this->render('WealthbotRiaBundle:Dashboard:account_settings.html.twig', [
            'scheduledDistributionForm' => $scheduledDistributionForm->createView(),
            'oneTimeDistributionForm' => $oneTimeDistributionForm->createView(),
            'form' => $form->createView(),
        ]);
    }

    public function showClientAction(Request $request)
    {
        $action = $request->query->get('action', 'Overview');
        $doAction = $action === 'Transactions' ? 'WealthbotClientBundle:Dashboard:transactions' : 'WealthbotClientBundle:Dashboard:index';

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WealthbotUserBundle:User');

        $ria = $this->getUser();
        $client = $repository->getClientByIdAndRiaId($request->get('client_id'), $ria->getId());

        if (!$client || $client->hasStatusProspect()) {
            return $this->redirect($this->generateUrl('rx_ria_dashboard_clients'));
        }

        $acl = $this->get('wealthbot_client.acl');
        $acl->setClientForRiaClientView($ria, $client->getId());

        $activeTab = $request->get('tab') ? $request->get('tab') : 'clients';
        $inviteForm = $this->createForm(new InviteProspectFormType($ria));

        return $this->render('WealthbotRiaBundle:Dashboard:show_client.html.twig', [
            'inviteForm' => $inviteForm->createView(),
            'activeTab' => $activeTab,
            'client' => $client,
            'action' => $action,
            'doAction' => $doAction,
            'searchForm' => $this->createForm(new RiaSearchClientsFormType())->createView(),
        ]);
    }

    public function clientPortfolioAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WealthbotUserBundle:User');

        $ria = $this->getUser();
        $client = $repository->getClientByIdAndRiaId($request->get('client_id'), $ria->getId());

        if (!$client || $client->hasStatusProspect()) {
            return $this->redirect($this->generateUrl('rx_ria_dashboard_clients'));
        }

        $acl = $this->get('wealthbot_client.acl');
        $acl->setClientForRiaClientView($ria, $client->getId());

        $activeTab = $request->get('tab') ? $request->get('tab') : 'clients';
        $inviteForm = $this->createForm(new InviteProspectFormType($ria));

        return $this->render('WealthbotRiaBundle:Dashboard:client_portfolio.html.twig', [
            'inviteForm' => $inviteForm->createView(),
            'activeTab' => $activeTab,
            'client' => $client,
            'searchForm' => $this->createForm(new RiaSearchClientsFormType())->createView(),
        ]);
    }

    public function clientViewAction(Request $request)
    {
        /** @var Acl $acl */
        $acl = $this->get('wealthbot_client.acl');
        /** @var \Wealthbot\UserBundle\Repository\UserRepository $repository */
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository('WealthbotUserBundle:User');

        $ria = $this->getUser();
        $acl->resetRiaClientView($ria);

        $client = $repository->getClientByIdAndRiaId($request->get('client_id'), $ria->getId());
        if (!$client) {
            throw $this->createNotFoundException();
        }

        $acl->setClientForRiaClientView($ria, $client->getId());

        switch ($request->get('redirect-action')) {
            case 'overview':
                $redirectUrl = $this->generateUrl('rx_client_dashboard');
                break;
            case 'holdings':
                $redirectUrl = $this->generateUrl('wealthbot_client_holdings');
                break;
            case 'allocation':
                $redirectUrl = $this->generateUrl('wealthbot_client_allocation');
                break;
            case 'gainslosses':
                $redirectUrl = $this->generateUrl('wealthbot_client_gainslosses');
                break;
            case 'transactions':
                $redirectUrl = $this->generateUrl('wealthbot_client_transactions');
                break;
            case 'billing':
                $redirectUrl = $this->generateUrl('wealthbot_client_billing');
                break;
            case 'performance':
                $redirectUrl = $this->generateUrl('wealthbot_client_performance');
                break;
            case 'activity':
                $redirectUrl = $this->generateUrl('wealthbot_client_activity');
                break;
            case 'documents':
                $redirectUrl = $this->generateUrl('wealthbot_client_documents');
                break;
            case 'account_management':
                $redirectUrl = $this->generateUrl('rx_client_dashboard_account_management');
                break;
            default:
                $redirectUrl = $this->generateUrl('rx_client_dashboard');
                break;
        }

        return $this->redirect($redirectUrl);
    }

    public function companyInformationAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $this->getUser();
        $riaCompanyInformation = $user->getRiaCompanyInformation();

        $progress = 0;
        $modelCompletion = $em->getRepository('WealthbotRiaBundle:RiaModelCompletion')->findOneBy(['ria_user_id' => $user->getId()]);

        if ($modelCompletion) {
            $progress = $modelCompletion->getProgress();
        }

        $form = $this->createForm(new RiaModelCompletionFormType($user, $em), $modelCompletion);
        $searchForm = $this->createForm(new RiaSearchClientsFormType());

        return $this->render('WealthbotRiaBundle:Dashboard:_company_information.html.twig', [
            'company_information' => $riaCompanyInformation,
            'form' => $form->createView(),
            'progress' => $progress,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    public function updateModelsCompletionAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $repo CeModelRepository */
        $repo = $em->getRepository('WealthbotAdminBundle:CeModel');

        /** @var User $user */
        $user = $this->getUser();

        $modelCompletion = $em->getRepository('WealthbotRiaBundle:RiaModelCompletion')->findOneBy([
            'ria_user_id' => $user->getId(),
        ]);
        if (!$modelCompletion) {
            $modelCompletion = new RiaModelCompletion();
            $modelCompletion->setRia($user);
        }

        $form = $this->createForm(new RiaModelCompletionFormType($user, $em), $modelCompletion);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $modelCompletion RiaModelCompletion */
                $modelCompletion = $form->getData();
                $riaCompanyInformation = $user->getRiaCompanyInformation();

                $portfolioModel = $riaCompanyInformation->getPortfolioModel();

                if ($modelCompletion->getSelectCustodians()) {
                    if (!$user->getCustodian()) {
                        return $this->getJsonResponse([
                            'status' => 'error',
                            'message' => 'You have not selected custodian.',
                        ]);
                    }
                }

                if ($modelCompletion->getRebalancingSettings()) {
                    if (!$riaCompanyInformation->getRebalancedMethod()) {
                        return $this->getJsonResponse([
                            'status' => 'error',
                            'message' => 'You have not customized rebalancing setting.',
                        ]);
                    }
                }

                if ($modelCompletion->getCreateSecurities()) {
                    /** @var AssetClassRepository $assetClassRepository */
                    $assetClassRepository = $em->getRepository('WealthbotAdminBundle:AssetClass');

                    $assetClasses = $assetClassRepository->findWithSubclassesByModelIdAndOwnerId($portfolioModel->getId(), $user->getId());
                    if (empty($assetClasses)) {
                        return $this->getJsonResponse([
                            'status' => 'error',
                            'message' => 'You have not created asset classes and subclasses. Please create them before continuing.',
                        ]);
                    }
                }

                if ($modelCompletion->getAssignSecurities()) {
                    $securityAssignments = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findBy(['model_id' => $portfolioModel->getId()]);

                    if (empty($securityAssignments)) {
                        return $this->getJsonResponse([
                            'status' => 'error',
                            'message' => 'You have not assigned classes and subclasses. Please assign them before continuing.',
                        ]);
                    }
                }

                if ($modelCompletion->getModelsCreated()) {
                    $portfolioId = $portfolioModel->getId();
                    $finishedModel = $repo->findCompletedModelByParentIdAndOwnerId($portfolioId, $user->getId());

                    if (!$finishedModel) {
                        return $this->getJsonResponse([
                            'status' => 'error',
                            'message' => 'You have not completed models. Please complete them before continuing.',
                        ]);
                    }

                    $modelWithoutRiskRating = $repo->findModelWithoutRiskRatingByRiaId($user->getId());
                    if ($modelWithoutRiskRating) {
                        return $this->getJsonResponse([
                            'status' => 'error',
                            'message' => 'You have models without risk rating. Please modify the risk rating of the models before continuing.',
                        ]);
                    }
                }

                if ($modelCompletion->getCustomizeProposals()) {
                    $existQuestions = $em->getRepository('WealthbotRiaBundle:RiskQuestion')->findOneBy([
                        'owner_id' => $user->getId(),
                    ]);
                    if (!$existQuestions) {
                        return $this->getJsonResponse([
                            'status' => 'error',
                            'message' => 'You do not completed the Risk Profiling. Please complete the Risk Profiling section before continuing.',
                        ]);
                    }
                }

                if ($modelCompletion->isBillingComplete() && $user->getBillingSpecs()->count() === 0) {
                    return $this->getJsonResponse([
                            'status' => 'error',
                            'message' => 'You do not completed the Billing Specs. Please create as least one Billing Spec on Billing section before continuing.',
                        ]);
                }

                if ($modelCompletion->getProposalDocuments()) {
                    $documentManager = $this->get('wealthbot_user.document_manager');
                    $documentTypes = [
                        Document::TYPE_ADV,
                        Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT,
                    ];

                    foreach ($documentTypes as $documentType) {
                        if (!$documentManager->getUserDocumentByType($user->getId(), $documentType)) {
                            return $this->getJsonResponse([
                                'status' => 'error',
                                'message' => 'You did not uploaded proposal documents.',
                            ]);
                        }
                    }
                }

                $em->persist($modelCompletion);
                $em->flush();

                if ($modelCompletion->isComplete()) {
                    $this->get('wealthbot.mailer')->sendAdminsRiaActivatedEmail($user);
                }

                return $this->getJsonResponse(['status' => 'success']);
            }
        }

        return $this->getJsonResponse(['status' => 'error']);
    }

    public function isCanCreateClientAction(Request $request)
    {
        /** @var $user User */
        $user = $this->getUser();
        $riaCompanyInformation = $user->getRiaCompanyInformation();

        if (!$riaCompanyInformation->getActivated()) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'You must be activated by an admin before you can create a client.',
            ]);
        }

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function securitiesAction(Request $request)
    {
        /** @var \Wealthbot\UserBundle\Entity\User $user */
        $user = $this->getUser();

        $isShowPriority = false;
        $isShowRebalancerHistory = false;
        if ($user->hasRole('ROLE_RIA') && $user->getRiaCompanyInformation()) {
            $riaCompanyInfo = $user->getRiaCompanyInformation();
            $isShowPriority = $riaCompanyInfo->isShowSubclassPriority();
            $isShowRebalancerHistory = $riaCompanyInfo->isRelationTypeTamp();
        }

        return $this->render('WealthbotRiaBundle:Dashboard:securities.html.twig', [
            'is_show_subclasses_priority' => $isShowPriority,
            'active_tab' => $request->get('tab'),
            'is_show_rebalancer_history' => $isShowRebalancerHistory,
        ]);
    }

    public function rebalancingAction(Request $request)
    {
        return $this->render('WealthbotRiaBundle:Dashboard:rebalancing.html.twig', [
            'active_tab' => $request->get('tab', 'rebalancer'),
        ]);
    }

    public function menuAction($route)
    {
        /** @var User $ria */
        $ria = $this->getUser();

        return $this->render('WealthbotRiaBundle:Dashboard:menu.html.twig', [
            'route' => $route,
            'riaCompanyInformation' => $ria->getRiaCompanyInformation(),
        ]);
    }

    public function clientsSearchAction(Request $request)
    {
        $query = $request->get('query');
        $withProspects = $request->get('with_prospects');
        $ria = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $userRepo = $em->getRepository('WealthbotUserBundle:User');

        if ($withProspects) {
            $clients = $userRepo->findClientsByRiaId($ria->getId(), $query);
        } else {
            $clients = $userRepo->findClientsWithoutProspectsByRiaId($ria->getId(), $query);
        }

        $response = [];
        /** @var User $client */
        foreach ($clients as $client) {
            $clientStr = $client->getLastName().', '.$client->getFirstName();

            if ($withProspects) {
                $clientStr .= ' - '.ucfirst($client->getClientStatusAsString());
            }

            if ($client->hasStatusProspect()) {
                $redirectUrl = $this->generateUrl('rx_ria_prospect_portfolio', ['client_id' => $client->getId()]);
            } else {
                $redirectUrl = $this->generateUrl('rx_ria_dashboard_show_client', ['client_id' => $client->getId()]);
            }

            $response[] = [
                'id' => $client->getId(),
                'name' => $clientStr,
                'redirect_url' => $redirectUrl,
            ];
        }

        return $this->getJsonResponse($response);
    }

    public function swapBoxesAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            $this->createNotFoundException();
        }

        $boxes = $request->get('boxes');

        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotRiaBundle:RiaDashboardBox');
        $ria = $this->getUser();

        foreach ($boxes as $box) {
            $dbBox = $repo->findOneBy(['ria_user_id' => $ria->getId(), 'template' => $box['template']]);

            if (!$dbBox) {
                $dbBox = new RiaDashboardBox();
                $dbBox->setRia($ria);
                $dbBox->setTemplate($box['template']);
            }
            $dbBox->setSequence($box['sequence']);

            $em->persist($dbBox);
        }

        $em->flush();

        return $this->getJsonResponse([
            'status' => 'success',
        ]);
    }

    public function deleteMostRecentActivityAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $activitySummaryManager = $this->get('wealthbot_client.activity_summary.manager');
        $paginator = $this->get('knp_paginator');

        $ria = $this->getUser();

        $mostRecentActivity = $activitySummaryManager->find($request->get('id'));

        if (!$mostRecentActivity || ($mostRecentActivity && !$activitySummaryManager->hasDeleteAccess($ria, $mostRecentActivity))) {
            throw $this->createNotFoundException('Most Recent Activity with id %s not found');
        }

        $mostRecentActivity->setIsShowRia(false);
        $em->persist($mostRecentActivity);
        $em->flush();

        $recentActivityPagination = $paginator->paginate($activitySummaryManager->findRiaActivitySummariesQuery($ria->getId(), 9), 1, 9);
        $recentActivityPagination->setUsedRoute('rx_ria_dashboard');

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotRiaBundle:Dashboard:_most_recent_activity_box.html.twig', [
                'recent_activity_pagination' => $recentActivityPagination,
            ]),
        ]);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
