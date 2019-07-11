<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 11.07.13
 * Time: 12:03
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Ria;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Event\ClientEvents;
use App\Entity\ClientAccount;
use App\Entity\Workflow;
use App\Event\WorkflowEvent;
use App\Form\Handler\WorkflowNoteFormHandler;
use App\Form\Type\WorkflowNoteFormType;

class WorkflowController extends Controller
{
    public function index(Request $request)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        /// $activityManager = $this->get('wealthbot.activity.manager');

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

                    $template = '/Ria/Workflow/_active_workflow_list.html.twig';
                    break;
                case 'archived':
                    $responseParameters['pagination'] = $this->buildPaginator(
                        $workflowManager->findByRiaIdQuery($ria->getId(), true),
                        $page
                    );

                    $template = '/Ria/Workflow/_archived_workflow_list.html.twig';
                    break;
                case 'activity':
                    $responseParameters['pagination'] = $this->buildPaginator(
                        $workflowManager->findByRiaQuery($ria->getId()),
                        $page
                    );

                    $template = '/Ria/Workflow/_workflow_activity_list.html.twig';
                    break;
                default:
                    $responseParameters['pagination'] = $this->buildPaginator(
                        $workflowManager->findByRiaIdQuery($ria->getId(), false),
                        $page
                    );

                    $template = '/Ria/Workflow/_active_workflow_list.html.twig';
                    break;
            }

            return $this->json([
                'status' => 'success',
                'content' => $this->renderView($template, $responseParameters),
            ]);
        } else {
            $paginations = [];

            $paginations['active'] = $this->buildPaginator(
                $workflowManager->findByRiaIdQuery($ria->getId(), false),
                'active' === $tab ? $page : 1
            );

            $paginations['archived'] = $this->buildPaginator(
                $workflowManager->findByRiaIdQuery($ria->getId(), true),
                'archived' === $tab ? $page : 1
            );

            $paginations['activity'] = $this->buildPaginator(
                $workflowManager->findByRiaIdQuery($ria->getId()),
                'activity' === $tab ? $page : 1
            );

            $responseParameters['paginations'] = $paginations;
        }

        $response = $this->render('/Ria/Workflow/index.html.twig', $responseParameters);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    public function view($id)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($id, $ria->getId());
        if (!$workflow) {
            return $this->json([
                'status' => 'error',
                'message' => 'The workflow does not exist or you cannot archive it.',
            ]);
        }

        $data = ['status' => 'success'];

        if (Workflow::MESSAGE_CODE_ALERT_CLOSED_ACCOUNT === $workflow->getMessageCode() &&
            'Entity\ClosingAccountHistory' === $workflow->getObjectType()) {
            $closedAccountsHistory = $workflowManager->getObjects($workflow);

            $data = [
                'status' => 'success',
                'content' => $this->renderView(
                    '/Ria/Workflow/_closed_accounts_list.html.twig',
                    ['history' => $closedAccountsHistory]
                ),
            ];
        }

        return $this->json($data);
    }

    public function archive($id)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($id, $ria->getId(), false);
        if (!$workflow) {
            return $this->json([
                'status' => 'error',
                'message' => 'The workflow does not exist or you cannot archive it.',
            ]);
        }

        $workflowManager->archiveAndSave($workflow);

        return $this->json(
            [
                'status' => 'success',
                'message' => 'Workflow has been archived.',
                'content' => $this->renderView(
                    '/Ria/Workflow/_archived_workflow_item.html.twig',
                    ['workflow' => $workflow]
                ),
            ]
        );
    }

    public function delete($id)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($id, $ria->getId(), false);
        if (!$workflow) {
            return $this->json([
                'status' => 'error',
                'message' => 'The workflow does not exist or you cannot delete it.',
            ]);
        }

        $workflowManager->delete($workflow);

        return $this->json(['status' => 'success', 'message' => 'Workflow has been deleted successfully.']);
    }

    public function updateStatus(Request $request)
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
            return $this->json(['status' => 'error', 'message' => $error]);
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
                        '/Ria/Workflow/_archived_workflow_item.html.twig',
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
                        '/Ria/Workflow/_active_workflow_item.html.twig',
                        ['workflow' => $newWorkflow]
                    );
                }
            } elseif ($workflow->isInProgress() && $workflow->isPaperwork()) {
                if ($workflow->isClientStatusEnvelopeCompleted()) {
                    $this->get('wealthbot.mailer')->sendCustodianWorkflowDocuments($ria, $workflow);
                }
            }
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'status' => 'error',
                'message' => sprintf('Invalid value for workflow status: %s.', $status),
            ]);
        }

        return $this->json($result);
    }

    public function editNote(Request $request)
    {
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $ria = $this->getUser();

        $workflow = $workflowManager->findOneByIdAndRiaId($request->get('id'), $ria->getId());
        if (!$workflow) {
            return $this->json([
                'status' => 'error',
                'message' => 'The workflow does not exist.',
            ]);
        }

        $form = $this->createForm(WorkflowNoteFormType::class, $workflow);
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

        return $this->json(
            [
                'status' => $status,
                'content' => $this->renderView(
                    '/Ria/Workflow/_note_form.html.twig',
                    [
                        'workflow' => $workflow,
                        'form' => $form->createView(),
                    ]
                ),
            ]
        );
    }

    public function deleteActivitySummary(Request $request)
    {
        /// $activityManager = $this->get('wealthbot.activity.manager');

        //// $activity = $activityManager->find($request->get('id'));
        $ria = $this->getUser();

        ////   if (!$activity || ($activity->getRiaUserId() !== $ria->getId())) {
        ////      throw $this->createNotFoundException('Activity Summary with id %s not found');
        ////  }

        //// $activity->setIsShowRia(false);

        ////   $activityManager->updateActivity($activity);

        ///   $pagination = $this->buildPaginator(
        ///       $activityManager->findByRiaQuery($ria)
        ///  );

        //   $showPagination = $request->get('show_pagination', true);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Workflow/_workflow_activity_list.html.twig', [
                'tab' => 'activity',
                'with_layout' => false,
           /////     'pagination' => $pagination,
                'show_pagination' => false,
            ]),
        ]);
    }

    public function documentsList(Request $request)
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
            return $this->json(['status' => 'error', 'message' => $error]);
        }

        $documents = $workflowManager->getDocumentsToDownload($workflow, false);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Workflow/_documents_list.html.twig', [
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
}
