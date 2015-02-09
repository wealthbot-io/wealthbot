<?php
namespace Wealthbot\AdminBundle\Controller;

use Wealthbot\AdminBundle\Document\RebalanceProgress;
use Wealthbot\AdminBundle\Entity\Job;
use Wealthbot\AdminBundle\Entity\RebalancerAction;
use Wealthbot\ClientBundle\Entity\ClientAccountValue;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\ClientBundle\Entity\ClientPortfolioValue;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Manager\ClientAccountValuesManager;
use Wealthbot\RiaBundle\Form\Handler\RebalanceHistoryFilterFormHandler;
use Wealthbot\RiaBundle\Form\Type\RebalanceFormType;
use Wealthbot\RiaBundle\Form\Type\RebalanceHistoryFilterFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RebalancerController extends AclController
{
    public function indexAction(Request $request)
    {
        /** @var ClientAccountValuesManager $clientAccountValuesManager */
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        $paginator = $this->get('knp_paginator');

        $clientValues = $clientAccountValuesManager->getLatestClientAccountValuesForAdminQuery($clientPortfolioManager);

        $clientValuesPagination = $paginator->paginate($clientValues, $request->get('page', 1), 20);
        $clientValuesPagination->setUsedRoute('rx_admin_rebalancer');

        $clientValueIds = array();
        foreach ($clientValuesPagination->getItems() as $item) {
            $clientValueIds[$item->getId()] = $item->getId();
        }

        $chooseRebalanceTypeForm = $this->createForm(new RebalanceFormType($clientValueIds, true));

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse(array(
                'status' => 'success',
                'content' => $this->renderView('WealthbotAdminBundle:Rebalancer:_rebalance_table.html.twig', array(
                        'client_values_pagination' => $clientValuesPagination,
                        'is_history' => false,
                        'form' => $chooseRebalanceTypeForm->createView()
                    ))
            ));
        }

        return $this->render('WealthbotAdminBundle:Rebalancer:index.html.twig', array(
            'client_values_pagination' => $clientValuesPagination,
            'form' => $chooseRebalanceTypeForm->createView()
        ));
    }

    public function historyAction(Request $request)
    {
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $em = $this->get('doctrine.orm.entity_manager');
        $paginator = $this->get('knp_paginator');
        $session = $this->get('session');

        $filters = $session->get('rebalance_history_filter', array());

        $historyFilterForm = $this->createForm(new RebalanceHistoryFilterFormType(), $filters);

        if ($request->get('is_filter')) {
            $historyFilterFormHandler = new RebalanceHistoryFilterFormHandler($historyFilterForm, $request, $em, array(
                'session' => $session
            ));

            if ($historyFilterFormHandler->process()) {
                $filters = $session->get('rebalance_history_filter', array());
            } else {

                $this->getJsonResponse(array(
                    'status' => 'error',
                    'content' => $this->renderView('WealthbotRiaBundle:Rebalancer:_history_filter_form.html.twig', array(
                            'history_filter_form' => $historyFilterForm
                        ))
                ));
            }
        }

        $historyClientValuesQuery = $clientAccountValuesManager->getHistoryForAdminQuery($filters);

        $historyClientValuesPagination = $paginator->paginate($historyClientValuesQuery, $request->get('page', 1), 20);
        $historyClientValuesPagination->setUsedRoute('rx_admin_rebalancer_history');

        $responseData = array(
            'client_values_pagination' => $historyClientValuesPagination,
            'is_history' => true,
            'history_filter_form' => $historyFilterForm->createView()
        );

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse(array(
                'status' => 'success',
                'content' => $this->renderView('WealthbotAdminBundle:Rebalancer:_rebalance_table.html.twig', $responseData)
            ));
        }

        return $this->render('WealthbotAdminBundle:Rebalancer:history.html.twig', $responseData);
    }

    public function checkProgressAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $dm = $this->get('doctrine.odm.mongodb.document_manager');

        $admin = $this->getUser();

        /** @var RebalanceProgress $rebalanceProgress */
        $rebalanceProgress = $dm->getRepository('WealthbotAdminBundle:RebalanceProgress')->findOneBy(array('userId' => $admin->getId()));

        if (!$rebalanceProgress) {
            return $this->getJsonResponse(array(
                'status' => 'error'
            ));
        }

        $total = $rebalanceProgress->getTotalCount();
        $complete = $rebalanceProgress->getCompleteCount();
        $progress = ($complete / $total) * 100;

        return $this->getJsonResponse(array(
            'status' => 'success',
            'progress' => (int) $progress
        ));
    }


    public function postRebalanceAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dm = $this->get('doctrine.odm.mongodb.document_manager');

        $admin = $this->getUser();

        /** @var RebalanceProgress $progress */
        $progress = $dm->getRepository('WealthbotAdminBundle:RebalanceProgress')->findOneBy(array('userId' => $admin->getId()));
        if ($progress && $progress->getTotalCount() == $progress->getCompleteCount()) {
            $dm->remove($progress);
            $dm->flush();
        }

        $job = $em->getRepository('WealthbotAdminBundle:Job')->findLastRebalanceJobForUser($admin);

        if (!$job) {
            throw $this->createNotFoundException();
        }

        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $paginator = $this->get('knp_paginator');

        $clientValues = $clientAccountValuesManager->getLatestValuesForJobQuery($job);

        $clientValuesPagination = $paginator->paginate($clientValues, $request->get('page', 1), 100);
        $clientValuesPagination->setUsedRoute('rx_admin_rebalance_post_rebalance');

        $clientValuesIds = array();
        foreach ($clientValuesPagination->getItems() as $clientValue) {
            $clientValuesIds[] = $clientValue->getId();
        }

        $form = $this->createForm(new RebalanceFormType($clientValuesIds));

        $responseData = array(
            'client_values_pagination' => $clientValuesPagination,
            'is_history' => false,
            'form' => $form->createView()
        );

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse(array(
                'status' => 'success',
                'content' => $this->renderView('WealthbotAdminBundle:Rebalancer:_rebalance_table.html.twig', $responseData)
            ));
        }

        return $this->render('WealthbotAdminBundle:Rebalancer:post_rebalance.html.twig', $responseData);
    }

    public function showDetailsAction(Request $request)
    {
        $clientValueId = $request->get('id');

        if (!$request->isXmlHttpRequest() || !$clientValueId) {
            throw $this->createNotFoundException();
        }

        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');

        $clientValue = $clientAccountValuesManager->find($clientValueId);

        return $this->getJsonResponse(array(
            'status' => 'success',
            'content' => $this->renderView('WealthbotRiaBundle:Rebalancer:_rebalance_details.html.twig', array(
                    'client_value' => $clientValue
                ))
        ));
    }

    public function accountsViewAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $clientPortfolioId = $request->get('client_portfolio_id', 0);

        $clientPortfolioValue = $em->getRepository('WealthbotClientBundle:ClientPortfolio')->find($clientPortfolioId);

        if (!$clientPortfolioValue) {
            $this->getJsonResponse(array(
                'status' => 'error',
                'content' => 'client portfolio not found'
            ));
        }

        $clientAccountValues = $em->getRepository('WealthbotClientBundle:ClientAccountValue')->findLatestValuesForClientPortfolio($clientPortfolioId);

        return $this->getJsonResponse(array(
            'status' => 'success',
            'content' => $this->renderView('WealthbotRiaBundle:Rebalancer:_rebalance_account_list.html.twig', array(
                    'client_account_values' => $clientAccountValues,
                ))
        ));
    }

    public function startAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $dm = $this->get('doctrine.odm.mongodb.document_manager');
        $clientAccountValuesManager = $this->get('wealthbot_client.client_account_values.manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        $clientPortfolioValuesManager = $this->get('wealthbot_client.client_portfolio_values.manager');

        $ria = $this->getUser();

        $formValues = $request->get('rebalance_form');

        if (isset($formValues['is_all']) && $formValues['is_all']) {

            $clientValues = $clientAccountValuesManager->getLatestClientAccountValuesForAdmin($ria, $clientPortfolioManager);

            $clientValuesIds = array();
            foreach ($clientValues as $clientValue) {
                $clientValuesIds[] = $clientValue->getId();
            }
        } else {
            $clientValuesIds = $formValues['client_value'];
        }


        if (!$request->isXmlHttpRequest() || empty($clientValuesIds)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new RebalanceFormType($clientValuesIds, true));

        $form->bind($request);

        if ($form->isValid()) {

            $formData = $form->getData();

            $rebalanceType = isset($formData['rebalance_type']) ? $formData['rebalance_type'] : null;

            $progress = new RebalanceProgress(count($clientValuesIds));
            $progress->setUserId($ria->getId());
            $dm->persist($progress);
            $dm->flush();

            $job = $this->createJob($rebalanceType);

            $em->persist($job);
            $em->flush();

            foreach ($clientValuesIds as $clientValueId) {

                $accountValue = $clientAccountValuesManager->find($clientValueId);
                $portfolioValue = $clientPortfolioValuesManager->getLatestValuesForPortfolio($accountValue->getClientPortfolio());

                $rebalancerAction = $this->createRebalancerAction($job, $portfolioValue, $accountValue);

                $em->persist($rebalancerAction);
                $em->flush();

                $progress->setCompleteCount($progress->getCompleteCount()+1);
                $dm->persist($progress);
                $dm->flush();
            }

            $job->setFinishedAt(new \DateTime());
            $job->setIsError(false);

            $em->persist($job);

            $em->flush();

            return $this->getJsonResponse(array(
                'status' => 'success'
            ));
        }

        return $this->getJsonResponse(array(
            'status' => 'error'
        ));
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
     * @param Job $job
     * @param ClientPortfolio $clientPortfolio
     * @param SystemAccount $systemAccount
     * @return RebalancerAction
     */
    private function createRebalancerAction(Job $job, ClientPortfolioValue $clientPortfolioValue, ClientAccountValue $clientAccountValue = null)
    {
        $rebalancerAction = new RebalancerAction();
        $rebalancerAction->setJob($job);
        $rebalancerAction->setClientPortfolioValue($clientPortfolioValue);
        $rebalancerAction->setClientAccountValue($clientAccountValue);

        //rebalance proccess
        //HOLD

//        $rebalancerAction->setFinishedAt(new \DateTime());

        return $rebalancerAction;
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, array('Content-Type'=>'application/json'));
    }
}