<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.07.13
 * Time: 12:03
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\ClientBundle\ClientEvents;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Event\WorkflowEvent;
use Wealthbot\RiaBundle\Form\Handler\WorkflowNoteFormHandler;
use Wealthbot\RiaBundle\Form\Type\WorkflowNoteFormType;

class WorkflowController extends Controller
{
    public function indexAction(Request $request)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $activityManager = $this->get('wealthbot.activity.manager');

        $ria = $this->getUser();
        $tab = $request->get('tab', 'active');
        $page = $request->query->get('page', 1);
        $withLayout = $request->get('with_layout', true);

        $isAjax = $request->isXmlHttpRequest();

        $responseParameters = [
            'tab' => $tab,
            'with_layout' => $withLayout,
        ];

        if ($isAjax) {
            switch ($tab) {
                case 'active':
                    $responseParameters['pagination'] = $this->buildPaginator(
                        $workflowManager->findByRiaIdQuery($ria->getId(), false),
                        $page
                    );

                    $template = 'WealthbotRiaBundle:Workflow:_active_workflow_list.html.twig';
                    break;
                case 'archived':
                    $responseParameters['pagination'] = $this->buildPaginator(
                        $workflowManager->findByRiaIdQuery($ria->getId(), true),
                        $page
                    );

                    $template = 'WealthbotRiaBundle:Workflow:_archived_workflow_list.html.twig';
                    break;
                case 'activity':
                    $responseParameters['pagination'] = $this->buildPaginator(
                        $activityManager->findByRiaQuery($ria),
                        $page
                    );

                    $template = 'WealthbotRiaBundle:Workflow:_workflow_activity_list.html.twig';
                    break;
                default:
                    $responseParameters['pagination'] = $this->buildPaginator(
                        $workflowManager->findByRiaIdQuery($ria->getId(), false),
                        $page
                    );

                    $template = 'WealthbotRiaBundle:Workflow:_active_workflow_list.html.twig';
                    break;
            }

            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $this->renderView($template, $responseParameters),
            ]);
        } else {
            $paginations = [];

            $paginations['active'] = $this->buildPaginator(
                $workflowManager->findByRiaIdQuery($ria->getId(), false),
                $tab === 'active' ? $page : 1
            );

            $paginations['archived'] = $this->buildPaginator(
                $workflowManager->findByRiaIdQuery($ria->getId(), true),
                $tab === 'archived' ? $page : 1
            );

            $paginations['activity'] = $this->buildPaginator(
                $activityManager->findByRiaQuery($ria),
                $tab === 'activity' ? $page : 1
            );

            $responseParameters['paginations'] = $paginations;
        }

        $response = $this->render('WealthbotRiaBundle:Workflow:index.html.twig', $responseParameters);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    public function viewAction($id)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($id, $ria->getId());
        if (!$workflow) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'The workflow does not exist or you cannot archive it.',
            ]);
        }

        $data = ['status' => 'success'];

        if ($workflow->getMessageCode() === Workflow::MESSAGE_CODE_ALERT_CLOSED_ACCOUNT &&
            $workflow->getObjectType() === 'Wealthbot\ClientBundle\Entity\ClosingAccountHistory') {
            $closedAccountsHistory = $workflowManager->getObjects($workflow);

            $data = [
                'status' => 'success',
                'content' => $this->renderView(
                    'WealthbotRiaBundle:Workflow:_closed_accounts_list.html.twig',
                    ['history' => $closedAccountsHistory]
                ),
            ];
        }

        return $this->getJsonResponse($data);
    }

    public function archiveAction($id)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($id, $ria->getId(), false);
        if (!$workflow) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'The workflow does not exist or you cannot archive it.',
            ]);
        }

        $workflowManager->archiveAndSave($workflow);

        return $this->getJsonResponse(
            [
                'status' => 'success',
                'message' => 'Workflow has been archived.',
                'content' => $this->renderView(
                    'WealthbotRiaBundle:Workflow:_archived_workflow_item.html.twig',
                    ['workflow' => $workflow]
                ),
            ]
        );
    }

    public function deleteAction($id)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($id, $ria->getId(), false);
        if (!$workflow) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'The workflow does not exist or you cannot delete it.',
            ]);
        }

        $workflowManager->delete($workflow);

        return $this->getJsonResponse(['status' => 'success', 'message' => 'Workflow has been deleted successfully.']);
    }

    public function updateStatusAction(Request $request)
    {
        $id = $request->get('id');
        $status = $request->get('status');
        $archive = $request->get('archive');

        $error = null;
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($id, $ria->getId(), false);
        if (!$workflow) {
            $error = 'The workflow does not exist or you cannot change status for it.';
        }

        if (!$workflow->isAdvisorStatusEditable()) {
            $error = 'You cannot change advisor status until the envelope is completed.';
        }

        if (null !== $error) {
            return $this->getJsonResponse(['status' => 'error', 'message' => $error]);
        }

        $client = $workflow->getClient();
        $result = ['status' => 'success'];

        try {
            $workflowManager->updateStatus($workflow, $status);
            if (null !== $archive) {
                $isArchived = (bool) $archive;
                $workflowManager->archive($workflow, $isArchived);

                $result['is_archived'] = $isArchived;
                if ($isArchived) {
                    $result['message'] = 'Workflow has been archived.';
                    $result['content'] = $this->renderView(
                        'WealthbotRiaBundle:Workflow:_archived_workflow_item.html.twig',
                        ['workflow' => $workflow]
                    );
                }
            }

            $workflowManager->save($workflow);

            // If all client accounts paperwork completed and all system client accounts is verified
            // Create new 'Initial Rebalance' alert
            if ($workflow->isCompleted() && $workflow->isAccountPaperwork()) {

                /** @var ClientAccount $clientAccount */
                $clientAccount = $workflowManager->getObject($workflow);

                $event = new WorkflowEvent($client, $clientAccount->getSystemAccount(), Workflow::TYPE_PAPERWORK);
                $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                $newWorkflow = $event->getData();
                if ($newWorkflow) {
                    $result['new_item'] = $this->renderView(
                        'WealthbotRiaBundle:Workflow:_active_workflow_item.html.twig',
                        ['workflow' => $newWorkflow]
                    );
                }
            } elseif ($workflow->isInProgress() && $workflow->isPaperwork()) {
                if ($workflow->isClientStatusEnvelopeCompleted()) {
                    $this->get('wealthbot.mailer')->sendCustodianWorkflowDocuments($ria, $workflow);
                }
            }
        } catch (\InvalidArgumentException $e) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => sprintf('Invalid value for workflow status: %s.', $status),
            ]);
        }

        return $this->getJsonResponse($result);
    }

    public function editNoteAction(Request $request)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($request->get('id'), $ria->getId());
        if (!$workflow) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'The workflow does not exist.',
            ]);
        }

        $form = $this->createForm(new WorkflowNoteFormType(), $workflow);
        $formHandler = new WorkflowNoteFormHandler($form, $request, $this->get('doctrine.orm.entity_manager'));
        $status = 'success';

        if ($request->isMethod('post')) {
            $process = $formHandler->process();

            if ($process) {
                $status = 'success';
            } else {
                $status = 'error';
            }
        }

        return $this->getJsonResponse(
            [
                'status' => $status,
                'content' => $this->renderView(
                    'WealthbotRiaBundle:Workflow:_note_form.html.twig',
                    [
                        'workflow' => $workflow,
                        'form' => $form->createView(),
                    ]
                ),
            ]
        );
    }

    public function deleteActivitySummaryAction(Request $request)
    {
        $activityManager = $this->get('wealthbot.activity.manager');

        $activity = $activityManager->find($request->get('id'));
        $ria = $this->getUser();

        if (!$activity || ($activity->getRiaUserId() !== $ria->getId())) {
            throw $this->createNotFoundException('Activity Summary with id %s not found');
        }

        $activity->setIsShowRia(false);

        $activityManager->updateActivity($activity);

        $pagination = $this->buildPaginator(
            $activityManager->findByRiaQuery($ria)
        );

        $showPagination = $request->get('show_pagination', true);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotRiaBundle:Workflow:_workflow_activity_list.html.twig', [
                'tab' => 'activity',
                'with_layout' => false,
                'pagination' => $pagination,
                'show_pagination' => $showPagination,
            ]),
        ]);
    }

    public function documentsListAction(Request $request)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();
        $error = null;

        $workflow = $workflowManager->findOneByIdAndRiaId($request->get('id'), $ria->getId());
        if (!$workflow) {
            $error = 'The workflow does not exist.';
        } elseif (!$workflow->canHaveDocuments()) {
            $error = 'The workflow does not have envelope applications.';
        }

        if ($error) {
            return $this->getJsonResponse(['status' => 'error', 'message' => $error]);
        }

        $documents = $workflowManager->getDocumentsToDownload($workflow, false);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotRiaBundle:Workflow:_documents_list.html.twig', [
                'documents' => $documents,
            ]),
        ]);
    }

    private function buildPaginator($data, $page = 1)
    {
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate($data, $page, $this->container->getParameter('pager_per_page'));
        $pagination->setUsedRoute('rx_ria_workflow');

        return $pagination;
    }

    private function getJsonResponse(array $data, $code = 200)
    {
        return new Response(json_encode($data), $code, ['Content-Type' => 'application/json']);
    }
}
