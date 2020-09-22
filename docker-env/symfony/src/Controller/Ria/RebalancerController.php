<?php

namespace App\Controller\Ria;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Job;
use App\Entity\RebalancerAction;
use App\Repository\JobRepository;
use App\Entity\ClientAccountValue;
use App\Entity\ClientPortfolioValue;
use App\Entity\RebalancerQueue;
use App\Entity\SystemAccount;
use App\Entity\Workflow;
use App\Repository\RebalancerQueueRepository;
use App\Entity\RiaCompanyInformation;
use App\Form\Handler\RebalanceHistoryFilterFormHandler;
use App\Form\Type\ClientSasCashCollectionFormType;
use App\Form\Type\RebalanceFormType;
use App\Form\Type\RebalanceHistoryFilterFormType;
use App\Entity\User;

class RebalancerController extends Controller
{
    public function index(Request $request)
    {
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $paginator = $this->get('knp_paginator');

        /** @var User $ria */
        $ria = $this->getUser();
        $isHouseholdLevel = $ria->getRiaCompanyInformation()->isHouseholdManagedLevel();

        if ($isHouseholdLevel) {
            $clientValues = $clientPortfolioValuesManager->getLatestClientPortfolioValuesForClientsQuery($ria);
        } else {
            $clientValues = $clientAccountValuesManager->getLatestClientAccountValuesForClientsQuery($ria, $clientPortfolioManager);
        }

        /** @var SlidingPagination $clientValuesPagination */
        $clientValuesPagination = $paginator->paginate($clientValues, $request->get('page', 1), 20);
        $clientValuesPagination->setUsedRoute('rx_ria_rebalancing_index');

        $clientValueIds = [];
        foreach ($clientValuesPagination->getItems() as $item) {
            $clientValueIds[$item->getId()] = $item->getId();
        }

        $chooseRebalanceTypeForm = $this->createForm(RebalanceFormType::class, null, ['client_value_ids'=>$clientValueIds, 'is_show_type'=> true]);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Ria/Rebalancer/_rebalance_table.html.twig', [
                    'client_values_pagination' => $clientValuesPagination,
                    'ria' => $ria,
                    'is_history' => false,
                    'form' => $chooseRebalanceTypeForm->createView(),
                ]),
            ]);
        }

        return $this->render('/Ria/Rebalancer/index.html.twig', [
            'ria' => $ria,
            'client_values_pagination' => $clientValuesPagination,
            'form' => $chooseRebalanceTypeForm->createView(),
        ]);
    }

    public function history(Request $request)
    {
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /* @var \Repository\LotRepository $lotRepo */
        $lotRepo = $em->getRepository('App\Entity\Lot');
        $paginator = $this->get('knp_paginator');
        $session = $this->get('session');

        $ria = $this->getUser();
        $isHouseholdLevel = $ria->getRiaCompanyInformation()->isHouseholdManagedLevel();

        $filters = $session->get('rebalance_history_filter', []);

        $historyFilterForm = $this->createForm(RebalanceHistoryFilterFormType::class, $filters);

        if ($request->get('is_filter')) {
            $historyFilterFormHandler = new RebalanceHistoryFilterFormHandler($historyFilterForm, $request, $em, [
                'session' => $session,
            ]);

            if ($historyFilterFormHandler->process()) {
                $filters = $session->get('rebalance_history_filter', []);
            } else {
                $this->json([
                    'status' => 'error',
                    'content' => $this->renderView('/Ria/Rebalancer/_history_filter_form.html.twig', [
                        'history_filter_form' => $historyFilterForm,
                    ]),
                ]);
            }
        }

        if ($isHouseholdLevel) {
            $historyClientValuesQuery = $clientPortfolioValuesManager->getHistoryForRiaClientsQuery($ria, $filters);
        } else {
            $historyClientValuesQuery = $clientAccountValuesManager->getHistoryForRiaClientsQuery($ria, $filters);
        }

        $historyClientValuesPagination = $paginator->paginate($historyClientValuesQuery, $request->get('page', 1), 20);
        $historyClientValuesPagination->setUsedRoute('rx_ria_rebalancing_history');

        if ($isHouseholdLevel) {
            foreach ($historyClientValuesPagination as $historyClientValue) {
                $historyClientValue->setReconciled($lotRepo->isReconciled($historyClientValue->getDate()));
            }
        } else {
            foreach ($historyClientValuesPagination as $historyClientValue) {
                $historyClientValue->setReconciled($lotRepo->isReconciled($historyClientValue->getDate(), $historyClientValue->getSystemClientAccount()));
            }
        }

        $responseData = [
            'client_values_pagination' => $historyClientValuesPagination,
            'ria' => $ria,
            'is_history' => true,
            'history_filter_form' => $historyFilterForm->createView(),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Ria/Rebalancer/_rebalance_table.html.twig', $responseData),
            ]);
        }

        return $this->render('/Ria/Rebalancer/history.html.twig', $responseData);
    }

    public function tradeRecon(Request $request, $client = null, $date = null)
    {
        $date = new \DateTime($date);

        $form = $this->createFormBuilder()
            ->add('client', TextType::class, [
                'attr' => [
                    'autocomplete' => 'off',
                    'class' => 'input-medium ria-find-clients-filter-form-type-search',
                ],
                'data' => $client,
                'label' => 'Client:',
                'required' => false,
            ])
            ->add('date_from', DateType::class, [
                'attr' => [
                    'class' => 'input-small jq-ce-date',
                    'placeholder' => 'MM-DD-YYYY',
                ],
                'data' => $date,
                'format' => 'MM-dd-yyyy',
                'label' => 'Date:',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('date_to', DateType::class, [
                'attr' => [
                    'class' => 'input-small jq-ce-date',
                    'placeholder' => 'MM-DD-YYYY',
                ],
                'data' => $date,
                'format' => 'MM-dd-yyyy',
                'label' => 'to',
                'required' => false,
                'widget' => 'single_text',
            ])
            ->getForm();

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
        }

        $clientName = $form->get('client')->getData();
        $dateFrom = $form->get('date_from')->getData();
        $dateTo = $form->get('date_to')->getData();
        if (empty($dateFrom)) {
            $dateFrom = new \DateTime();
        }
        if (empty($dateTo)) {
            $dateTo = new \DateTime();
        }

        $tradeReconManager = $this->get('wealthbot_admin.trade_recon.manager');

        return $this->render('/Ria/Rebalancer/trade_recon.html.twig', [
            'data' => $tradeReconManager->getValues($dateFrom, $dateTo, $this->getUser(), $clientName),
            'form' => $form->createView(),
        ]);
    }

    public function accountsView(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $clientPortfolioId = $request->get('client_portfolio_id', 0);
        $clientPortfolioValue = $em->getRepository('App\Entity\ClientPortfolio')->find($clientPortfolioId);

        if (!$clientPortfolioValue || $clientPortfolioValue->getClient()->getRia() !== $this->getUser()) {
            $this->json([
                'status' => 'error',
                'content' => 'client portfolio not found',
            ]);
        }

        $clientAccountValues = $em->getRepository('App\Entity\ClientAccountValue')->findLatestValuesForClientPortfolio($clientPortfolioId);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Rebalancer/_rebalance_account_list.html.twig', [
                'client_account_values' => $clientAccountValues,
            ]),
        ]);
    }

    public function startInitialRebalance(Request $request)
    {
        $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
        $portfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $userManager = $this->get('wealthbot.manager.user');
        $workflowManager = $this->get('wealthbot.workflow.manager');

        /** @var User $client */
        $client = $userManager->find($request->get('client_id'));
        if ($client->getRia() !== $this->getUser()) {
            throw $this->createNotFoundException();
        }

        $systemAccounts = $systemAccountManager->getAccountsForClient($client);
        $rebalanceAccountIds = $request->get('rebalance_accounts', []);

        $updateAccounts = [];
        $systemAccountIds = [];
        foreach ($systemAccounts as $account) {
            $id = $account->getId();
            $systemAccountIds[] = $id;

            if (in_array($id, $rebalanceAccountIds)) {
                $updateAccounts[$id] = $account;
            }
        }

        $diff = array_diff($systemAccountIds, $rebalanceAccountIds);
        if (empty($diff)) {
            foreach ($systemAccounts as $account) {
                $workflow = $workflowManager->findOneByClientAndObject($client, $account);
                if ($workflow) {
                    $workflowManager->updateStatusAndSave($workflow, Workflow::STATUS_COMPLETED);
                }
            }
        }

        /** @var SystemAccount $item */
        foreach ($updateAccounts as $item) {
            $item->setStatusInitRebalance();
            $systemAccountManager->save($item);
        }

        $accountValues = $systemAccountManager->getClientAccountsValues($client);
        $initRebalanceWorkflow = $workflowManager->findNotCompletedInitRebalanceWorkflow($client);
        if ($initRebalanceWorkflow) {
            $isInitRebalance = $systemAccountManager->isClientAccountsHaveInitRebalanceStatus($client);
        } else {
            $isInitRebalance = true;
        }

        $sasCashForm = $this->createForm(ClientSasCashCollectionFormType::class, $client);
        $parameters = [
            'client' => $client,
            'is_client_view' => false,
            'sas_cash_form' => $sasCashForm->createView(),
            'account_values' => $accountValues,
            'is_init_rebalance' => $isInitRebalance,
            'client_portfolio_values_information' => $portfolioValuesManager->prepareClientPortfolioValuesInformation($client),
        ];

        return $this->json([
            'status' => 'success',
            'message' => 'These accounts will be initially rebalanced.',
            'content' => $this->renderView('/Client/Dashboard/_index_content.html.twig', $parameters),
        ]);
    }

    public function startRebalance(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
//        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        /** @var JobRepository $jobRepo */
        $jobRepo = $em->getRepository('App\Entity\Job');

        $ria = $this->getUser();
        $isHouseholdLevel = $ria->getRiaCompanyInformation()->isHouseholdManagedLevel();

        $formValues = $request->get('rebalance_form');

        if (isset($formValues['is_all']) && $formValues['is_all']) {
            if ($isHouseholdLevel) {
                $clientValues = $clientPortfolioValuesManager->getLatestClientPortfolioValuesForClients($ria);
            } else {
                $clientValues = $clientAccountValuesManager->getLatestClientAccountValuesForClients($ria, $clientPortfolioManager);
            }

            $clientValuesIds = [];
            foreach ($clientValues as $clientValue) {
                $clientValuesIds[] = $clientValue->getId();
            }
        } else {
            $clientValuesIds = $formValues['client_value'];
        }

        if (!$request->isXmlHttpRequest() || empty($clientValuesIds)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RebalanceFormType::class, null, [
            'client_value_ids' => $clientValuesIds,
            'is_show_type' => true]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();

            $rebalanceType = isset($formData['rebalance_type']) ? $formData['rebalance_type'] : null;

//            $progress = new RebalanceProgress(count($clientValuesIds));
//            $progress->setUserId($ria->getId());
//            $dm->persist($progress);
//            $dm->flush();

            $job = $this->createJob($rebalanceType);

            $em->persist($job);
            $em->flush();

            foreach ($clientValuesIds as $clientValueId) {
                if ($isHouseholdLevel) {
                    /** @var ClientPortfolioValue $clientPortfolioValue */
                    $clientPortfolioValue = $clientPortfolioValuesManager->find($clientValueId);
                    if ($clientPortfolioValue->getClientPortfolio()->getClient()->getRia() !== $this->getUser()) {
                        continue;
                    }

                    $rebalancerAction = $this->createRebalancer($job, $clientPortfolioValue);

                    $em->persist($rebalancerAction);
                } else {
                    $accountValues = [$clientAccountValuesManager->find($clientValueId)];

                    /** @var ClientAccountValue $accountValue */
                    foreach ($accountValues as $accountValue) {
                        if ($accountValue->getClientPortfolio()->getClient()->getRia() !== $this->getUser()) {
                            continue;
                        }

                        $clientPortfolioValue = $clientPortfolioValuesManager->getLatestValuesForPortfolio($accountValue->getClientPortfolio());
                        $rebalancerAction = $this->createRebalancer($job, $clientPortfolioValue, $accountValue);

                        $em->persist($rebalancerAction);
                    }
                }
            }
            $em->persist($job);
            $em->flush();

            return $this->json([
                'status' => 'timeout',
            ]);
        }

        return $this->json([
            'status' => 'error',
            'message' => 'Invalid Data',
        ]);
    }

    public function checkProgress(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $this->getUser();
        /** @var JobRepository $jobRepo */
        $jobRepo = $em->getRepository('App\Entity\Job');
        $incompleteJobs = $jobRepo->findIncompleteByRia($ria);

        $incompleteJobIds = [];
        foreach ($incompleteJobs as $incompleteJob) {
            $incompleteJobIds[] = $incompleteJob->getId();
        }

        if (empty($incompleteJobIds)) {
            return $this->json([
                'status' => 'success',
            ]);
        }

        return $this->json([
            'status' => 'error',
            'incomplete_job_ids' => $incompleteJobIds,
        ]);
    }

    private function createJob($type)
    {
        $job = new Job();
        $job->setNameRebalancer();
        $job->setUser($this->getUser());
        $job->setRebalanceType($type);

        return $job;
    }

    /**
     * @param Job                  $job
     * @param ClientPortfolioValue $clientPortfolioValue
     * @param ClientAccountValue   $clientAccountValue
     *
     * @return RebalancerAction
     */
    private function createRebalancer(Job $job, ClientPortfolioValue $clientPortfolioValue, ClientAccountValue $clientAccountValue = null)
    {
        $rebalancerAction = new RebalancerAction();
        $rebalancerAction->setJob($job);
        $rebalancerAction->setClientPortfolioValue($clientPortfolioValue);
        $rebalancerAction->setClientAccountValue($clientAccountValue);
        return $rebalancerAction;
    }

    public function postRebalance(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dm = $this->get('doctrine.odm.mongodb.document_manager');

        /** @var User $ria */
        $ria = $this->getUser();

        /** @var RebalanceProgress $progress */
        $progress = $dm->getRepository('App\Entity\RebalanceProgress')->findOneBy(['userId' => $ria->getId()]);
        if ($progress && $progress->getTotalCount() === $progress->getCompleteCount()) {
            $dm->remove($progress);
            $dm->flush();
        }

        /** @var Job $job */
        $job = $em->getRepository('App\Entity\Job')->findLastRebalanceJobForUser($ria);

        if (!$job) {
            throw $this->createNotFoundException();
        }

        $isHouseholdLevel = $ria->getRiaCompanyInformation()->isHouseholdManagedLevel();

        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $paginator = $this->get('knp_paginator');

        if ($isHouseholdLevel) {
            $clientValues = $clientPortfolioValuesManager->getLatestValuesForJobQuery($job);
        } else {
            $clientValues = $clientAccountValuesManager->getLatestValuesForJobQuery($job);
        }

        /** @var SlidingPagination $clientValuesPagination */
        $clientValuesPagination = $paginator->paginate($clientValues, $request->get('page', 1), 100);
        $clientValuesPagination->setUsedRoute('rx_ria_rebalancing_post_rebalance');

        $clientValuesIds = [];
        foreach ($clientValuesPagination->getItems() as $clientValue) {
            $clientValuesIds[] = $clientValue->getId();
        }

        $form = $this->createForm(RebalanceFormType::class, $clientValuesIds);

        $responseData = [
            'client_values_pagination' => $clientValuesPagination,
            'ria' => $ria,
            'is_history' => false,
            'form' => $form->createView(),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Ria/Rebalancer/_rebalance_table.html.twig', $responseData),
            ]);
        }

        return $this->render('/Ria/Rebalancer/post_rebalance.html.twig', $responseData);
    }

    public function showDetails(Request $request)
    {
        $clientValueId = $request->get('id');

        if (!$request->isXmlHttpRequest() || !$clientValueId) {
            throw $this->createNotFoundException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $clientAllocationManager = $this->get('wealthbot_client.client_allocation_values.manager');
        $rebalancerQueueManager = $this->get('wealthbot.manager.rebalancer_queue');

        /** @var User $ria */
        $ria = $this->getUser();
        $isHouseholdLevel = $ria->getRiaCompanyInformation()->isHouseholdManagedLevel();

        if ($isHouseholdLevel) {
            $clientValue = $clientPortfolioValuesManager->find($clientValueId);
        } else {
            /** @var ClientAccountValue $clientValue */
            $clientValue = $clientAccountValuesManager->find($clientValueId);
        }

        if ($clientValue->getClientPortfolio()->getClient()->getRia() !== $ria) {
            throw $this->createNotFoundException();
        }

        $client = $clientValue->getClientPortfolio()->getClient();
        $rebalancerAction = $clientValue->getRebalancer();

        /** @var RebalancerQueueRepository $rebalancerQueueRepo */
        $rebalancerQueueRepo = $em->getRepository('App\Entity\RebalancerQueue');
        $rebalancerQueueCollection = $rebalancerQueueRepo->findGroupedInformationByRebalancer($rebalancerAction);

        $allocationValues = $clientAllocationManager->getValues($client);
        $tableData = $clientAllocationManager->refundValues($allocationValues['tableData'], $rebalancerAction);

        $summary = $rebalancerQueueManager->prepareSummary($rebalancerAction);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Rebalancer/_rebalance_details.html.twig', [
                'client_value' => $clientValue,
                'rebalancer_queue' => $rebalancerQueueCollection,
                'allocations' => $tableData,
                'summary' => $summary,
            ]),
        ]);
    }

    public function rebalancerQueueChangeState(Request $request)
    {
        $id = $request->get('id', 0);
        $state = $request->get('state');

        if (!$request->isXmlHttpRequest() || !$id || !$state) {
            throw $this->createNotFoundException();
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $clientAllocationManager = $this->get('wealthbot_client.client_allocation_values.manager');
        $rebalancerQueueManager = $this->get('wealthbot.manager.rebalancer_queue');

        /** @var RebalancerQueueRepository $rebalancerQueueRepo */
        $rebalancerQueueRepo = $em->getRepository('App\Entity\RebalancerQueue');
        /** @var RebalancerQueue $rebalancerQueue */
        $rebalancerQueue = $rebalancerQueueRepo->find($id);
        $rebalancerAction = $rebalancerQueue->getRebalancer();
        $isHouseholdLevel = $this->getUser()->getRiaCompanyInformation()->isHouseholdManagedLevel();

        if ($isHouseholdLevel) {
            $client = $rebalancerAction->getClientPortfolioValue()->getClientPortfolio()->getClient();
        } else {
            $client = $rebalancerAction->getClientAccountValue()->getClientPortfolio()->getClient();
        }

        if (!$rebalancerQueue) {
            return $this->json([
                'status' => 'error',
                'message' => 'Rebalancer Queue with id '.$id.' does not exist',
            ]);
        }

        $isDeleted = 'deleted' === $state ? true : false;

        if ($rebalancerQueue->isBuy()) {
            $rebalancerQueue->setIsDeleted($isDeleted);

            $em->persist($rebalancerQueue);
            $em->flush();
        } else {
            $queues = $rebalancerQueueRepo->findRelatedSellTrades($rebalancerQueue);
            foreach ($queues as $queue) {
                $queue->setIsDeleted($isDeleted);

                $em->persist($queue);
            }
            $em->flush();
            $em->clear();
        }

        $allocationValues = $clientAllocationManager->getValues($client);
        $tableData = $clientAllocationManager->refundValues($allocationValues['tableData'], $rebalancerAction);
        $summary = $rebalancerQueueManager->prepareSummary($rebalancerAction);

        return $this->json([
            'status' => 'success',
            'state' => $state,
            'allocation_table' => $this->renderView('/Ria/Rebalancer/_portfolio_allocation_table.html.twig', [
                'allocations' => $tableData,
            ]),
            'summary_table' => $this->renderView('/Ria/Rebalancer/_rebalancing_summary_table.html.twig', [
                'summary' => $summary,
            ]),
        ]);
    }

    public function generateTradeFile(Request $request)
    {
        $ria = $this->getUser();
        /** @var RiaCompanyInformation $riaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();
        $isHouseholdLevel = $riaCompanyInformation->isHouseholdManagedLevel();

        $formValues = $request->get('rebalance_form');

        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        $rebalancerQueueManager = $this->get('wealthbot.manager.rebalancer_queue');

        if (isset($formValues['is_all']) && $formValues['is_all']) {
            if ($isHouseholdLevel) {
                $clientValues = $clientPortfolioValuesManager->getLatestClientPortfolioValuesForClients($ria);
            } else {
                $clientValues = $clientAccountValuesManager->getLatestClientAccountValuesForClients($ria, $clientPortfolioManager);
            }

            $clientValuesIds = [];
            foreach ($clientValues as $clientValue) {
                $clientValuesIds[] = $clientValue->getId();
            }
        } else {
            $clientValuesIds = $formValues['client_value'];
        }

        if (!$request->isXmlHttpRequest() || empty($clientValuesIds)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RebalanceFormType::class, null, [
            'client_value_ids' => $clientValuesIds,
            'is_show_type' => true]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $tradeDataCollection = $rebalancerQueueManager->getTradeDataCollection($riaCompanyInformation, $clientValuesIds);

            if (empty($tradeDataCollection)) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'No Trades',
                ]);
            }

            $date = date('mdY-His');
            $filename = $date.'.csv';

            $filePath = $this->container->getParameter('uploads_trade_files_dir').'/'.$filename;

            $file = new \SplFileObject($filePath, 'w');

            foreach ($tradeDataCollection as $tradeData) {
                $file->fputcsv($tradeData->toArrayForTradeFile());

                if (RebalancerQueue::STATUS_SELL === $tradeData->get()) {
                    foreach ($tradeData->getVsps() as $vsp) {
                        $file->fputcsv($vsp);
                    }
                }
            }

            return $this->json([
                'status' => 'success',
                'redirect_url' => $this->generateUrl('rx_download_trade_file', ['filename' => $filename]),
            ]);
        }

        return $this->json([
            'status' => 'error',
            'message' => 'Invalid Data',
        ]);
    }
}
