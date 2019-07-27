<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 09.01.13
 * Time: 18:02
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Client;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use App\Event\AdminEvents;
use App\Event\UserHistoryEvent;
use App\Event\ClientEvents;
use App\Entity\ClientAccount;
use App\Entity\Workflow;
use App\Event\WorkflowEvent;
use App\Form\Handler\BankInformationFormHandler;
use App\Form\Type\BankInformationFormType;
use App\Form\Type\TransferClientInfoFormType;
use App\Form\Type\TransferFundingDistributingFormType;
use App\Model\UserAccountOwnerAdapter;
use App\Repository\ClientAccountRepository;
use App\Entity\DocumentSignature;
use App\Entity\Document;
use App\Entity\User;

class TransferController extends BaseTransferController
{
    public function index()
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        /** @var $client User */
        $client = $this->getUser();
        $profile = $client->getProfile();

        if (6 !== $profile->getRegistrationStep()) {
            $profile->setRegistrationStep(6);
            $em->persist($profile);
            $em->flush();
        }

        $hasNotOpenedAccounts = $repo->findOneNotOpenedAccountByClientId($client->getId()) ? true : false;

        return $this->render('/Client/Transfer/index.html.twig', [
            'client' => $client,
            'has_not_opened_accounts' => $hasNotOpenedAccounts,
        ]);
    }

    public function accountsList()
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        /** @var $client User */
        $client = $this->getUser();

        $clientAccounts = $repo->findConsolidatedAccountsByClientId($client->getId());
        $total = $repo->getTotalScoreByClientId($client->getId());

        return $this->render('/Client/Transfer/accounts_list.html.twig', [
            'client' => $client,
            'client_accounts' => $clientAccounts,
            'total' => $total,
            'show_sas_cash' => $this->containsSasCash($clientAccounts),
            //'is_transfer' => true
        ]);
    }

    public function accountDocuments(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workflowManager = $this->get('wealthbot.workflow.manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $account = $em->getRepository('App\Entity\ClientAccount')->find($request->get('account_id'));
        if (!$account) {
            $this->createNotFoundException(sprintf('Account with id: %s does not exists.', $request->get('account_id')));
        }

        $applicationWorkflow = $workflowManager->findAccountApplicationWorkflow($account);
        if ($applicationWorkflow) {
            $documents = $workflowManager->getDocumentsToDownload($applicationWorkflow);

            $count = count($documents);
            if ($count) {
                if (1 === $count) {
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

    public function delete($id)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $repo ClientAccountRepository */
        $repo = $em->getRepository('App\Entity\ClientAccount');

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

    public function createBankInformation(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $client = $this->getUser();
        $accountId = (int) $request->get('account_id');
        $account = null;
        $isPreSaved = $request->isXmlHttpRequest();

        if (0 !== $accountId) {
            /** @var ClientAccount $account */
            $account = $repo->findOneBy(['id' => $accountId, 'client_id' => $client->getId()]);
        }

        $bankInfo = null;
        $form = $this->createForm(BankInformationFormType::class);
        $formHandler = new BankInformationFormHandler($form, $request, $em, ['client' => $client]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                $bankInfo = $form->getData();

                // Only if bank information has been created on the client account management page
                if (0 === $accountId) {
                    $event = new WorkflowEvent($client, $bankInfo, Workflow::TYPE_PAPERWORK);
                    $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                    $this->dispatchHistoryEvent($client, 'Created bank information');
                }
            } else {
                return $this->json([
                    'status' => 'error',
                    'form' => $this->renderView(
                        '/Client/Transfer/_create_bank_account_form.html.twig',
                        [
                        'form' => $form->createView(),
                        'account_id' => $accountId,
                    ]
                    ),
                ]);
            }
        }

        $response = ['status' => 'success'];

        if (0 !== $accountId) {
            $transferForm = $this->createForm(
                TransferFundingDistributingFormType::class,
                null,
                ['em' => $em, 'account' => $account, 'isPreSaved' => $isPreSaved]
            );

            $transferFormChildren = $transferForm->createView()->vars['form']->getChildren();

            $response['form_fields'] = $this->renderView(
                '/Client/Transfer/_bank_transfer_form_fields.html.twig',
                ['form' => $transferFormChildren['funding'], 'account' => $account]
            );
        } else {
            $response['bank_account_item'] = $this->renderView(
                '/Client/Dashboard/_bank_account_item.html.twig',
                ['bank_account' => $bankInfo]
            );
        }

        return $this->json($response);
    }

    public function editBankInformation(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $repo = $em->getRepository('App\Entity\BankInformation');
        $accountRepo = $em->getRepository('App\Entity\ClientAccount');
        $client = $this->getUser();
        $accountId = (int) $request->get('account_id');
        $account = null;

        $bankInfo = $repo->findOneBy(['id' => $request->get('bank_id'), 'client_id' => $client->getId()]);
        if (!$bankInfo) {
            return $this->json(['status' => 'error', 'message' => 'Bank information does not exist.']);
        }

        if (0 !== $accountId) {
            $account = $accountRepo->findOneBy(['id' => $accountId, 'client_id' => $client->getId()]);
            if (!$account) {
                return $this->json(['status' => 'error', 'message' => 'Account does not exist.']);
            }
        }

        $responseStatus = 'success';
        $form = $this->createForm(BankInformationFormType::class, $bankInfo);
        $formHandler = new BankInformationFormHandler($form, $request, $em, ['client' => $client]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                $response = ['status' => 'success'];

                $signatures = $documentSignatureManager->createBankInformationSignature($bankInfo);
                if (count($signatures)) {
                    $response['content'] = $this->renderView(
                        '/Client/Transfer/_bank_information_sign.html.twig',
                        ['signatures' => $signatures]
                    );
                }

                if (0 !== $accountId) {
                    $transferForm = $this->createForm(
                        TransferFundingDistributingFormType::class,
                        null,
                        ['em' => $em, 'account' => $account, 'isPreSaved' => $request->isXmlHttpRequest()]
                    );
                    $transferFormChildren = $transferForm->createView()->vars['form']->getChildren();

                    $response['form_fields'] = $this->renderView(
                        '/Client/Transfer/_bank_transfer_form_fields.html.twig',
                        ['form' => $transferFormChildren['funding'], 'account' => $account]
                    );
                } else {
                    // Only if bank information has been updated on the client account management page
                    $event = new WorkflowEvent($client, $bankInfo, Workflow::TYPE_PAPERWORK, $signatures);
                    $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                    $response['bank_account_id'] = $bankInfo->getId();
                    $response['bank_account_item'] = $this->renderView(
                        '/Client/Dashboard/_bank_account_item.html.twig',
                        ['bank_account' => $bankInfo]
                    );

                    $this->dispatchHistoryEvent($client, 'Updated bank information');
                }

                return $this->json($response);
            } else {
                $responseStatus = 'error';
            }
        }

        return $this->json([
            'status' => $responseStatus,
            'content' => $this->renderView('/Client/Transfer/_edit_bank_account_form.html.twig', [
                'form' => $form->createView(),
                'bank' => $bankInfo,
                'account_id' => $accountId,
            ]),
        ]);
    }

    public function deleteBankInformation(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\BankInformation');

        $client = $this->getUser();

        $bankInfo = $repo->findOneBy(['id' => $request->get('bank_id'), 'client_id' => $client->getId()]);
        if (!$bankInfo) {
            return $this->json([
                'status' => 'error',
                'message' => 'Bank information does not exist.',
            ]);
        }

        $em->remove($bankInfo);
        $em->flush();

        $this->dispatchHistoryEvent($client, 'Deleted bank information');

        return $this->json(['status' => 'success']);
    }

    public function bankInformationSign(Request $request)
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
                    '/Client/Transfer/_bank_information_sign.html.twig',
                    ['signatures' => $signatures]
                );
            }
        }

        return $this->json($response);
    }

    public function editClientInfo(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $client User */
        $client = $this->getUser();
        $clientInfo = new UserAccountOwnerAdapter($client);

        $form = $this->createForm(TransferClientInfoFormType::class, $clientInfo);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $em->persist($data->getObjectToSave());
                $em->flush();

                return $this->json([
                    'status' => 'success',
                    'content' => $this->renderView('/Client/Transfer/_client_info.html.twig', [
                        'client' => $client,
                    ]),
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'content' => $this->renderView('/Client/Transfer/_client_info_form.html.twig', [
                            'form' => $form->createView(),
                        ]),
                ]);
            }
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/Transfer/_client_info_form.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function applications()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\ClientAccount');

        $client = $this->getUser();
        $companyInformation = $client->getRiaCompanyInformation();
        $accounts = $repository->findConsolidatedAccountsByClientId($client->getId());

        return $this->render('/Client/Transfer/applications.html.twig', [
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
