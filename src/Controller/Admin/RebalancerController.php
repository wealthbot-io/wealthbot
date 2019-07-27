<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Job;
use App\Entity\RebalancerAction;
use App\Entity\ClientAccountValue;
use App\Entity\ClientPortfolio;
use App\Entity\ClientPortfolioValue;
use App\Entity\SystemAccount;
use App\Manager\ClientAccountValuesManager;
use App\Form\Handler\RebalanceHistoryFilterFormHandler;
use App\Form\Type\RebalanceFormType;
use App\Form\Type\RebalanceHistoryFilterFormType;

class RebalancerController extends AclController
{
    public function index(Request $request)
    {
        /** @var ClientAccountValuesManager $clientAccountValuesManager */
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $paginator = $this->get('knp_paginator');

        $clientValues = $clientAccountValuesManager->getLatestClientAccountValuesForAdminQuery($clientPortfolioManager);

        $clientValuesPagination = $paginator->paginate($clientValues, $request->get('page', 1), 20);
        $clientValuesPagination->setUsedRoute('rx_admin_rebalancer');

        $clientValueIds = [];
        foreach ($clientValuesPagination->getItems() as $item) {
            $clientValueIds[$item->getId()] = $item->getId();
        }

        $chooseRebalanceTypeForm = $this->createForm(RebalanceFormType::class, null, ['client_value_ids' => $clientValueIds, 'is_show_type'=> true]);

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Admin/Rebalancer/_rebalance_table.html.twig', [
                        'client_values_pagination' => $clientValuesPagination,
                        'is_history' => false,
                        'form' => $chooseRebalanceTypeForm->createView(),
                    ]),
            ]);
        }

        return $this->render('/Admin/Rebalancer/index.html.twig', [
            'client_values_pagination' => $clientValuesPagination,
            'form' => $chooseRebalanceTypeForm->createView(),
        ]);
    }

    public function history(Request $request)
    {
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $em = $this->get('doctrine.orm.entity_manager');
        $paginator = $this->get('knp_paginator');
        $session = $this->get('session');

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

        $historyClientValuesQuery = $clientAccountValuesManager->getHistoryForAdminQuery($filters);

        $historyClientValuesPagination = $paginator->paginate($historyClientValuesQuery, $request->get('page', 1), 20);
        $historyClientValuesPagination->setUsedRoute('rx_admin_rebalancer_history');

        $responseData = [
            'client_values_pagination' => $historyClientValuesPagination,
            'is_history' => true,
            'history_filter_form' => $historyFilterForm->createView(),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Admin/Rebalancer/_rebalance_table.html.twig', $responseData),
            ]);
        }

        return $this->render('/Admin/Rebalancer/history.html.twig', $responseData);
    }

    public function checkProgress(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $admin = $this->getUser();

        /** @var RebalanceProgress $rebalanceProgress */

        $rebalanceProgress = $em->getRepository('App\Entity\RebalanceProgress')->findOneBy(['userId' => $admin->getId()]);

        if (!$rebalanceProgress) {
            return $this->json([
                'status' => 'error',
            ]);
        }

        $total = $rebalanceProgress->getTotalCount();
        $complete = $rebalanceProgress->getCompleteCount();
        $progress = ($complete / $total) * 100;

        return $this->json([
            'status' => 'success',
            'progress' => (int) $progress,
        ]);
    }

    public function postRebalance(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $admin = $this->getUser();

        $job = $em->getRepository('App\Entity\Job')->findLastRebalanceJobForUser($admin);

        if (!$job) {
            throw $this->createNotFoundException();
        }

        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $paginator = $this->get('knp_paginator');

        $clientValues = $clientAccountValuesManager->getLatestValuesForJobQuery($job);

        $clientValuesPagination = $paginator->paginate($clientValues, $request->get('page', 1), 100);
        $clientValuesPagination->setUsedRoute('rx_admin_rebalance_post_rebalance');

        $clientValuesIds = [];
        foreach ($clientValuesPagination->getItems() as $clientValue) {
            $clientValuesIds[] = $clientValue->getId();
        }

        $form = $this->createForm(RebalanceFormType::class, $clientValuesIds);

        $responseData = [
            'client_values_pagination' => $clientValuesPagination,
            'is_history' => false,
            'form' => $form->createView(),
        ];

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Admin/Rebalancer/_rebalance_table.html.twig', $responseData),
            ]);
        }

        return $this->render('/Admin/Rebalancer/post_rebalance.html.twig', $responseData);
    }

    public function showDetails(Request $request)
    {
        $clientValueId = $request->get('id');

        if (!$request->isXmlHttpRequest() || !$clientValueId) {
            throw $this->createNotFoundException();
        }

        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');

        $clientValue = $clientAccountValuesManager->find($clientValueId);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Rebalancer/_rebalance_details.html.twig', [
                    'client_value' => $clientValue,
                ]),
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

        if (!$clientPortfolioValue) {
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

    public function start(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');

        $ria = $this->getUser();

        $formValues = $request->get('rebalance_form');

        if (isset($formValues['is_all']) && $formValues['is_all']) {
            $clientValues = $clientAccountValuesManager->getLatestClientAccountValuesForAdmin($ria, $clientPortfolioManager);

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

        $form = $this->createForm(RebalanceFormType::class, null, ['client_value_ids'=>$clientValuesIds,'is_show_type'=> true]);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $formData = $form->getData();

            $rebalanceType = isset($formData['rebalance_type']) ? $formData['rebalance_type'] : null;
            $job = $this->createJob($rebalanceType);

            $em->persist($job);
            $em->flush();

            $em->clear();

            foreach ($clientValuesIds as $clientValueId) {
                $accountValue = $clientAccountValuesManager->find($clientValueId);
                $portfolioValue = $clientPortfolioValuesManager->getLatestValuesForPortfolio($accountValue->getClientPortfolio());

                $rebalancerAction = $this->createRebalancer($job, $portfolioValue, $accountValue);

                $em->persist($rebalancerAction);
            }

            $em->flush();
            $em->clear();

            $job->setFinishedAt(new \DateTime());
            $job->setIsError(false);

            $em->persist($job);

            $em->flush();
            $em->clear();

            return $this->json([
                'status' => 'success',
            ]);
        }

        return $this->json([
            'status' => 'error',
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
     * @param Job             $job
     * @param ClientPortfolio $clientPortfolio
     * @param SystemAccount   $systemAccount
     *
     * @return RebalancerAction
     */
    private function createRebalancer(Job $job, ClientPortfolioValue $clientPortfolioValue, ClientAccountValue $clientAccountValue = null)
    {
        $rebalancerAction = $this->get('App\Api\Rebalancer');

        return $rebalancerAction;
    }
}
