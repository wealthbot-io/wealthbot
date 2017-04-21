<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.02.13
 * Time: 16:40
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\AdminEvents;
use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Event\UserHistoryEvent;
use Wealthbot\ClientBundle\ClientEvents;
use Wealthbot\ClientBundle\Document\TempPortfolio;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\Beneficiary;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\ClientSettings;
use Wealthbot\ClientBundle\Entity\ClosingAccountHistory;
use Wealthbot\ClientBundle\Entity\Position;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Event\WorkflowEvent;
use Wealthbot\ClientBundle\Form\Factory\AccountContributionFormFactory;
use Wealthbot\ClientBundle\Form\Factory\DistributionFormFactory;
use Wealthbot\ClientBundle\Form\Handler\ClientTempQuestionsFormHandler;
use Wealthbot\ClientBundle\Form\Handler\CloseAccountsFormHandler;
use Wealthbot\ClientBundle\Form\Type\AccountGroupsFormType;
use Wealthbot\ClientBundle\Form\Type\BankInformationFormType;
use Wealthbot\ClientBundle\Form\Type\BeneficiaryFormType;
use Wealthbot\ClientBundle\Form\Type\ClientAddressFormType;
use Wealthbot\ClientBundle\Form\Type\ClientQuestionsFormType;
use Wealthbot\ClientBundle\Form\Type\ClientStopTLHValueFormType;
use Wealthbot\ClientBundle\Form\Type\CloseAccountsFormType;
use Wealthbot\ClientBundle\Form\Type\DashboardRetirementPlanInfoFormType;
use Wealthbot\ClientBundle\Mailer\TwigSwiftMailer;
use Wealthbot\ClientBundle\Repository\BeneficiaryRepository;
use Wealthbot\ClientBundle\Repository\SystemAccountRepository;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\RiaBundle\Form\Type\ClientSasCashCollectionFormType;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Form\Handler\ClientDocumentFormHandler;
use Wealthbot\UserBundle\Form\Type\ClientDocumentFormType;

class DashboardController extends Controller
{
    use AclController;

    public function indexAction(Request $request)
    {
        $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $workflowManager = $this->get('wealthbot.workflow.manager');

        $isClientView = !$this->isRiaClientView();
        $client = $this->getUser();
        $isAjax = $request->isXmlHttpRequest();

        $accountValues = $systemAccountManager->getClientAccountsValues($client);
        if ($isClientView) {
            $isInitRebalance = true;
        } else {
            $initRebalanceWorkflow = $workflowManager->findNotCompletedInitRebalanceWorkflow($client);
            if ($initRebalanceWorkflow) {
                $isInitRebalance = $systemAccountManager->isClientAccountsHaveInitRebalanceStatus($client);
            } else {
                $isInitRebalance = true;
            }
        }

        $sasCashForm = $this->createForm(new ClientSasCashCollectionFormType($client));

        $parameters = [
            'client' => $client,
            'is_client_view' => $isClientView,
            'layout_variables' => $this->getLayoutVariables('Overview', 'rx_client_dashboard', false),
            'sas_cash_form' => $sasCashForm->createView(),
            'account_values' => $accountValues,
            'is_init_rebalance' => $isInitRebalance,
            'client_portfolio_values_information' => $clientPortfolioValuesManager->prepareClientPortfolioValuesInformation($client),
        ];

        if ($isAjax) {
            return $this->getJsonResponse([
                'status' => 'success',
                'active_tab' => 'overview',
                'content' => $this->renderView('WealthbotClientBundle:Dashboard:_index_content.html.twig', $parameters),
            ]);
        }

        return $this->render('WealthbotClientBundle:Dashboard:index.html.twig', $parameters);
    }

    public function allocationAction(Request $request)
    {
        $accountId = $request->get('account_id');
        $clientAllocationManager = $this->get('wealthbot_client.client_allocation_values.manager');

        $isClientView = !$this->isRiaClientView();

        return $this->prepareResponse($request, 'allocation', 'Allocation', $clientAllocationManager->getValues($this->getUser(), $isClientView, $accountId));
    }

    public function holdingsAction(Request $request)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $lotsRepo = $em->getRepository('WealthbotClientBundle:Lot');
        $positionsRepo = $em->getRepository('WealthbotClientBundle:Position');
        $clientAccountRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');
        $securityAssignmentRepo = $em->getRepository('WealthbotAdminBundle:SecurityAssignment');

        $client = $this->getUser();

        $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $isClientView = !$this->isRiaClientView();
        $activeClientAccounts = $systemAccountManager->getAccountsForClient($client, $isClientView);

        $parameters = [];

        //get accounts...
        $accounts = $activeClientAccounts;
        if ($accountId = $request->get('account_id')) {
            /** @var ClientAccount $account */
            if ($account = $clientAccountRepo->findOneBy(['id' => $accountId, 'client' => $client])) {
                if ($systemAccount = $account->getSystemAccount()) {
                    $accounts = [$systemAccount];
                }
            }
        }

        //get positions by accounts
        /** @var Position[] $positions */
        $positions = $positionsRepo->getOpenPositions($accounts);

        $portfolioValue = $clientPortfolioManager->getPortfolioValue($client);

        //get asset types by security
        $assetTypes = [];
        foreach ($positions as $position) {
            $security = $position->getSecurity();
            //get security assignment for that security and my ria.
            /** @var SecurityAssignment $securityAssignment */
            $securityAssignment = $securityAssignmentRepo->getOneBySecurityAndAccount($security, $position->getClientSystemAccount());

            //get asset type
            if (!$securityAssignment) {
                continue;
            }

            $assetType = $securityAssignment->getSubclass()->getAssetClass()->getName().' - '.$securityAssignment->getSubclass()->getName();
            if (!$assetType) {
                continue;
            }

            $initialLot = $lotsRepo->getInitialLot($position);
            $costBasis = $initialLot->getAmount() / $initialLot->getQuantity() * $position->getQuantity();
            $currentValue = $position->getAmount();
            $sharesOwned = $position->getQuantity();

            $securityId = $security->getId();
            if (isset($assetTypes[$assetType][$securityId])) {
                $currentValue += $assetTypes[$assetType][$securityId]['currentValue'];
                $costBasis += $assetTypes[$assetType][$securityId]['costBasis'];
                $sharesOwned += $assetTypes[$assetType][$securityId]['sharesOwned'];
            }
            $assetTypes[$assetType][$securityId] = [
                'asset' => $assetType,
                'description' => $security->getName(),
                'symbol' => $security->getSymbol(),
                'sharesOwned' => $sharesOwned, //count of securities owned by client

                'currentValue' => $currentValue,
                'costBasis' => $costBasis,
                'weight' => $currentValue / $portfolioValue,
                'unrealizedGain' => $currentValue - $costBasis,
                'unrealizedGainPercent' => ($currentValue - $costBasis) / $currentValue,
            ];
        }

        $sumAllWeight = 0;
        $sumAllCurrentValue = 0;
        $sumAllCostBasis = 0;
        $sumAllGainLoss = 0;
        $sumAllGainLossPercent = 0;

        $parameters['assetTypesSumm'] = [];

        foreach ($assetTypes as $assetType => $assetData) {
            $sumWeight = 0;
            $sumCurrentValue = 0;
            $sumCostBasis = 0;
            $sumGainLoss = 0;
            $sumGainLossPercent = 0;

            foreach ($assetData as $asset) {
                $sumWeight += $asset['weight'];
                $sumCurrentValue += $asset['currentValue'];
                $sumCostBasis += $asset['costBasis'];
                $sumGainLoss += $asset['unrealizedGain'];
                $sumGainLossPercent += $asset['unrealizedGainPercent'];
            }

            $parameters['assetTypesSumm'][$assetType] = [
                'sumWeight' => $sumWeight,
                'sumCurrentValue' => $sumCurrentValue,
                'sumCostBasis' => $sumCostBasis,
                'sumGainLoss' => $sumGainLoss,
                'sumGainLossPercent' => $sumGainLossPercent,
            ];

            $sumAllWeight += $sumWeight;
            $sumAllCurrentValue += $sumCurrentValue;
            $sumAllCostBasis += $sumCostBasis;
            $sumAllGainLoss += $sumGainLoss;
            $sumAllGainLossPercent += $sumGainLossPercent;
        }

        $parameters['total'] = [
            'sumAllWeight' => $sumAllWeight,
            'sumAllCurrentValue' => $sumAllCurrentValue,
            'sumAllCostBasis' => $sumAllCostBasis,
            'sumAllGainLoss' => $sumAllGainLoss,
            'sumAllGainLossPercent' => $sumAllGainLossPercent,
        ];

        $parameters['assetTypes'] = $assetTypes;

        return $this->prepareResponse($request, 'holdings', null, $parameters);
    }

    public function gainslossesAction(Request $request)
    {
        $params = [];
        $totalNetProceeds = 0;
        $totalCost = 0;
        $longTermGainLoss = 0;
        $shortTermGainLoss = 0;
        $totalGainLoss = 0;
        $sort = $request->query->get('sort', 'securities.name');
        $direction = $request->query->get('direction', 'ASC');

        $client = $this->getUser();

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $em->getConfiguration()->addCustomDatetimeFunction('YEAR', 'Wealthbot\MailerBundle\DQL\DatetimeFunction\Year');
        $systemAccountManager = $this->get('wealthbot_client.system_account_manager');

        /** @var \Wealthbot\ClientBundle\Repository\ClientSettings $repository */
        $repository = $em->getRepository('WealthbotClientBundle:ClientSettings');
        /** @var \Wealthbot\ClientBundle\Repository\PositionRepository $positionsRepo */
        $positionsRepo = $em->getRepository('WealthbotClientBundle:Position');
        /** @var \Wealthbot\ClientBundle\Repository\LotRepository $lotsRepo */
        $lotsRepo = $em->getRepository('WealthbotClientBundle:Lot');

        $isClientView = $this->isRiaClientView();
        if ($isClientView) {
            $client = $this->getUser();
            $clientSettings = $repository->findOneBy(['client' => $client]);
            if (!$clientSettings) {
                $clientSettings = new ClientSettings();
                $clientSettings->setClient($client);
            }

            $stopTLHValueForm = $this->createForm(new ClientStopTLHValueFormType(), $clientSettings);
            $params = ['stop_tlh_form' => $stopTLHValueForm->createView()];

            if ($request->isMethod('post')) {
                $stopTLHValueForm->handleRequest($request);
                if ($stopTLHValueForm->isValid()) {
                    $em->persist($clientSettings);
                    $em->flush();

                    return $this->getJsonResponse(['status' => 'success']);
                } else {
                    $content = $this->renderView(
                        'WealthbotClientBundle:Dashboard:gainlosses_stop_tlh_form.html.twig',
                        ['form' => $stopTLHValueForm->createView()]
                    );

                    return $this->getJsonResponse(['status' => 'error', 'content' => $content]);
                }
            }
        }

        if ($request->isMethod('get')) {
            $isClientView = !$this->isRiaClientView();
            $activeClientAccounts = $systemAccountManager->getAccountsForClient($client, $isClientView);
            $accounts = $activeClientAccounts;
            if ($accountId = $request->get('account_id')) {
                if ($account = $em->getRepository('WealthbotClientBundle:ClientAccount')->findBy(['id' => $accountId, 'client' => $client])) {
                    /** @var ClientAccount $account */
                    if ($systemAccount = $account->getSystemAccount()) {
                        $accounts = [$systemAccount];
                    }
                }
            }

            $gainLossYears = $positionsRepo->getGainLossYears($accounts);
            $year = $request->get('year');
            if (empty($year)) {
                if (isset($gainLossYears[0])) {
                    $year = $gainLossYears[0]['year'];
                }
            }

            $lots = $lotsRepo->getRealizedLots($year, $accounts, $sort, $direction);

            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate(
                $lots,
                $request->get('page', 1),
                10
            );

            foreach ($pagination as $gainLoss) {
                $totalNetProceeds += $gainLoss->getAmount();
                $totalCost += $gainLoss->getInitial()->getAmount();
                $longTermGainLoss += $gainLoss->getLongTermGain();
                $shortTermGainLoss += $gainLoss->getShortTermGain();
                $totalGainLoss += $gainLoss->getRealizedGain();
            }

            $params = array_merge($params, [
                'pagination' => $pagination,
                'gainLossYears' => $gainLossYears,
                'lots' => $lots,
                'totalNetProceeds' => $totalNetProceeds,
                'totalCost' => $totalCost,
                'longTermGainLoss' => $longTermGainLoss,
                'shortTermGainLoss' => $shortTermGainLoss,
                'totalGainLoss' => $totalGainLoss,
            ]);
        }

        return $this->prepareResponse($request, 'gainslosses', 'Gains/Losses', $params);
    }

    public function transactionsAction(Request $request)
    {
        $account_id = $request->query->get('account_id');
        $sort = $request->query->get('sort', 'transactions.txDate');
        $direction = $request->query->get('direction', 'DESC');

        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WealthbotAdminBundle:Transaction');
        $paginator = $this->get('knp_paginator');

        $transactions = $repository->findByClientIdAndAccountId($this->getUser()->getId(), $sort, $direction, $account_id);
        $pagination = $paginator->paginate(
            $transactions,
            $request->get('page', 1),
            10
        );
        $params = ['pagination' => $pagination];

        return $this->prepareResponse($request, 'transactions', 'Transactions', $params);
    }

    protected function getBillingData(Request $request)
    {
        $now = new \DateTime();
        $year = $now->format('Y');
        $quarter = ceil($now->format('m') / 3);

        $year = $request->get('year', $year);
        $quarter = $request->get('quarter', $quarter);

        $em = $this->get('doctrine.orm.entity_manager');
        $feeManager = $this->get('wealthbot.manager.fee');
        $cashManager = $this->get('wealthbot_client.cash.manager');
        $infoManager = $this->get('wealthbot_ria.summary_information.manager');
        $periodManager = $this->get('wealthbot_ria.period.manager');

        $client = $this->getUser();
        $period = $periodManager->getPeriod($year, $quarter);

        $billingSpec = $client->getAppointedBillingSpec();
        $isBillingSpecTier = ($billingSpec->getType() === BillingSpec::TYPE_TIER);

        if ($isBillingSpecTier) {
            $fees = $client->getAppointedBillingSpec()->getFees();
        }

        $accounts = $em->getRepository('WealthbotClientBundle:ClientAccount')->findByClient($client);

        $billTotal = $averageTotal = $value = 0;
        $clientAccounts = $tiers = [];

        foreach ($accounts as $account) {
            $systemAccount = $account->getSystemAccount();
            $data = [
                'id' => $account->getId(),
                'name' => $account->getOwnerNames(),
                'number' => !empty($systemAccount) ? $systemAccount->getAccountNumber() : '',
                'averageValue' => $infoManager->getAccountAverageValue($account, $period['startDate'], $period['endDate']),
                'billAmount' => $infoManager->getAccountFeeBilled($account, $year, $quarter),
            ];

            $billTotal += $data['billAmount'];
            $averageTotal += $data['averageValue'];

            if ($isBillingSpecTier) {
                $value = $cashManager->getAccountValueOnDate($account, $period['endDate']);
                $tiers[] = $feeManager->getCalculationTiers($value, $fees);
            }

            $clientAccounts[] = $data;
        }

        $tierMap = [];
        if ($isBillingSpecTier) {
            foreach ($tiers as $tier) {
                $start = 0;
                foreach ($tier as $sort) {
                    $data = [];
                    $data['tier_top'] = $sort['tier_top'];
                    $data['tier_bottom'] = $start;
                    $data['fee'] = $sort['fee'];
                    $data['fee_amount'] = $sort['fee_amount'];

                    $key = (string) $sort['fee'];
                    if (array_key_exists($key, $tierMap)) {
                        $tierMap[$key]['fee_amount'] += $sort['fee_amount'];
                    } else {
                        $tierMap[$key] = $data;
                    }

                    $start = $sort['tier_top'] + 0.01;
                }
            }
        }

        $date = new \DateTime();
        $years = range($client->getCreated()->format('Y'), $date->format('Y'));

        return [
            'curYear' => $year,
            'curQuarter' => $quarter,
            'years' => $years,
            'endDate' => $period['endDate']->modify('-1 day')->format('m/d/Y'),
            'accounts' => $clientAccounts,
            'billTotal' => $billTotal,
            'averageTotal' => $averageTotal,
            'tiers' => $tierMap,
            'isBillingSpecTier' => $isBillingSpecTier,
        ];
    }

    public function billingPeriodAction(Request $request)
    {
        $params = $this->getBillingData($request);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_billing_data.html.twig', $params),
        ]);
    }

    public function billingAction(Request $request)
    {
        $params = $this->getBillingData($request);

        return $this->prepareResponse($request, 'billing', 'Billing', $params);
    }

    protected function getPerformanceData(Request $request)
    {
        $client = $this->getUser();
        $accountId = $request->query->get('account_id');

        $period = $request->get('period', 1);
        $periodManager = $this->get('wealthbot_ria.period.manager');

        $twrCalculatorManager = $this->get('wealthbot.manager.twr_calculator');
        $twrCalculatorManager->setClient($client);
        if ($accountId) {
            $account = $this
                ->get('doctrine.orm.entity_manager')
                ->getRepository('WealthbotClientBundle:SystemAccount')
                ->findOneBy([
                    'client' => $client,
                    'client_account_id' => $accountId,
                ])
            ;
            $twrCalculatorManager->setAccount($account);
        }
        $twrCalculatorManager->loadTwrData();

        $curDate = new \DateTime('now');

        switch ($period) {
            case 2:
                $fromDate = $periodManager->firstDayOf('quarter');
                break;
            case 3:
                $fromDate = $periodManager->firstDayOf('year');
                break;
            case 4:
                $date = clone $curDate;
                $fromDate = $date->modify('-1 year');
                break;
            case 5:
                $date = clone $curDate;
                $fromDate = $date->modify('-3 year');
                break;
            case 6:
                $fromDate = $twrCalculatorManager->getBillingInceptionDate();
                break;
            default:
                $fromDate = $periodManager->firstDayOf('month');
                break;
        }

        $twrCalculatorManager->setPeriod($period);
        $twrCalculatorManager->setStartDate($fromDate);
        $twrCalculatorManager->setEndDate($curDate);

        $performance = new \stdClass();
        $performance->beginningValue = $twrCalculatorManager->getBeginningValue();
        $performance->contributions = $twrCalculatorManager->getContributions();
        $performance->withdrawals = $twrCalculatorManager->getWithdrawals();
        $performance->endingValue = $twrCalculatorManager->getEndingValue();
        $performance->investmentGain = $twrCalculatorManager->getInvestmentGain();
        $performance->netActual = $twrCalculatorManager->getNetActual();
        $performance->netAnnualized = $twrCalculatorManager->getNetAnnualized();
        $performance->grossActual = $twrCalculatorManager->getGrossActual();
        $performance->grossAnnualized = $twrCalculatorManager->getGrossAnnualized();

        return ['performance' => $performance];
    }

    public function performancePeriodAction(Request $request)
    {
        $params = $this->getPerformanceData($request);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_performance_data.html.twig', $params),
        ]);
    }

    public function performanceAction(Request $request)
    {
        $params = $this->getPerformanceData($request);

        return $this->prepareResponse($request, 'performance', 'Performance', $params);
    }

    public function activityAction(Request $request)
    {
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $paginator = $this->get('knp_paginator');

        $client = $this->getUser();

        $qb = $dm->getRepository('WealthbotClientBundle:Activity')->createQueryBuilder();
        $qb->field('clientUserId')->equals($client->getId());

        $pagination = $paginator->paginate($qb->getQuery(), $request->get('page', 1), 10);
        $params = ['pagination' => $pagination];

        return $this->prepareResponse($request, 'activity', 'Activity', $params);
    }

    public function documentsAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');
        $paginator = $this->get('knp_paginator');
        $mailer = $this->get('wealthbot.mailer');
        $session = $this->get('session');

        /** @var User $client */
        $client = $this->getUser();
        $ria = $client->getRia();

        $riaDocuments = [
            'investment_management_agreement' => $documentManager->getUserDocumentByType($ria->getId(), Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT),
            'adv' => $documentManager->getUserDocumentByType($ria->getId(), Document::TYPE_ADV),
        ];

        $clientDocumentUploadForm = $this->createForm(new ClientDocumentFormType($this->isRiaClientView()));

        if ($request->isMethod('post')) {
            $clientDocumentFormHandler = new ClientDocumentFormHandler(
                $clientDocumentUploadForm,
                $request,
                $em,
                $mailer,
                [
                    'user' => $client,
                    'is_ria_client_view' => $this->isRiaClientView(),
                ]
            );

            $isValid = $clientDocumentFormHandler->process();

            if ($isValid) {
                $session->getFlashBag()->add('success', 'Document Upload');
            }
        }

        $pagination = $paginator->paginate(
            $documentManager->getUserDocumentSorted($client->getId(), $request->get('sort'), $request->get('direction')),
            $request->get('page', 1),
            3
        );

        $params = [
            'ria_documents' => $riaDocuments,
            'document_upload_form' => $clientDocumentUploadForm->createView(),
            'pagination' => $pagination,
        ];

        return $this->prepareResponse($request, 'documents', 'Documents', $params);
    }

    public function deleteDocumentAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        /** @var User $user */
        $user = $this->getUser();
        $document = $em->getRepository('WealthbotUserBundle:Document')->find($request->get('document_id'));

        $isUserDocument = $documentManager->isUserDocument($document->getId(), $user->getId());

        if (!$document || ($document && ($document->getType() !== Document::TYPE_ANY || !$isUserDocument || $document->isApplication()))) {
            throw $this->createNotFoundException();
        }

        $user->removeUserDocument($document);

        $em->persist($user);
        $em->flush();

        return $this->redirect($this->generateUrl('wealthbot_client_documents'));
    }

    public function accountManagementAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo SystemAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');

        $client = $this->getUser();

        $retirementAccounts = $repo->findRetirementByClientId($client->getId());
        $beneficiariesAccounts = $repo->findWithBeneficiariesByClientId($client->getId());
        $contributionDistributionAccounts = $repo->findContributionDistributionAccounts($client);
        $allAccounts = $repo->findByClientId($client->getId());
        $bankAccounts = $em->getRepository('WealthbotClientBundle:BankInformation')->findBy(['client_id' => $client->getId()]);

        $bankForm = $this->createForm(new BankInformationFormType());

        $parameters = [
            'retirement_accounts' => $retirementAccounts,
            'beneficiaries_accounts' => $beneficiariesAccounts,
            'contribution_distribution_accounts' => $contributionDistributionAccounts,
            'all_accounts' => $allAccounts,
            'bank_accounts' => $bankAccounts,
            'bank_info_form' => $this->renderView('WealthbotClientBundle:Dashboard:_bank_account_form.html.twig', [
                'form' => $bankForm->createView(),
            ]),
            'client' => $client,
            'is_ria_client_view' => $this->isRiaClientView(),
        ];

        $changeProfileTab = $request->get('active_tab', null);
        if ($changeProfileTab) {
            if ($changeProfileTab === 'update_password') {
                $parameters['ajax_url'] = $this->generateUrl('rx_client_change_profile_change_password');
            } elseif ($changeProfileTab === 'your_portfolio') {
                $parameters['ajax_url'] = $this->generateUrl('rx_client_change_profile_your_portfolio', ['data_type' => 'json']);
            }
        }

        return $this->prepareResponse($request, 'account_management', 'Account Management', $parameters);
    }

    public function editRetirementAccountInfoAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo SystemAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');

        $user = $this->getUser();

        /** @var $account SystemAccount */
        $account = $repo->findOneRetirementAccountById($request->get('account_id'));
        if (!$account || $account->getClientId() !== $user->getId()) {
            throw $this->createNotFoundException(
                sprintf('You have not retirement account with id: %s.', $account->getId())
            );
        }

        $clientAccount = $account->getClientAccount();
        $accountInfo = $clientAccount->getRetirementPlanInfo();
        $form = $this->createForm(new DashboardRetirementPlanInfoFormType(), $accountInfo);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $accountInfo = $form->getData();

                $em->persist($accountInfo);
                $em->flush();

                $this->dispatchHistoryEvent($user, 'Updated retirement account information');

                return $this->getJsonResponse([
                    'status' => 'success',
                    'message' => 'Account information has been successfully changed.',
                    'account_title' => $accountInfo->getAccountNumber().' ['.$accountInfo->getAccountDescription().']',
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'content' => $this->renderView('WealthbotClientBundle:Dashboard:_retirement_account_info_form.html.twig', [
                        'form' => $form->createView(),
                        'account' => $account,
                    ]),
                ]);
            }
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_retirement_account_info.html.twig', [
                'form' => $form->createView(),
                'account' => $account,
            ]),
        ]);
    }

    public function accountBeneficiariesAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo SystemAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');

        $client = $this->getUser();

        /** @var $account SystemAccount */
        $account = $repo->find($request->get('account_id'));
        if (!$account || $account->getClientId() !== $client->getId()) {
            throw $this->createNotFoundException(sprintf('You have not account with id: %s.', $account->getId()));
        }

        $clientAccount = $account->getClientAccount();
        $beneficiaries = $clientAccount->getBeneficiaries();

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_account_beneficiaries.html.twig', [
                'account' => $account,
                'beneficiaries' => $beneficiaries,
            ]),
        ]);
    }

    public function addBeneficiaryAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo SystemAccountRepository */
        /* @var $beneficiaryRepo BeneficiaryRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');
        $beneficiaryRepo = $em->getRepository('WealthbotClientBundle:Beneficiary');

        $signatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $client = $this->getUser();

        /** @var $account SystemAccount */
        $account = $repo->find($request->get('account_id'));
        if (!$account || $account->getClientId() !== $client->getId()) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => sprintf('You have not account id: %s.', $account->getId()),
            ]);
        }

        $clientAccount = $account->getClientAccount();

        $beneficiary = new Beneficiary();
        $beneficiary->setAccount($clientAccount);

        $form = $this->createForm(new BeneficiaryFormType(false, true), $beneficiary);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var Beneficiary $beneficiary */
                $beneficiary = $form->getData();
                $shareSum = $beneficiaryRepo->getBeneficiariesShareForAccount($clientAccount, $beneficiary->getType());

                if ((round($beneficiary->getShare()) + $shareSum) > 100) {
                    $form->get('share')->addError(new FormError('Beneficiary share can not be more then 100%.'));
                } else {
                    $em->persist($beneficiary);
                    $em->flush();

                    $this->get('wealthbot_docusign.document_signature.manager')->createSignature($beneficiary);

                    $event = new WorkflowEvent($client, $beneficiary, Workflow::TYPE_PAPERWORK);
                    $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                    $this->dispatchHistoryEvent($client, 'Created beneficiary');

                    return $this->getJsonResponse([
                        'status' => 'success',
                        'form' => $this->renderView('WealthbotClientBundle:Dashboard:_beneficiaries_sign.html.twig', [
                            'signatures' => $signatureManager->findChangeBeneficiaryByClientAccount($clientAccount),
                            'account' => $account,
                        ]),
                        'content' => $this->renderView('WealthbotClientBundle:Dashboard:_beneficiary_row.html.twig', [
                            'account' => $account,
                            'beneficiary' => $beneficiary,
                        ]),
                    ]);
                }
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'content' => $this->renderView('WealthbotClientBundle:Dashboard:_beneficiary_form.html.twig', [
                    'form' => $form->createView(),
                    'account' => $account,
                ]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_beneficiary_form.html.twig', [
                'form' => $form->createView(),
                'account' => $account,
            ]),
        ]);
    }

    public function editBeneficiaryAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo SystemAccountRepository */
        /* @var $beneficiaryRepo BeneficiaryRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');
        $beneficiaryRepo = $em->getRepository('WealthbotClientBundle:Beneficiary');

        $signatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $client = $this->getUser();

        /** @var $account SystemAccount */
        $account = $repo->find($request->get('account_id'));
        if (!$account || $account->getClientId() !== $client->getId()) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => sprintf('You have not account with id: %s.', $account->getId()),
            ]);
        }

        $clientAccount = $account->getClientAccount();

        $beneficiary = $beneficiaryRepo->findOneBy([
            'id' => $request->get('beneficiary_id'),
            'account_id' => $clientAccount->getId(),
        ]);
        if (!$beneficiary) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => sprintf(
                    'Beneficiary with id: %s and account_id: %s does not exist.',
                    $request->get('beneficiary_id'),
                    $account->getId()
                ),
            ]);
        }

        $shareSum = $beneficiaryRepo->getBeneficiariesShareForAccount($clientAccount, $beneficiary->getType());
        $shareSum -= round($beneficiary->getShare(), 2);

        $form = $this->createForm(new BeneficiaryFormType(false, true), $beneficiary);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $beneficiary = $form->getData();

                if ((round($beneficiary->getShare()) + $shareSum) > 100) {
                    $form->get('share')->addError(new FormError('Beneficiary share can not be more then 100%.'));
                } else {
                    $em->persist($beneficiary);
                    $em->flush();

                    $existSignature = $signatureManager->findChangeBeneficiaryCreatedByClientAccount($clientAccount);
                    if (null === $existSignature) {
                        $signatureManager->createSignature($beneficiary);

                        $event = new WorkflowEvent($client, $beneficiary, Workflow::TYPE_PAPERWORK);
                        $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);
                    }

                    $this->dispatchHistoryEvent($client, 'Updated beneficiary');

                    return $this->getJsonResponse([
                        'status' => 'success',
                        'beneficiary_id' => $beneficiary->getId(),
                        'form' => $this->renderView('WealthbotClientBundle:Dashboard:_beneficiaries_sign.html.twig', [
                            'signatures' => $signatureManager->findChangeBeneficiaryByClientAccount($clientAccount),
                            'account' => $account,
                        ]),
                        'content' => $this->renderView('WealthbotClientBundle:Dashboard:_beneficiary_row.html.twig', [
                            'account' => $account,
                            'beneficiary' => $beneficiary,
                        ]),
                    ]);
                }
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'content' => $this->renderView('WealthbotClientBundle:Dashboard:_beneficiary_form.html.twig', [
                    'form' => $form->createView(),
                    'account' => $account,
                ]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_beneficiary_form.html.twig', [
                'form' => $form->createView(),
                'account' => $account,
            ]),
        ]);
    }

    public function beneficiariesSignAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $signatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');

        $status = 'success';
        $message = 'The operation was successful.';
        $content = null;

        $account = $repo->find($request->get('account_id'));
        $signatures = $signatureManager->findChangeBeneficiaryByClientAccount($account->getClientAccount());

        if ($request->isMethod('post')) {
            foreach ($signatures as $signature) {
                if (!$signature->isCompleted()) {
                    $status = 'error';
                    $message = 'You have not signed applications. Please sign all applications.';
                    break;
                }
            }
        } else {
            $status = 'error';
            $message = 'You have not signed applications. Please sign all applications.';
        }

        $data = ['status' => $status, 'message' => $message];
        if ($status === 'error') {
            $data['content'] = $this->renderView('WealthbotClientBundle:Dashboard:_beneficiaries_sign.html.twig', [
                'signatures' => $signatures,
                'account' => $account,
            ]);
        }

        return $this->getJsonResponse($data);
    }

    public function deleteBeneficiaryAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo SystemAccountRepository */
        /* @var $beneficiaryRepo BeneficiaryRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');
        $beneficiaryRepo = $em->getRepository('WealthbotClientBundle:Beneficiary');

        $client = $this->getUser();

        /** @var $account SystemAccount */
        $account = $repo->find($request->get('account_id'));
        if (!$account || $account->getClientId() !== $client->getId()) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => sprintf('You have not account with id: %s.', $account->getId()),
            ]);
        }

        $clientAccount = $account->getClientAccount();

        $beneficiary = $beneficiaryRepo->findOneBy([
            'id' => $request->get('beneficiary_id'),
            'account_id' => $clientAccount->getId(),
        ]);
        if (!$beneficiary) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => sprintf(
                    'Beneficiary with id: %s and account_id: %s does not exist.',
                    $request->get('beneficiary_id'),
                    $account->getId()
                ),
            ]);
        }

        $em->remove($beneficiary);
        $em->flush();

        $this->dispatchHistoryEvent($client, 'Deleted beneficiary');

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function newAccountAction()
    {
        $client = $this->getUser();
        $riaCompanyInformation = $client->getRia()->getRiaCompanyInformation();

        $data = ['groups' => $this->get('session')->get('client.accounts.groups')];
        $this->get('session')->set('client.accounts.is_consolidate_account', false);

        $form = $this->createForm(new AccountGroupsFormType($client), $data);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_new_account.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
                'ria_company_information' => $riaCompanyInformation,
            ]),
        ]);
    }

    public function editAddressAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $client = $this->getUser();
        $profile = $client->getProfile();

        $form = $this->createForm(new ClientAddressFormType(), $profile);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $profile = $form->getData();

                $em->persist($profile);
                $em->flush($profile);

                $event = new WorkflowEvent($client, $profile, Workflow::TYPE_PAPERWORK);
                $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                $this->dispatchHistoryEvent($client, 'Updated address');

                return $this->getJsonResponse([
                    'status' => 'success',
                    'message' => 'Address has been changed successfully.',
                ]);
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'content' => $this->renderView('WealthbotClientBundle:Dashboard:_client_address_form.html.twig', [
                    'form' => $form->createView(),
                ]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_client_address_form.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function accountContributionAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');
        $custodianMessageRepo = $em->getRepository('WealthbotAdminBundle:CustodianMessage');

        $client = $this->getUser();
        $riaCompanyInfo = $client->getRia()->getRiaCompanyInformation();

        /** @var $account SystemAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        if (!$account) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => sprintf('You have not account with id: %s.', $account->getId()),
            ]);
        }

        $status = 'success';
        $action = $request->get('type');

        try {
            /** @var $contributionFormFactory AccountContributionFormFactory */
            $contributionFormFactory = $this->get('wealthbot_client.contribution_form_factory');
            $form = $contributionFormFactory->create($action, $account);
        } catch (\Exception $e) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $contribution = $form->getData();

                $em->persist($contribution);
                $em->flush();

                $signature = $documentSignatureManager->createSignature($contribution);

                $event = new WorkflowEvent($client, $contribution, Workflow::TYPE_PAPERWORK);
                $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                $this->dispatchHistoryEvent($client, 'Updated contribution');

                return $this->getJsonResponse([
                    'status' => 'success',
                    'message' => 'The operation was successful.',
                    'content' => $this->renderView('WealthbotClientBundle:DashboardTransfer:contribution_sign.html.twig', [
                        'signature' => $signature,
                        'action' => $action,
                    ]),
                ]);
            }

            $status = 'error';
        }

        return $this->getJsonResponse([
            'status' => $status,
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_account_contribution_form.html.twig', [
                'form' => $form->createView(),
                'account' => $account,
                'type' => $action,
                'messages' => $custodianMessageRepo->getAssocByCustodianId($riaCompanyInfo->getCustodianId()),
            ]),
        ]);
    }

    public function accountContributionActionSignAction(Request $request)
    {
        $signatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $status = 'error';
        $message = 'You have not signed applications. Please sign all applications.';
        $content = null;

        if ($request->isMethod('post')) {
            $signature = $signatureManager->findActiveDocumentSignature($request->get('signature_id'));
            if (!$signature) {
                $message = 'Signature does not exist.';
            } elseif ($signature->isCompleted()) {
                $status = 'success';
                $message = 'The operation was successful.';
            } else {
                if ($signature->getType() === DocumentSignature::TYPE_ONE_TIME_CONTRIBUTION) {
                    $action = 'one_time';
                } else {
                    $exist = $signatureManager->findOneDocumentSignatureBySourceIdAndType(
                        $signature->getSourceId(),
                        DocumentSignature::TYPE_AUTO_INVEST_CONTRIBUTION,
                        false
                    );
                    $action = $exist ? 'update' : 'create';
                }

                $content = $this->renderView('WealthbotClientBundle:DashboardTransfer:contribution_sign.html.twig', [
                    'signature' => $signature,
                    'action' => $action,
                ]);
            }
        }

        $data = ['status' => $status, 'message' => $message];
        if (null !== $content) {
            $data['content'] = $content;
        }

        return $this->getJsonResponse($data);
    }

    public function accountDistributionAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo SystemAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:SystemAccount');

        $documentSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $client = $this->getUser();

        /** @var $account SystemAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        if (!$account) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => sprintf('You have not account with id: %s.', $account->getId()),
            ]);
        }

        $type = $request->get('type');

        try {
            /** @var $formFactory DistributionFormFactory */
            $formFactory = $this->get('wealthbot_client.distribution_form_factory');
            /** @var Form $form */
            $form = $formFactory->create($type, $account);
        } catch (\Exception $e) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        }

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $distribution = $form->getData();

                $em->persist($distribution);
                $em->flush();

                $signature = $documentSignatureManager->createSignature($distribution);

                $event = new WorkflowEvent($client, $distribution, Workflow::TYPE_PAPERWORK);
                $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                $this->dispatchHistoryEvent($client, 'Updated distribution');

                return $this->getJsonResponse([
                    'status' => 'success',
                    'message' => 'Please allow 3-5 business days for your request to be processed.',
                    'content' => $this->renderView('WealthbotClientBundle:DashboardTransfer:distribution_sign.html.twig', [
                        'signature' => $signature,
                        'action' => $type,
                    ]),
                ]);
            }

            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'The operation failed due to some errors.',
                'content' => $this->renderView('WealthbotClientBundle:Dashboard:_account_distribution_form.html.twig', [
                    'form' => $form->createView(),
                    'account' => $account,
                    'type' => $type,
                ]),
            ]);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'type' => $type,
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_account_distribution_form.html.twig', [
                'form' => $form->createView(),
                'account' => $account,
                'type' => $type,
            ]),
        ]);
    }

    public function accountDistributionActionSignAction(Request $request)
    {
        $signatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $status = 'error';
        $message = 'You have not signed applications. Please sign all applications.';
        $content = null;

        if ($request->isMethod('post')) {
            $signature = $signatureManager->findActiveDocumentSignature($request->get('signature_id'));
            if (!$signature) {
                $message = 'Signature does not exist.';
            } elseif ($signature->isCompleted()) {
                $status = 'success';
                $message = 'The operation was successful.';
            } else {
                if ($signature->getType() === DocumentSignature::TYPE_ONE_TIME_DISTRIBUTION) {
                    $action = 'one_time';
                } else {
                    $exist = $signatureManager->findOneDocumentSignatureBySourceIdAndType(
                        $signature->getSourceId(),
                        DocumentSignature::TYPE_AUTO_DISTRIBUTION,
                        false
                    );
                    $action = $exist ? 'update' : 'create';
                }

                $content = $this->renderView('WealthbotClientBundle:DashboardTransfer:distribution_sign.html.twig', [
                    'signature' => $signature,
                    'action' => $action,
                ]);
            }
        }

        $data = ['status' => $status, 'message' => $message];
        if (null !== $content) {
            $data['content'] = $content;
        }

        return $this->getJsonResponse($data);
    }

    public function changePortfolioAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();

        $form = $this->createForm(new ClientQuestionsFormType($em, $user));
        $status = 'success';

        if ($request->isMethod('post')) {
            $dm = $this->get('doctrine_mongodb.odm.document_manager');
            $formHandler = new ClientTempQuestionsFormHandler($form, $request, $em, $dm);

            if ($formHandler->process($user)) {
                return $this->redirect($this->generateUrl('rx_client_dashboard_approve_portfolio'));
            } else {
                $status = 'error';
            }
        }

        return $this->getJsonResponse([
            'status' => $status,
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_change_portfolio.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function approvePortfolioAction(Request $request)
    {
        /** @var $client User */
        $client = $this->getUser();
        $ria = $client->getRia();

        $dm = $this->get('doctrine_mongodb.odm.document_manager');

        /** @var TempPortfolio $tmpPortfolio */
        $tmpPortfolio = $dm->getRepository('WealthbotClientBundle:TempPortfolio')->findOneByClientUserId($client->getId());
        if (!$tmpPortfolio || !$tmpPortfolio->getModelId()) {
            throw $this->createNotFoundException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $accountsRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        /** @var RiaCompanyInformation $companyInformation */
        $companyInformation = $ria->getRiaCompanyInformation();
        $portfolioInformationManager = $this->get('wealthbot_client.portfolio_information_manager');

        $isUseQualified = $companyInformation->getIsUseQualifiedModels();
        $isQualified = false;

        if ($isUseQualified) {
            $isQualified = (bool) $request->get('is_qualified');
        }

        $portfolio = $em->getRepository('WealthbotAdminBundle:CeModel')->find($tmpPortfolio->getModelId());
        $portfolioInformation = $portfolioInformationManager->getPortfolioInformation($client, $portfolio, $isQualified);

        if ($request->isMethod('post')) {
            $tmpPortfolio->setStatus(TempPortfolio::STATUS_APPROVED);

            $dm->persist($tmpPortfolio);
            $dm->flush();

            return $this->render('WealthbotClientBundle:Dashboard:approve_portfolio.html.twig', [
                'is_approved' => true,
            ]);
        }

        $clientAccounts = $accountsRepo->findConsolidatedAccountsByClientId($client->getId());
        $retirementAccounts = $accountsRepo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);
        $form = $this->createFormBuilder()->add('name', 'text')->getForm();

        return $this->render('WealthbotClientBundle:Dashboard:approve_portfolio.html.twig', [
            'client' => $client,
            'client_accounts' => $clientAccounts,
            'total' => $accountsRepo->getTotalScoreByClientId($client->getId()),
            'ria_company_information' => $companyInformation,
            'has_retirement_account' => count($retirementAccounts) ? true : false,
            'portfolio_information' => $portfolioInformation,
            'show_sas_cash' => $accountsRepo->containsSasCash($clientAccounts),
            'is_approved' => $tmpPortfolio->isApproved(),
            'is_use_qualified_models' => $isUseQualified,
            'form' => $form->createView(),
            'signing_date' => new \DateTime('now'),
            'action' => 'client_approve_portfolio',
            'approve_url' => 'rx_client_dashboard_approve_portfolio',
        ]);
    }

    public function closeAccountsAction(Request $request)
    {
        /** @var EntityManager $em */
        /* @var TwigSwiftMailer $mailer */
        $em = $this->get('doctrine.orm.entity_manager');
        $mailer = $this->get('wealthbot.mailer');

        $client = $this->getUser();

        $form = $this->createForm(new CloseAccountsFormType());
        $formHandler = new CloseAccountsFormHandler($form, $request, $em, $mailer);

        if ($request->isMethod('post')) {
            try {
                $process = $formHandler->process($client);
                if ($process) {
                    $closedAccountsHistory = $formHandler->getSavedObjects();
                    $ids = [];
                    foreach ($closedAccountsHistory as $item) {
                        $ids[] = $item->getId();
                    }

                    $event = new WorkflowEvent($client, new ClosingAccountHistory(), Workflow::TYPE_ALERT, null, $ids);
                    $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                    $this->dispatchHistoryEvent($client, 'Closed account');

                    return $this->getJsonResponse([
                        'status' => 'success',
                        'message' => 'The operation was successful.',
                    ]);
                } else {
                    return $this->getJsonResponse([
                        'status' => 'error',
                        'message' => 'The operation failed due to some errors.',
                        'content' => $this->renderView('WealthbotClientBundle:Dashboard:_close_accounts_form.html.twig', [
                            'form' => $form->createView(),
                            'client' => $client,
                        ]),
                    ]);
                }
            } catch (\Exception $e) {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Dashboard:_close_accounts_form.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
            ]),
        ]);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }

    private function prepareResponse(Request $request, $tab, $action = null, array $params = [])
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if (null === $action) {
            $action = ucfirst($tab);
        }

        $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $isClientView = !$this->isRiaClientView();
        /** @var User $client */
        $client = $this->getUser();

        $isAjax = $request->isXmlHttpRequest();

        $isClearLayout = $request->get('is_clear_layout');
        $isClearLayout = $isClearLayout ? $isClearLayout : $isAjax;

        $activeClientAccounts = $systemAccountManager->getAccountsForClient($client, $isClientView);

        $parameters = [
            'layout_variables' => $this->getLayoutVariables($action, 'wealthbot_client_'.$tab),
            'is_ajax' => $isAjax,
            'is_clear_layout' => $isClearLayout,
            'accounts' => $activeClientAccounts,
            'client' => $client,
            'is_client_view' => $isClientView,
            'client_portfolio' => $clientPortfolioManager->getCurrentPortfolio($client),
            'client_created_at' => $client->getCreated()->format('Y-m-d h:i:s A'),
        ];

        if (!empty($params)) {
            $parameters = array_merge($parameters, $params);
        }

        if ($isAjax) {
            return $this->getJsonResponse([
                'status' => 'success',
                'active_tab' => $tab,
                'content' => $this->renderView('WealthbotClientBundle:Dashboard:'.$tab.'.html.twig', $parameters),
            ]);
        }

        return $this->render('WealthbotClientBundle:Dashboard:'.$tab.'.html.twig', $parameters);
    }

    private function getLayoutVariables($action)
    {
        $variables = [
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
