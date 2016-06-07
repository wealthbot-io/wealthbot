<?php

namespace Wealthbot\ClientBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedException;
use Wealthbot\AdminBundle\AdminEvents;
use Wealthbot\AdminBundle\Event\UserHistoryEvent;
use Wealthbot\ClientBundle\ClientEvents;
use Wealthbot\ClientBundle\Docusign\TransferInformationConsolidatorCondition;
use Wealthbot\ClientBundle\Docusign\TransferInformationCustodianCondition;
use Wealthbot\ClientBundle\Docusign\TransferInformationPolicyCondition;
use Wealthbot\ClientBundle\Docusign\TransferInformationQuestionnaireCondition;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Entity\TransferInformation;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Event\WorkflowEvent;
use Wealthbot\ClientBundle\Form\Handler\BankInformationFormHandler;
use Wealthbot\ClientBundle\Form\Handler\TransferInformationFormHandler;
use Wealthbot\ClientBundle\Form\Type\BankInformationFormType;
use Wealthbot\ClientBundle\Form\Type\DashboardSystemAccountsFormType;
use Wealthbot\ClientBundle\Form\Type\TransferFundingDistributingFormType;
use Wealthbot\ClientBundle\Form\Type\TransferInformationFormType;
use Wealthbot\ClientBundle\Form\Type\TransferReviewFormType;
use Wealthbot\ClientBundle\Manager\SystemAccountManager;
use Wealthbot\ClientBundle\Repository\ClientAccountRepository;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Entity\User;

class DashboardTransferController extends BaseTransferController
{
    public function selectSystemAccountAction(Request $request)
    {
        $client = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');
        $systemAccountRepo = $em->getRepository('WealthbotClientBundle:SystemAccount');

        /** @var ClientAccount $account */
        $account = $repo->find($request->get('account_id'));
        if (!$account || $account->getClient() !== $this->getUser()) {
            throw $this->createNotFoundException(sprintf('Account with id : %s does not exist.'));
        }

        $group = $account->getGroupName();
        if ($group === AccountGroup::GROUP_EMPLOYER_RETIREMENT) {
            $clientAccounts = $repo->findConsolidatedAccountsByClientId($client->getId());

            return $this->getJsonResponse([
                'status' => 'success',
                'account_table' => $this->renderView('WealthbotClientBundle:Transfer:_accounts_list.html.twig', [
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
            ($group === AccountGroup::GROUP_FINANCIAL_INSTITUTION || $group === AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)
        ) {
            $form = $this->createForm(new DashboardSystemAccountsFormType($account, $systemAccounts));

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
                $selectedAccount = count($systemAccounts) === 1 ? $systemAccounts[0] : null;

                return $this->render('WealthbotClientBundle:DashboardTransfer:select_system_account.html.twig', [
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
                // Only if bank information has been created on the client account management page
                if ($accountId === 0) {
                    $event = new WorkflowEvent($client, $bankInfo, Workflow::TYPE_PAPERWORK);
                    $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);
                }
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'form' => $this->renderView(
                        'WealthbotClientBundle:DashboardTransfer:_create_bank_account_form.html.twig', [
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

    public function reviewAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');
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
        $form = $this->createForm(new TransferReviewFormType($documentSignatureManager, $account));

        $isPreSaved = $request->isXmlHttpRequest();
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $account->setProcessStep(ClientAccount::PROCESS_STEP_FINISHED_APPLICATION);
                foreach ($account->getConsolidatedAccounts() as $consolidated) {
                    $consolidated->setProcessStep(ClientAccount::PROCESS_STEP_FINISHED_APPLICATION);
                }

                $account->setStepAction(ClientAccount::STEP_ACTION_REVIEW);
                $account->setIsPreSaved($isPreSaved);

                $group = $account->getGroupName();
                if (($group === AccountGroup::GROUP_FINANCIAL_INSTITUTION || $group === AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT) &&
                    $this->getSystemTransferAccount()
                ) {
                    /** @var SystemAccount $systemAccount */
                    $systemAccount = $em->getRepository('WealthbotClientBundle:SystemAccount')->find($this->getSystemTransferAccount());
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
                if (!$hasNotOpenedAccounts && ($profile->getRegistrationStep() !== 7)) {
                    $profile->setRegistrationStep(7);
                    $profile->setClientStatus(Profile::CLIENT_STATUS_CLIENT);
                    $em->persist($profile);
                    $em->flush();
                }

                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_REVIEW);

                if ($isPreSaved) {
                    return $this->getJsonResponse(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->getJsonResponse(['status' => 'error']);
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

    public function transferAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');
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
            throw new AccessDeniedException('Current account has not this step.');
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
        $form = $this->createForm(new TransferInformationFormType($adm, $isPreSaved), $information);
        $formHandler = new TransferInformationFormHandler($form, $request, $em, ['client' => $client]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
                /** @var TransferInformation $information */
                $information = $form->getData();

                $account->setStepAction(ClientAccount::STEP_ACTION_TRANSFER);
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
                    return $this->getJsonResponse(['status' => 'success', 'redirect_url' => $redirectUrl]);
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
                return $this->getJsonResponse([
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
