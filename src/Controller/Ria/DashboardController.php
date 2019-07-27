<?php

namespace App\Controller\Ria;

use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\AssetClassRepository;
use App\Repository\CeModelRepository;
use App\Entity\ClientAccount;
use App\Entity\Distribution;
use App\Entity\SystemAccount;
use App\Model\Acl;
use App\Entity\RiaDashboardBox;
use App\Entity\RiaModelCompletion;
use App\Form\Type\AccountSettingsFormType;
use App\Form\Type\HouseholdBillingSettingsFormType;
use App\Form\Type\HouseholdCloseFormType;
use App\Form\Type\HouseholdContactSettingsFormType;
use App\Form\Type\HouseholdPersonalSettingsFormType;
use App\Form\Type\HouseholdPortfolioSettingsFormType;
use App\Form\Type\HouseholdSpouseFormType;
use App\Form\Type\InviteProspectFormType;
use App\Form\Type\OneTimeDistributionFormType;
use App\Form\Type\RiaModelCompletionFormType;
use App\Form\Type\RiaSearchClientsFormType;
use App\Form\Type\ScheduledDistributionFormType;
use App\Entity\Document;
use App\Entity\Profile;
use App\Entity\User;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $workflowRepository = $em->getRepository('App\Entity\Workflow');
        $userRepository = $em->getRepository('App\Entity\User');
        //  $activityManager = $this->get('wealthbot.activity.manager');

        $user = $this->getUser();
        $clients = $userRepository->findClientsByRiaId($user->getId());

        $paginator = $this->get('knp_paginator');
        //// $recentActivityPagination = $paginator->paginate($activityManager->findByRiaQuery($user), 1, 10);

        if ($request->isXmlHttpRequest() && 'most_recent_activity' === $request->get('block')) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Ria/Workflow/_workflow_activity_list.html.twig', [
                  //  'pagination' => $recentActivityPagination,
                    'show_pagination' => false,
                ]),
            ]);
        }

        $riaDashboardBoxes = $em->getRepository('App\Entity\RiaDashboardBox')->findBy([
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


        $recentActivityPagination = $em->getRepository('App\Entity\Workflow')->findByRiaId($this->getUser()->getId());

        $securitiesStatistic = [
            ['label' => 'Vanguard Total Stock Market', 'data' => 50000000],
            ['label' => 'iShares Total Bond', 'data' => 40000000],
            ['label' => 'DFA Large Cap Value', 'data' => 20000000],
            ['label' => 'American Funds Growth Fund', 'data' => 10000000],
            ['label' => 'Vanguard Intermediate Bond', 'data' => 10000000],
        ];

        return $this->render('/Ria/Dashboard/index.html.twig', [
            'user' => $user,
            'clients' => $clients,
            'company_information' => $user->getRiaCompanyInformation(),
            'blocks_sequence' => json_encode($blocksSequence),
            'paperwork_counts' => $workflowRepository->getPaperworkCountsByRiaId($user->getId()),
            'portfolios_counts' => $portfoliosCount,
            'securities_statistic' => json_encode($securitiesStatistic),
            'recent_activity_pagination' => $recentActivityPagination
        ]);
    }

    public function ajaxClientsList()
    {
        define('HOUSEHOLD_LEVEL', 1);
        define('ACCOUNT_LEVEL', 2);

        $ria = $this->getUser();
        /* @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var \Repository\UserRepository $userRepo */
        $userRepo = $em->getRepository('App\Entity\User');
        /** @var \Repository\ClientPortfolioValueRepository $clientPortfolioValuesRepo */
        $clientPortfolioValuesRepo = $em->getRepository('App\Entity\ClientPortfolioValue');
        /** @var \Repository\ClientAccountValueRepository $clientAccountValuesRepo */
        $clientAccountValuesRepo = $em->getRepository('App\Entity\ClientAccountValue');
        /** @param \App\Entity\User[] $clients */
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
                    'ceModels' => HOUSEHOLD_LEVEL === $client->getProfile()->getClientAccountManaged() ? $clientPortfolio->getPortfolio()->getName() : '',
                    'hasClosedAccounts' => false,
                ];
            /** @param \App\Entity\SystemAccount $account */
            foreach ($client->getSystemAccounts() as $account) {
                if ($account->getClientAccount()) {
                    $lastSystemClientAccountValue = $clientAccountValuesRepo->getLatestValueForSystemClientAccountId($account->getId());
                    $accountItem = [
                            'id' => $account->getClientAccountId(),
                            'status' => ucfirst($account->getStatus()),
                            'lastName' => $account->getClientAccount()->getPrimaryApplicant()->getLastName(),
                            'firstName' => $account->getClientAccount()->getPrimaryApplicant()->getFirstName(),
                            'accountType' => $account->getTypeAsString(),
                            'number' => $account->getAccountNumber(),
                            'ceModels' => ACCOUNT_LEVEL === $client->getProfile()->getClientAccountManaged() ? '' : '',
                            'totalValue' => $lastSystemClientAccountValue ? $lastSystemClientAccountValue->getTotalValue() : 0,
                        ];
                    $clientItem['accounts'][] = $accountItem;

                    if (SystemAccount::STATUS_CLOSED === $account->getStatus()) {
                        $clientItem['hasClosedAccounts'] = true;
                    }
                }

                $results[] = $clientItem;
            }
        }

        return $this->json($results);
    }

    public function clientsList(Request $request)
    {
        $ria = $this->getUser();
        $activeTab = $request->get('tab') ? $request->get('tab') : 'clients';
        $inviteForm = $this->createForm(InviteProspectFormType::class, null, ['ria'=>$ria]);

        return $this->render('/Ria/Dashboard/clients_list.html.twig', [
            'inviteForm' => $inviteForm->createView(),
            'activeTab' => $activeTab,
            'searchForm' => $this->createForm(RiaSearchClientsFormType::class)->createView(),
        ]);
    }

    /**
     * SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     */
    public function householdClose(User $riaClient, Request $request)
    {
        $form = $this
            ->createForm(HouseholdCloseFormType::class, $riaClient);

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

        return $this->render('/Ria/Dashboard/household_close.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     */
    public function householdSettingsPersonal(User $riaClient, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $householdForm = $this
            ->createForm(HouseholdPersonalSettingsFormType::class, $riaClient);
        $spouseForm = $this
            ->createForm(HouseholdSpouseFormType::class);

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

        return $this->render('/Ria/Dashboard/household_settings_personal.html.twig', [
            'householdForm' => $householdForm->createView(),
            'spouseForm' => $spouseForm->createView(),
        ]);
    }

    /**
     * SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     */
    public function householdSettingsContact(User $riaClient, Request $request)
    {
        $form = $this
            ->createForm(HouseholdContactSettingsFormType::class, $riaClient->getProfile());

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

        return $this->render('/Ria/Dashboard/household_settings_contact.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     */
    public function householdSettingsBilling(User $riaClient, Request $request)
    {
        $form = $this
            ->createForm(HouseholdBillingSettingsFormType::class, $riaClient);

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

        return $this->render('/Ria/Dashboard/household_settings_billing.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * SecureParam(name="riaClient", permissions="EDIT")
     * @ParamConverter("riaClient", class="App\Entity\User", options={"id" = "client_id"})
     */
    public function householdSettingsPortfolio(User $riaClient, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this
            ->createForm(HouseholdPortfolioSettingsFormType::class, $riaClient, [
                'em' => $em
            ]);

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

        return $this->render('/Ria/Dashboard/household_settings_portfolio.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * SecureParam(name="account", permissions="EDIT")
     * @ParamConverter("account", class="App\Entity\ClientAccount", options={"id" = "account_id"})
     */
    public function accountSettings(ClientAccount $account, Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $systemAccount = $account->getSystemAccount();
        $form = $this->createForm(AccountSettingsFormType::class, $account, [
            'em' => $em
        ]);

        $oneTimeDistribution = new Distribution();
        $oneTimeDistribution->setType(Distribution::TYPE_ONE_TIME);
        $oneTimeDistribution->setSystemClientAccount($systemAccount);
        $oneTimeDistributionForm = $this->createForm(
            OneTimeDistributionFormType::class,
            $oneTimeDistribution,
            [
                'client' => $account->getClient()
            ]
        );

        $scheduledDistribution = $em
            ->getRepository('App\Entity\Distribution')
            ->findOneBy(['systemClientAccount' => $systemAccount, 'type' => Distribution::TYPE_SCHEDULED]);
        if (null === $scheduledDistribution) {
            $scheduledDistribution = new Distribution();
            $scheduledDistribution->setType(Distribution::TYPE_SCHEDULED);
            $scheduledDistribution->setSystemClientAccount($systemAccount);
        }
        $scheduledDistributionForm = $this->createForm(
            ScheduledDistributionFormType::class,
            $scheduledDistribution,
            [
                'client' => $account->getClient()
            ]
        );

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

        return $this->render('/Ria/Dashboard/account_settings.html.twig', [
            'scheduledDistributionForm' => $scheduledDistributionForm->createView(),
            'oneTimeDistributionForm' => $oneTimeDistributionForm->createView(),
            'form' => $form->createView(),
        ]);
    }

    public function showClient(Request $request)
    {
        $action = $request->query->get('action', 'Overview');
        $doAction = 'Transactions' === $action ? "App\\Controller\\Client\\DashboardController:transactions" : "App\\Controller\Client\\DashboardController:index";

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\User');

        $ria = $this->getUser();
        $client = $repository->getClientByIdAndRiaId($request->get('account_id'), $ria->getId());

        if (!$client || $client->hasStatusProspect()) {
            return $this->redirect($this->generateUrl('rx_ria_dashboard_clients'));
        }

        $acl = $this->get('wealthbot_client.acl');
        $acl->setClientForRiaClientView($ria, $client->getId());

        $activeTab = $request->get('tab') ? $request->get('tab') : 'clients';
        $inviteForm = $this->createForm(InviteProspectFormType::class, $ria);

        return $this->render('/Ria/Dashboard/show_client.html.twig', [
            'inviteForm' => $inviteForm->createView(),
            'activeTab' => $activeTab,
            'client' => $client,
            'action' => $action,
            'doAction' => $doAction,
            'searchForm' => $this->createForm(RiaSearchClientsFormType::class)->createView(),
        ]);
    }

    public function clientPortfolio(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\User');

        $ria = $this->getUser();
        $client = $repository->getClientByIdAndRiaId($request->get('client_id'), $ria->getId());

        if (!$client || $client->hasStatusProspect()) {
            return $this->redirect($this->generateUrl('rx_ria_dashboard_clients'));
        }

        $acl = $this->get('wealthbot_client.acl');
        $acl->setClientForRiaClientView($ria, $client->getId());

        $activeTab = $request->get('tab') ? $request->get('tab') : 'clients';
        $inviteForm = $this->createForm(InviteProspectFormType::class, $ria);

        return $this->render('/Ria/Dashboard/client_portfolio.html.twig', [
            'inviteForm' => $inviteForm->createView(),
            'activeTab' => $activeTab,
            'client' => $client,
            'searchForm' => $this->createForm(
                RiaSearchClientsFormType::class
            )
                ->createView(),
        ]);
    }

    public function clientView(Request $request)
    {
        /** @var Acl $acl */
        $acl = $this->get('wealthbot_client.acl');
        /** @var \Repository\UserRepository $repository */
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository('App\Entity\User');

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

    public function companyInformation()
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $this->getUser();
        $riaCompanyInformation = $user->getRiaCompanyInformation();

        $progress = 0;
        $modelCompletion = $em->getRepository('App\Entity\RiaModelCompletion')->findOneBy(['ria_user_id' => $user->getId()]);

        if ($modelCompletion) {
            $progress = $modelCompletion->getProgress();
        }

        $form = $this->createForm(RiaModelCompletionFormType::class, null, ['user' => $user,'em' => $em, 'modelCompletion' => $modelCompletion]);
        $searchForm = $this->createForm(RiaSearchClientsFormType::class);

        return $this->render('/Ria/Dashboard/_company_information.html.twig', [
            'company_information' => $riaCompanyInformation,
            'form' => $form->createView(),
            'progress' => $progress,
            'searchForm' => $searchForm->createView(),
        ]);
    }

    public function updateModelsCompletion(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $repo CeModelRepository */
        $repo = $em->getRepository('App\Entity\CeModel');

        /** @var User $user */
        $user = $this->getUser();

        $modelCompletion = $em->getRepository('App\Entity\RiaModelCompletion')->findOneBy([
            'ria_user_id' => $user->getId(),
        ]);
        if (!$modelCompletion) {
            $modelCompletion = new RiaModelCompletion();
            $modelCompletion->setRia($user);
        }

        $form = $this->createForm(RiaModelCompletionFormType::class, null, ['user'=>$user, 'em'=>$em,'modelCompletion'=> $modelCompletion]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $modelCompletion RiaModelCompletion */
                $modelCompletion = $form->getData();
                $riaCompanyInformation = $user->getRiaCompanyInformation();

                $portfolioModel = $riaCompanyInformation->getPortfolioModel();

                if ($modelCompletion->getSelectCustodians()) {
                    if (!$user->getCustodian()) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'You have not selected custodian.',
                        ]);
                    }
                }

                if ($modelCompletion->getRebalancingSettings()) {
                    if (!$riaCompanyInformation->getRebalancedMethod()) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'You have not customized rebalancing setting.',
                        ]);
                    }
                }

                if ($modelCompletion->getCreateSecurities()) {
                    /** @var AssetClassRepository $assetClassRepository */
                    $assetClassRepository = $em->getRepository('App\Entity\AssetClass');

                    $assetClasses = $assetClassRepository->findWithSubclassesByModelIdAndOwnerId($portfolioModel->getId(), $user->getId());
                    if (empty($assetClasses)) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'You have not created asset classes and subclasses. Please create them before continuing.',
                        ]);
                    }
                }

                if ($modelCompletion->getAssignSecurities()) {
                    $securityAssignments = $em->getRepository('App\Entity\SecurityAssignment')->findBy(['model_id' => $portfolioModel->getId()]);

                    if (empty($securityAssignments)) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'You have not assigned classes and subclasses. Please assign them before continuing.',
                        ]);
                    }
                }

                if ($modelCompletion->getModelsCreated()) {
                    $portfolioId = $portfolioModel->getId();
                    $finishedModel = $repo->findCompletedModelByParentIdAndOwnerId($portfolioId, $user->getId());

                    if (!$finishedModel) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'You have not completed models. Please complete them before continuing.',
                        ]);
                    }

                    $modelWithoutRiskRating = $repo->findModelWithoutRiskRatingByRiaId($user->getId());
                    if ($modelWithoutRiskRating) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'You have models without risk rating. Please modify the risk rating of the models before continuing.',
                        ]);
                    }
                }

                if ($modelCompletion->getCustomizeProposals()) {
                    $existQuestions = $em->getRepository('App\Entity\RiskQuestion')->findOneBy([
                        'owner_id' => $user->getId(),
                    ]);
                    if (!$existQuestions) {
                        return $this->json([
                            'status' => 'error',
                            'message' => 'You do not completed the Risk Profiling. Please complete the Risk Profiling section before continuing.',
                        ]);
                    }
                }

                if ($modelCompletion->isBillingComplete() && 0 === $user->getBillingSpecs()->count()) {
                    return $this->json([
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
                            return $this->json([
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

                return $this->json(['status' => 'success']);
            }
        }

        return $this->json(['status' => 'error']);
    }

    public function isCanCreateClient(Request $request)
    {
        /** @var $user User */
        $user = $this->getUser();
        $riaCompanyInformation = $user->getRiaCompanyInformation();

        if (!$riaCompanyInformation->getActivated()) {
            return $this->json([
                'status' => 'error',
                'message' => 'You must be activated by an admin before you can create a client.',
            ]);
        }

        return $this->json(['status' => 'success']);
    }

    public function securities(Request $request)
    {
        /** @param \App\Entity\User $user */
        $user = $this->getUser();

        $isShowPriority = false;
        $isShowRebalancerHistory = false;
        if ($user->hasRole('ROLE_RIA') && $user->getRiaCompanyInformation()) {
            $riaCompanyInfo = $user->getRiaCompanyInformation();
            $isShowPriority = $riaCompanyInfo->isShowSubclassPriority();
            $isShowRebalancerHistory = $riaCompanyInfo->isRelationTypeTamp();
        }

        return $this->render('/Ria/Dashboard/securities.html.twig', [
            'is_show_subclasses_priority' => $isShowPriority,
            'active_tab' => $request->get('tab'),
            'is_show_rebalancer_history' => $isShowRebalancerHistory,
        ]);
    }

    public function rebalancing(Request $request)
    {
        return $this->render('/Ria/Dashboard/rebalancing.html.twig', [
            'active_tab' => $request->get('tab', 'rebalancer'),
        ]);
    }

    public function menu($route)
    {
        /** @var User $ria */
        $ria = $this->getUser();

        return $this->render('/Ria/Dashboard/menu.html.twig', [
            'route' => $route,
            'riaCompanyInformation' => $ria->getRiaCompanyInformation(),
        ]);
    }

    public function clientsSearch(Request $request)
    {
        $query = $request->get('query');
        $withProspects = $request->get('with_prospects');
        $ria = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $userRepo = $em->getRepository('App\Entity\User');

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

        return $this->json($response);
    }

    public function swapBoxes(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            $this->createNotFoundException();
        }

        $boxes = $request->get('boxes');

        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\RiaDashboardBox');
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

        return $this->json([
            'status' => 'success',
        ]);
    }

    public function deleteMostRecentActivity(Request $request)
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

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Dashboard/_most_recent_activity_box.html.twig', [
                'recent_activity_pagination' => $recentActivityPagination,
            ]),
        ]);
    }
}
