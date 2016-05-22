<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 09.01.13
 * Time: 18:02
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\AdminBundle\AdminEvents;
use Wealthbot\AdminBundle\Event\UserHistoryEvent;
use Wealthbot\ClientBundle\ClientEvents;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Event\WorkflowEvent;
use Wealthbot\ClientBundle\Form\Handler\BankInformationFormHandler;
use Wealthbot\ClientBundle\Form\Type\BankInformationFormType;
use Wealthbot\ClientBundle\Form\Type\TransferClientInfoFormType;
use Wealthbot\ClientBundle\Form\Type\TransferFundingDistributingFormType;
use Wealthbot\ClientBundle\Model\UserAccountOwnerAdapter;
use Wealthbot\ClientBundle\Repository\ClientAccountRepository;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\User;

class TransferController extends BaseTransferController
{
    public function indexAction()
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        /** @var $client User */
        $client = $this->getUser();
        $profile = $client->getProfile();

        if ($profile->getRegistrationStep() !== 6) {
            $profile->setRegistrationStep(6);
            $em->persist($profile);
            $em->flush();
        }

        $hasNotOpenedAccounts = $repo->findOneNotOpenedAccountByClientId($client->getId()) ? true : false;

        return $this->render('WealthbotClientBundle:Transfer:index.html.twig', [
            'client' => $client,
            'has_not_opened_accounts' => $hasNotOpenedAccounts,
        ]);
    }

    public function accountsListAction()
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        /** @var $client User */
        $client = $this->getUser();

        $clientAccounts = $repo->findConsolidatedAccountsByClientId($client->getId());
        $total = $repo->getTotalScoreByClientId($client->getId());

        return $this->render('WealthbotClientBundle:Transfer:accounts_list.html.twig', [
            'client' => $client,
            'client_accounts' => $clientAccounts,
            'total' => $total,
            'show_sas_cash' => $this->containsSasCash($clientAccounts),
            //'is_transfer' => true
        ]);
    }

    public function accountDocumentsAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $account = $em->getRepository('WealthbotClientBundle:ClientAccount')->find($request->get('account_id'));
        if (!$account) {
            $this->createNotFoundException(sprintf('Account with id: %s does not exists.', $request->get('account_id')));
        }

        $applicationWorkflow = $workflowManager->findAccountApplicationWorkflow($account);
        if ($applicationWorkflow) {
            $documents = $workflowManager->getDocumentsToDownload($applicationWorkflow);

            $count = count($documents);
            if ($count) {
                if ($count === 1) {
                    $documents = array_values($documents);

                    /** @var Document $document */
                    $document = $documents[0];
                    $url = $documentManager->getDownloadLink($document->getFilename());
                } else {
                    $url = $documentManager->getDocumentsPackageLink(
                        $documents,
                        'application_'.$account->getId().'_documents.zip'
                    );
                }

                return $this->redirect($url);
            }
        }

        return $this->redirect($request->headers->get('referer'));
    }

    public function deleteAction($id)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $repo ClientAccountRepository */
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        $client = $this->getUser();

        $account = $repo->findOneBy([
           'id' => $id,
           'client_id' => $client->getId(),
        ]);

        if (!$account) {
            throw $this->createNotFoundException('Account does not exist.');
        }

        $em->remove($account);
        $em->flush();

        return $this->redirect($this->generateUrl('rx_client_transfer'));
    }

    public function createBankInformationAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');
        $client = $this->getUser();
        $accountId = (integer) $request->get('account_id');
        $account = null;

        if ($accountId !== 0) {
            /** @var ClientAccount $account */
            $account = $repo->findOneBy(['id' => $accountId, 'client_id' => $client->getId()]);
        }

        $bankInfo = null;
        $form = $this->createForm(new BankInformationFormType());
        $formHandler = new BankInformationFormHandler($form, $request, $em, ['client' => $client]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                $bankInfo = $form->getData();

                // Only if bank information has been created on the client account management page
                if ($accountId === 0) {
                    $event = new WorkflowEvent($client, $bankInfo, Workflow::TYPE_PAPERWORK);
                    $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                    $this->dispatchHistoryEvent($client, 'Created bank information');
                }
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'form' => $this->renderView(
                        'WealthbotClientBundle:Transfer:_create_bank_account_form.html.twig', [
                        'form' => $form->createView(),
                        'account_id' => $accountId,
                    ]),
                ]);
            }
        }

        $response = ['status' => 'success'];

        if ($accountId !== 0) {
            $transferForm = $this->createForm(
                new TransferFundingDistributingFormType($em, $account),
                ['funding' => $account->getAccountContribution()]
            );

            $transferFormChildren = $transferForm->createView()->vars['form']->getChildren();

            $response['form_fields'] = $this->renderView(
                'WealthbotClientBundle:Transfer:_bank_transfer_form_fields.html.twig',
                ['form' => $transferFormChildren['funding'], 'account' => $account]
            );
        } else {
            $response['bank_account_item'] = $this->renderView(
                'WealthbotClientBundle:Dashboard:_bank_account_item.html.twig',
                ['bank_account' => $bankInfo]
            );
        }

        return $this->getJsonResponse($response);
    }

    public function editBankInformationAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $repo = $em->getRepository('WealthbotClientBundle:BankInformation');
        $accountRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');
        $client = $this->getUser();
        $accountId = (integer) $request->get('account_id');
        $account = null;

        $bankInfo = $repo->findOneBy(['id' => $request->get('bank_id'), 'client_id' => $client->getId()]);
        if (!$bankInfo) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Bank information does not exist.']);
        }

        if ($accountId !== 0) {
            $account = $accountRepo->findOneBy(['id' => $accountId, 'client_id' => $client->getId()]);
            if (!$account) {
                return $this->getJsonResponse(['status' => 'error', 'message' => 'Account does not exist.']);
            }
        }

        $responseStatus = 'success';
        $form = $this->createForm(new BankInformationFormType(), $bankInfo);
        $formHandler = new BankInformationFormHandler($form, $request, $em, ['client' => $client]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                $response = ['status' => 'success'];

                $signatures = $documentSignatureManager->createBankInformationSignature($bankInfo);
                if (count($signatures)) {
                    $response['content'] = $this->renderView(
                        'WealthbotClientBundle:Transfer:_bank_information_sign.html.twig',
                        ['signatures' => $signatures]
                    );
                }

                if ($accountId !== 0) {
                    $transferForm = $this->createForm(
                        new TransferFundingDistributingFormType($em, $account),
                        ['funding' => $account->getAccountContribution()]
                    );
                    $transferFormChildren = $transferForm->createView()->vars['form']->getChildren();

                    $response['form_fields'] = $this->renderView(
                        'WealthbotClientBundle:Transfer:_bank_transfer_form_fields.html.twig',
                        ['form' => $transferFormChildren['funding'], 'account' => $account]
                    );
                } else {

                    // Only if bank information has been updated on the client account management page
                    $event = new WorkflowEvent($client, $bankInfo, Workflow::TYPE_PAPERWORK, $signatures);
                    $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                    $response['bank_account_id'] = $bankInfo->getId();
                    $response['bank_account_item'] = $this->renderView(
                        'WealthbotClientBundle:Dashboard:_bank_account_item.html.twig',
                        ['bank_account' => $bankInfo]
                    );

                    $this->dispatchHistoryEvent($client, 'Updated bank information');
                }

                return $this->getJsonResponse($response);
            } else {
                $responseStatus = 'error';
            }
        }

        return $this->getJsonResponse([
            'status' => $responseStatus,
            'content' => $this->renderView('WealthbotClientBundle:Transfer:_edit_bank_account_form.html.twig', [
                'form' => $form->createView(),
                'bank' => $bankInfo,
                'account_id' => $accountId,
            ]),
        ]);
    }

    public function deleteBankInformationAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:BankInformation');

        $client = $this->getUser();

        $bankInfo = $repo->findOneBy(['id' => $request->get('bank_id'), 'client_id' => $client->getId()]);
        if (!$bankInfo) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Bank information does not exist.',
            ]);
        }

        $em->remove($bankInfo);
        $em->flush();

        $this->dispatchHistoryEvent($client, 'Deleted bank information');

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function bankInformationSignAction(Request $request)
    {
        $signatureManager = $this->get('wealthbot_docusign.document_signature.manager');
        $client = $this->getUser();

        $response = [
            'status' => 'error',
            'message' => 'Error. Please try again later.',
        ];

        if ($request->isMethod('post')) {
            $response = [
                'status' => 'success',
                'message' => 'The operation was successful.',
            ];

            $signatures = $signatureManager->findDocumentSignaturesByClientAndTypes(
                $client,
                [DocumentSignature::TYPE_AUTO_INVEST_CONTRIBUTION, DocumentSignature::TYPE_AUTO_DISTRIBUTION]
            );

            $isCompleted = true;
            /** @var DocumentSignature $signature */
            foreach ($signatures as $signature) {
                if (!$signature->isCompleted()) {
                    $isCompleted = false;
                    break;
                }
            }

            if (!$isCompleted) {
                $response['status'] = 'error';
                $response['message'] = 'You have not signed applications. Please sign all applications.';
                $response['content'] = $this->renderView(
                    'WealthbotClientBundle:Transfer:_bank_information_sign.html.twig',
                    ['signatures' => $signatures]
                );
            }
        }

        return $this->getJsonResponse($response);
    }

    public function editClientInfoAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $client User */
        $client = $this->getUser();
        $clientInfo = new UserAccountOwnerAdapter($client);

        $form = $this->createForm(new TransferClientInfoFormType($clientInfo), $clientInfo);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $em->persist($data->getObjectToSave());
                $em->flush();

                return $this->getJsonResponse([
                    'status' => 'success',
                    'content' => $this->renderView('WealthbotClientBundle:Transfer:_client_info.html.twig', [
                        'client' => $client,
                    ]),
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'content' => $this->renderView('WealthbotClientBundle:Transfer:_client_info_form.html.twig', [
                            'form' => $form->createView(),
                        ]),
                ]);
            }
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Transfer:_client_info_form.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function applicationsAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('WealthbotClientBundle:ClientAccount');

        $client = $this->getUser();
        $companyInformation = $client->getRiaCompanyInformation();
        $accounts = $repository->findConsolidatedAccountsByClientId($client->getId());

        return $this->render('WealthbotClientBundle:Transfer:applications.html.twig', [
            'client' => $client,
            'accounts' => $accounts,
            'company_information' => $companyInformation,
        ]);
    }

    /**
     * Get prefix for routing.
     *
     * @return string
     */
    protected function getRoutePrefix()
    {
        return 'rx_client_';
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
