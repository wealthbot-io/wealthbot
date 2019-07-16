<?php

namespace App\Controller\Client;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Event\AdminEvents;
use App\Event\UserHistoryEvent;
use App\Event\ClientEvents;
use App\Docusign\TransferInformationConsolidatorCondition;
use App\Docusign\TransferInformationCustodianCondition;
use App\Docusign\TransferInformationPolicyCondition;
use App\Docusign\TransferInformationQuestionnaireCondition;
use App\Entity\AccountGroup;
use App\Entity\ClientAccount;
use App\Entity\SystemAccount;
use App\Entity\TransferInformation;
use App\Entity\Workflow;
use App\Event\WorkflowEvent;
use App\Form\Handler\BankInformationFormHandler;
use App\Form\Handler\TransferInformationFormHandler;
use App\Form\Type\BankInformationFormType;
use App\Form\Type\DashboardSystemAccountsFormType;
use App\Form\Type\TransferFundingDistributingFormType;
use App\Form\Type\TransferInformationFormType;
use App\Form\Type\TransferReviewFormType;
use App\Manager\SystemAccountManager;
use App\Repository\ClientAccountRepository;
use App\Entity\Document;
use App\Entity\Profile;
use App\Entity\User;

class DashboardTransferController extends BaseTransferController
{
    public function selectSystemAccount(Request $request)
    {
        $client = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $systemAccountRepo = $em->getRepository('App\Entity\SystemAccount');

        /** @var ClientAccount $account */
        $account = $repo->find($request->get('account_id'));
        if (!$account || $account->getClient() !== $this->getUser()) {
            throw $this->createNotFoundException(sprintf('Account with id : %s does not exist.', $account->getId()));
        }

        $group = $account->getGroupName();
        if (AccountGroup::GROUP_EMPLOYER_RETIREMENT === $group) {
            $clientAccounts = $repo->findConsolidatedAccountsByClientId($client->getId());

            return $this->json([
                'status' => 'success',
                'account_table' => $this->renderView('/Client/Transfer/_accounts_list.html.twig', [
                    'client_accounts' => [$account],
                    'client' => $client,
                    'total' => $repo->getTotalScoreById($account->getId()),
                    'show_sas_cash' => $this->containsSasCash($clientAccounts),
                ]),
                'message' => 'Your advisor has been notified of your new retirement plan. They will contact you once they have reviewed your plan and fund options.',
            ]);
        }

        $systemAccounts = $systemAccountRepo->findByClientIdAndType($client->getId(), $account->getSystemType());
        if (count($systemAccounts) > 0 &&
            (AccountGroup::GROUP_FINANCIAL_INSTITUTION === $group || AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT === $group)
        ) {
            $form = $this->createForm(DashboardSystemAccountsFormType::class, null, ['account'=>$account, 'systemAccounts' => $systemAccounts]);

            if ($request->isMethod('post')) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $systemAccount = $form->get('account')->getData();
                    if (!($systemAccount instanceof SystemAccount)) {
                        $systemAccount = $systemAccountRepo->find($systemAccount);
                    }

                    $this->setSystemTransferAccount($systemAccount->getId());

                    // Check if docusign is allowed
                    $account->setConsolidator($systemAccount->getClientAccount());
                    $accountDocusignManager = $this->get('wealthbot_docusign.account_docusign.manager');
                    $accountDocusignManager->updateIsDocusignUsed(
                        $account->getTransferInformation(),
                        new TransferInformationConsolidatorCondition()
                    );

                    return $this->redirect(
                        $this->generateUrl('rx_client_dashboard_transfer_basic', ['account_id' => $account->getId()])
                    );
                }
            } else {
                $selectedAccount = 1 === count($systemAccounts) ? $systemAccounts[0] : null;

                return $this->render('/Client/DashboardTransfer/select_system_account.html.twig', [
                    'account' => $account,
                    'system_account' => $selectedAccount,
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->redirect(
            $this->generateUrl('rx_client_dashboard_transfer_basic', ['account_id' => $account->getId()])
        );
    }

    public function createBankInformation(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $client = $this->getUser();
        $accountId = (int) $request->get('account_id');
        $account = null;

        if (0 !== $accountId) {
            /** @var ClientAccount $account */
            $account = $repo->findOneBy(['id' => $accountId, 'client_id' => $client->getId()]);
        }

        $bankInfo = null;
        $form = $this->createForm(BankInformationFormType::class);
        $formHandler = new BankInformationFormHandler($form, $request, $em, ['client' => $client]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                // Only if bank information has been created on the client account management page
                if (0 === $accountId) {
                    $event = new WorkflowEvent($client, $bankInfo, Workflow::TYPE_PAPERWORK);
                    $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);
                }
            } else {
                return $this->json([
                    'status' => 'error',
                    'form' => $this->renderView(
                        '/Client/DashboardTransfer/_create_bank_account_form.html.twig',
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
                ['em' => $em, 'account' => $account, 'isPreSaved' => $request->isXmlHttpRequest()]
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

    public function review(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $documentSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        /** @var User $client */
        $client = $this->getUser();
        $custodian = $client->getCustodian();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        if (!$documentSignatureManager->isDocumentSignatureForObjectExist($account)) {
            $documentSignatureManager->createSignature($account);

            $signatures = $documentSignatureManager->getApplicationSignatures($account);
            $event = new WorkflowEvent($client, $account, Workflow::TYPE_PAPERWORK, $signatures);
            $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);
        }

        // Custodian disclosures links
        $custodianDisclosures = $documentManager->getCustodianDisclosuresLinks($custodian);
        if ($account->isTraditionalIraType()) {
            unset($custodianDisclosures[Document::TYPE_ROTH_ACCOUNT_DISCLOSURE]);
        } elseif ($account->isRothIraType()) {
            unset($custodianDisclosures[Document::TYPE_IRA_ACCOUNT_DISCLOSURE]);
        } else {
            unset($custodianDisclosures[Document::TYPE_ROTH_ACCOUNT_DISCLOSURE]);
            unset($custodianDisclosures[Document::TYPE_IRA_ACCOUNT_DISCLOSURE]);
        }

        $isCurrentRetirement = $repo->findRetirementAccountById($account->getId()) ? true : false;
        $form = $this->createForm(TransferReviewFormType::class, null, [
            'manager' => $documentSignatureManager, 'account'=> $account]);

        $isPreSaved = $request->isXmlHttpRequest();
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $account->setProcessStep(ClientAccount::PROCESS_STEP_FINISHED_APPLICATION);
                foreach ($account->getConsolidatedAccounts() as $consolidated) {
                    $consolidated->setProcessStep(ClientAccount::PROCESS_STEP_FINISHED_APPLICATION);
                }

                $account->setProcessStep(ClientAccount::STEP_ACTION_REVIEW);
                $account->setIsPreSaved($isPreSaved);

                $group = $account->getGroupName();
                if ((AccountGroup::GROUP_FINANCIAL_INSTITUTION === $group || AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT === $group) &&
                    $this->getSystemTransferAccount()
                ) {
                    /** @var SystemAccount $systemAccount */
                    $systemAccount = $em->getRepository('App\Entity\SystemAccount')->find($this->getSystemTransferAccount());
                    if ($systemAccount) {
                        $account->setConsolidator($systemAccount->getClientAccount());
                    }

                    $this->removeSystemTransferAccount();
                } else {
                    // Create system account for client account
                    /** @var $systemAccountManager SystemAccountManager */
                    $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
                    $systemAccountManager->createSystemAccountForClientAccount($account);
                }

                $em->persist($account);
                $em->flush($account);

                $this->dispatchHistoryEvent($client, 'Opened or transferred new account');

                // If client complete all accounts
                $hasNotOpenedAccounts = $repo->findOneNotOpenedAccountByClientId($client->getId()) ? true : false;
                $profile = $client->getProfile();
                if (!$hasNotOpenedAccounts && (7 !== $profile->getRegistrationStep())) {
                    $profile->setRegistrationStep(7);
                    $profile->setClientStatus(Profile::CLIENT_STATUS_CLIENT);
                    $em->persist($profile);
                    $em->flush();
                }

                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_REVIEW);

                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render($this->getTemplate('review.html.twig'), [
            'client' => $client,
            'account' => $account,
            'application_signatures' => $documentSignatureManager->findSignaturesByAccountConsolidatorId($account->getId()),
            'form' => $form->createView(),
            'is_current_retirement' => $isCurrentRetirement,
            'custodian' => $custodian,
            'custodian_disclosures' => $custodianDisclosures,
        ]);
    }

    public function transfer(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');
        $documentSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');
        $client = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        if (!$account->hasGroup(AccountGroup::GROUP_FINANCIAL_INSTITUTION)) {
            return $this->createAccessDeniedException('Current account has not this step.');
        }

        $accountIndex = $request->get('account_index', 1);
        $consolidatedAccounts = $account->getConsolidatedAccountsCollection();
        $consolidatedAccounts->first();
        $transferAccounts = $consolidatedAccounts->getTransferAccounts();

        if ($transferAccounts->isEmpty()) {
            $this->createNotFoundException('You have not transfer accounts.');
        }
        if (!$transferAccounts->containsKey($accountIndex)) {
            throw $this->createNotFoundException('Page not found.');
        }

        $currentAccount = $transferAccounts->get($accountIndex);

        /** @var $information TransferInformation */
        $information = $currentAccount->getTransferInformation();
        if (!$information) {
            $information = new TransferInformation();
            $information->setClientAccount($currentAccount);
            $information->setFinancialInstitution($currentAccount->getFinancialInstitution());
        }

        $isPreSaved = $request->isXmlHttpRequest();
        $form = $this->createForm(TransferInformationFormType::class, $information, ['adm'=>$adm, 'isPreSaved' => $isPreSaved]);
        $formHandler = new TransferInformationFormHandler($form, $request, $em, ['client' => $client]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                /** @var TransferInformation $information */
                $information = $form->getData();

                $account->setStep(ClientAccount::STEP_ACTION_TRANSFER);
                $account->setIsPreSaved($isPreSaved);

                $isDocusignAllowed = $adm->isDocusignAllowed($information, [
                    new TransferInformationCustodianCondition(),
                    new TransferInformationPolicyCondition(),
                    new TransferInformationQuestionnaireCondition(),
                    new TransferInformationConsolidatorCondition(),
                ]);

                $adm->setIsUsedDocusign($account, $isDocusignAllowed);

                if (!$documentSignatureManager->isDocumentSignatureForObjectExist($information)) {
                    $documentSignatureManager->createSignature($information);
                }

                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_TRANSFER);
                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                // If account has next consolidated transfer account than redirect to it
                // else redirect to another step
                if ($transferAccounts->containsNextKey($accountIndex)) {
                    return $this->redirect(
                        $this->generateUrl($this->getRoutePrefix().'transfer_transfer_account', [
                            'account_id' => $account->getId(),
                            'account_index' => ($accountIndex + 1),
                        ])
                    );
                } else {
                    return $this->redirect($redirectUrl);
                }
            } elseif ($isPreSaved) {
                return $this->json([
                    'status' => 'error',
                    'form' => $this->renderView($this->getTemplate('_transfer_form.html.twig'), [
                        'account' => $account,
                        'current_account' => $currentAccount,
                        'account_index' => $accountIndex,
                        'form' => $form->createView(),
                    ]),
                ]);
            }
        }

        return $this->render($this->getTemplate('transfer.html.twig'), [
            'client' => $client,
            'account' => $account,
            'transfer_accounts' => $transferAccounts,
            'current_account' => $currentAccount,
            'account_index' => $accountIndex,
            'information' => $information,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Get prefix for routing.
     *
     * @return string
     */
    protected function getRoutePrefix()
    {
        return 'rx_client_dashboard_';
    }

    private function getSystemTransferAccount()
    {
        return $this->get('session')->get('client.dashboard_transfer.system_transfer_account');
    }

    private function setSystemTransferAccount($accountId)
    {
        $this->get('session')->set('client.dashboard_transfer.system_transfer_account', $accountId);
    }

    private function removeSystemTransferAccount()
    {
        $this->get('session')->remove('client.dashboard_transfer.system_transfer_account');
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
