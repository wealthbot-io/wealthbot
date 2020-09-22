<?php

namespace App\Controller\Client;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Entity\CustodianMessage;
use App\Event\ClientEvents;
use App\Docusign\TransferInformationConsolidatorCondition;
use App\Docusign\TransferInformationCustodianCondition;
use App\Docusign\TransferInformationPolicyCondition;
use App\Docusign\TransferInformationQuestionnaireCondition;
use App\Entity\AccountContribution;
use App\Entity\AccountGroup;
use App\Entity\Beneficiary;
use App\Entity\ClientAccount;
use App\Entity\RetirementPlanInformation;
use App\Entity\SystemAccount;
use App\Entity\TransferCustodianQuestionAnswer;
use App\Entity\TransferInformation;
use App\Entity\Workflow;
use App\Event\WorkflowEvent;
use App\Form\Handler\TransferBasicFormHandler;
use App\Form\Handler\TransferInformationFormHandler;
use App\Form\Handler\TransferPersonalFormHandler;
use App\Form\Type\AccountGroupsFormType;
use App\Form\Type\AccountOwnerPersonalInformationFormType;
use App\Form\Type\AccountOwnerReviewInformationFormType;
use App\Form\Type\BankInformationFormType;
use App\Form\Type\BeneficiariesCollectionFormType;
use App\Form\Type\RetirementPlanInfoFormType;
use App\Form\Type\TransferBasicFormType;
use App\Form\Type\TransferFundingDistributingFormType;
use App\Form\Type\TransferInformationFormType;
use App\Form\Type\TransferReviewFormType;
use App\Manager\SystemAccountManager;
use App\Model\AccountOwnerInterface;
use App\Repository\ClientAccountRepository;
use App\Entity\RiaCompanyInformation;
use App\Entity\Document;
use App\Entity\Profile;
use App\Entity\User;

class BaseTransferController extends Controller
{
    use AclController;

    public function account(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $user = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $user->getId()]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $action = $account->getStep();
        $process = $account->getProcessStep();

        if ((ClientAccount::STEP_ACTION_TRANSFER === $action || ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING === $action)
            && $account->hasGroup(AccountGroup::GROUP_FINANCIAL_INSTITUTION)
            && ClientAccount::PROCESS_STEP_FINISHED_APPLICATION !== $process
        ) {
            return $this->redirect($this->generateUrl($this->getRoutePrefix().'transfer_transfer_account', ['account_id' => $account->getId()]));
        }

        if (!$action) {
            $isPreSaved = true;
            $isCurrentRetirement = $repo->findRetirementAccountById($account->getId()) ? true : false;

            if ($isCurrentRetirement) {
                $action = ClientAccount::STEP_ACTION_CREDENTIALS;
            } else {
                $action = ClientAccount::STEP_ACTION_BASIC;
            }
        } else {
            $isPreSaved = $account->getIsPreSaved();
        }

        if ($isPreSaved) {
            return $this->redirect($this->generateUrl($this->getRouteUrl($action), ['account_id' => $account->getId()]));
        } elseif (
            (
                !$account->hasGroup(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
                && ClientAccount::PROCESS_STEP_FINISHED_APPLICATION === $process
            ) || (
                $account->hasGroup(AccountGroup::GROUP_EMPLOYER_RETIREMENT)
                && ClientAccount::PROCESS_STEP_COMPLETED_CREDENTIALS === $process
            )
        ) {
            return $this->redirect($this->generateUrl('rx_client_transfer_applications'));
        }

        return $this->redirect($this->getRedirectUrl($account, $action));
    }

    public function progressMenu(ClientAccount $account, $step)
    {
        if (ClientAccount::STEP_ACTION_ADDITIONAL_BASIC === $step) {
            $step = ClientAccount::STEP_ACTION_BASIC;
        } elseif (ClientAccount::STEP_ACTION_ADDITIONAL_PERSONAL === $step) {
            $step = ClientAccount::STEP_ACTION_PERSONAL;
        }

        $adm = $this->get('wealthbot_docusign.account_docusign.manager');
        $group = $account->getGroupName();
        $isRothOrIra = $account->isRothIraType();

        if (AccountGroup::GROUP_EMPLOYER_RETIREMENT !== $group) {
            $items = [
                'names' => ['Basics', 'Personal'],
                'steps' => [ClientAccount::STEP_ACTION_BASIC, ClientAccount::STEP_ACTION_PERSONAL],
            ];

            if ($account->hasGroup(AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT) || $isRothOrIra || $account->isTraditionalIraType()) {
                $items['names'][] = 'Beneficiaries';
                $items['steps'][] = ClientAccount::STEP_ACTION_BENEFICIARIES;
            }

            if ($account->hasGroup(AccountGroup::GROUP_FINANCIAL_INSTITUTION)) {
                $items['names'][] = 'Transfer Screen';
                $items['steps'][] = ClientAccount::STEP_ACTION_TRANSFER;
            }

            if ($account->hasGroup(AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)) {
                $items['names'][] = 'Your Rollover';
                $items['steps'][] = ClientAccount::STEP_ACTION_ROLLOVER;
            }

            $hasFunding = $account->hasFunding();
            $hasDistributing = $account->hasDistributing();

            if ($hasFunding && $hasDistributing) {
                $items['names'][] = 'Funding & Distributing';
                $items['steps'][] = ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING;
            } elseif ($hasFunding || $account->hasGroup(AccountGroup::GROUP_DEPOSIT_MONEY)) {
                $items['names'][] = 'Funding';
                $items['steps'][] = ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING;
            } elseif ($hasDistributing) {
                $items['names'][] = 'Distributing';
                $items['steps'][] = ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING;
            } elseif ($adm->hasElectronicallySignError($account)) {
                $items['names'][] = 'Funding';
                $items['steps'][] = ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING;
            }

            $items['names'][] = 'Review & Sign';
            $items['steps'][] = ClientAccount::STEP_ACTION_REVIEW;
        } else {
            $items = [
                'names' => ['Need Credentials'],
                'steps' => [ClientAccount::STEP_ACTION_CREDENTIALS],
            ];
        }

        return $this->render($this->getTemplate('progress_menu.html.twig'), [
            'items' => $items,
            'active' => array_search($step, $items['steps']),
        ]);
    }

    public function basic(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $client = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        $primaryApplicant = $account->getPrimaryApplicant();
        $isPreSaved = $request->isXmlHttpRequest();

        $form = $this->createForm(TransferBasicFormType::class, $primaryApplicant, [
            'is_pre_save' => $isPreSaved,
            'secondaryApplicant'=> $account->getSecondaryApplicant(),
            'profile' => $this->getUser()->getProfile(),
        ]);
        $formHandler = new TransferBasicFormHandler($form, $request, $em);

        if ($request->isMethod('post')) {
            $process = $formHandler->process($account, $isPreSaved);
            if ($process) {
                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_BASIC);

                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render($this->getTemplate('basic.html.twig'), [
            'client' => $client,
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    public function additionalBasic(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        /** @var $client User */
        $client = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy([
            'id' => $request->get('account_id'),
            'client_id' => $client->getId(),
        ]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        if (!$repo->isJointAccount($account)) {
            return $this->createAccessDeniedException('Current account has not this step.');
        }

        $secondaryApplicant = $account->getSecondaryApplicant();
        if (!$secondaryApplicant) {
            throw $this->createNotFoundException('Account does not have secondary applicant.');
        }

        $isPreSaved = $request->isXmlHttpRequest();
        $form = $this->createForm(TransferBasicFormType::class, $secondaryApplicant, [
            'secondaryApplicant'=> $secondaryApplicant,
            'is_pre_save' => $isPreSaved,
            'profile' => $this->getUser()->getProfile()
            ]);
        $formHandler = new TransferBasicFormHandler($form, $request, $em);

        if ($request->isMethod('post')) {
            $process = $formHandler->process($account, $isPreSaved);

            if ($process) {
                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_ADDITIONAL_BASIC);

                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render($this->getTemplate('additional_basic.html.twig'), [
            'client' => $client,
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    public function personal(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $client = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy([
            'id' => $request->get('account_id'),
            'client_id' => $client->getId(),
        ]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        $primaryApplicant = $account->getPrimaryApplicant();
        $isPreSaved = $request->isXmlHttpRequest();

        $isRollover = (AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT === $account->getGroupName());
        $isRothOrIra = ($repo->isRothAccount($account) || $repo->isIraAccount($account));
        $withMaritalStatus = ($isRollover || $isRothOrIra);

        $form = $this->createForm(AccountOwnerPersonalInformationFormType::class, $primaryApplicant, [
            'class' => 'App\Entity\ClientAccountOwner',
            'owner' => $this->getUser(),
            'primaryAccount'=>$this->getUser()->getProfile(), 'isPreSaved'=> $isPreSaved, 'withMaterialStatus' => $withMaritalStatus]);
        $formHandler = new TransferPersonalFormHandler($form, $request, $em, ['validator' => $this->get('validator')]);

        if ($request->isMethod('post')) {
            $process = $formHandler->process($account, $withMaritalStatus);
            if ($process) {
                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_PERSONAL);

                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render($this->getTemplate('personal.html.twig'), [
            'client' => $client,
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    public function additionalPersonal(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        /** @var $client User */
        $client = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy([
                'id' => $request->get('account_id'),
                'client_id' => $client->getId(),
            ]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        if (!$repo->isJointAccount($account)) {
            return $this->createAccessDeniedException('Current account has not this step.');
        }

        $secondaryApplicant = $account->getSecondaryApplicant();
        if (!$secondaryApplicant) {
            throw $this->createNotFoundException('Account does not have secondary applicant.');
        }

        $isPreSaved = $request->isXmlHttpRequest();
        $form = $this->createForm(AccountOwnerPersonalInformationFormType::class, $account, [
            'class' => 'App\Entity\ClientAccountOwner',
            'owner' => $this->getUser(),
            'primaryAccount'=>$account, 'isPreSaved'=> $isPreSaved, 'withMaterialStatus' => true]);
        $formHandler = new TransferPersonalFormHandler($form, $request, $em);

        if ($request->isMethod('post')) {
            $process = $formHandler->process($account);
            if ($process) {
                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_ADDITIONAL_PERSONAL);

                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render($this->getTemplate('additional_personal.html.twig'), [
            'client' => $client,
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    public function beneficiaries(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        /** @var $client User */
        $client = $this->getUser();
        $profile = $client->getProfile();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy([
                'id' => $request->get('account_id'),
                'client_id' => $client->getId(),
            ]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        $beneficiaries = $account->getBeneficiaries();
        if (!$beneficiaries->count()) {
            if (Profile::CLIENT_MARITAL_STATUS_MARRIED === $profile->getMaritalStatus()) {
                $stepActionsKeys = array_flip(array_keys(ClientAccount::getStepActionChoices()));

                if ($stepActionsKeys[$account->getStep()] < $stepActionsKeys[ClientAccount::STEP_ACTION_BENEFICIARIES]) {
                    $beneficiary = $this->buildBeneficiaryByClient($client);
                }
            }

            if (!isset($beneficiary)) {
                $beneficiary = new Beneficiary();
            }

            $beneficiary->setAccount($account);
            $beneficiaries->add($beneficiary);
        }

        $isPreSaved = $request->isXmlHttpRequest();
        $form = $this->createForm(BeneficiariesCollectionFormType::class, $isPreSaved);
        $form->get('beneficiaries')->setData($beneficiaries);

        $originalBeneficiaries = [];
        foreach ($beneficiaries as $item) {
            $originalBeneficiaries[] = $item;
        }

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $beneficiaries = $form['beneficiaries']->getData();

                foreach ($beneficiaries as $beneficiary) {
                    $beneficiary->setAccount($account);
                    $em->persist($beneficiary);

                    foreach ($originalBeneficiaries as $key => $toDel) {
                        if ($beneficiary->getId() === $toDel->getId()) {
                            unset($originalBeneficiaries[$key]);
                        }
                    }
                }

                foreach ($originalBeneficiaries as $beneficiary) {
                    $account->removeBeneficiarie($beneficiary);

                    $em->remove($beneficiary);
                }
                $em->flush();
                $em->clear();

                $account->setStep(ClientAccount::STEP_ACTION_BENEFICIARIES);
                $account->setIsPreSaved($isPreSaved);

                $em->persist($account);
                $em->flush();

                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_BENEFICIARIES);

                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render($this->getTemplate('beneficiaries.html.twig'), [
            'client' => $client,
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    public function fundingDistributing(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');
        $documentSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $repo = $em->getRepository('App\Entity\ClientAccount');
        $custodianMessagesRepo = $em->getRepository('App\Entity\CustodianMessage');

        /** @var User $client */
        $client = $this->getUser();
        $riaCompanyInformation = $client->getRiaCompanyInformation();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        $transferFunding = $account->getAccountContribution();
        if (!$transferFunding) {
            $transferFunding = new AccountContribution();
            $transferFunding->setAccount($account);
        }

        $isPreSaved = $request->isXmlHttpRequest();
        $formData = ['funding' => $transferFunding];
        $form = $this->createForm(TransferFundingDistributingFormType::class, $formData, ['em' => $em, 'account' => $account, 'isPreSaved' => $isPreSaved]);
        $bankInfoForm = $this->createForm(BankInformationFormType::class);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if ($account->hasFunding() ||
                    $account->hasGroup(AccountGroup::GROUP_DEPOSIT_MONEY) ||
                    $adm->hasElectronicallySignError($account)
                ) {
                    $transferFunding = $form->get('funding')->getData();
                    $em->persist($transferFunding);
                    $em->flush($transferFunding);
                } else {
                    $em->remove($transferFunding);
                }

                $account->setProcessStep(ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING);
                $account->setIsPreSaved($isPreSaved);

                $em->persist($account);
                $em->flush();

                $em->refresh($account);

                $consolidatedAccounts = $account->getConsolidatedAccountsCollection();
                $bankTransferAccounts = $consolidatedAccounts->getBankTransferredAccounts();
                if ($bankTransferAccounts->count()) {
                    $accountContribution = $account->getAccountContribution();
                    if (!$documentSignatureManager->isDocumentSignatureForObjectExist($accountContribution)) {
                        $documentSignatureManager->createSignature($accountContribution);
                    }
                }

                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING);

                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->json(['status' => 'error']);
            }
        }

        $hasDocusignError = false;
        if ($account->getTransferInformation()) {
            $adm = $this->get('wealthbot_docusign.account_docusign.manager');

            $isAllowedNonElectronicallyTransfer = $riaCompanyInformation->getAllowNonElectronicallySigning();
            $hasDocusignError = (!$isAllowedNonElectronicallyTransfer && !$adm->isUsedDocusign($account->getId()));
        }

        return $this->render($this->getTemplate('funding_distributing.html.twig'), [
            'client' => $client,
            'account' => $account,
            'transfer_funding' => $transferFunding,
            'form' => $form->createView(),
            'bank_info_form' => $this->renderView($this->getTemplate('_create_bank_account_form.html.twig'), [
                'form' => $bankInfoForm->createView(),
                'account_id' => $account->getId(),
            ]),
            'messages' => $custodianMessagesRepo->getAssocByCustodianId($riaCompanyInformation->getCustodianId()),
            'has_docusign_error' => $hasDocusignError,
        ]);
    }

    public function rollover(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $custodianMessagesRepo = $em->getRepository('App\Entity\CustodianMessage');

        $client = $this->getUser();
        /** @var RiaCompanyInformation $riaCompanyInformation */
        $riaCompanyInformation = $client->getRia()->getRiaCompanyInformation();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy([
            'id' => $request->get('account_id'),
            'client_id' => $client->getId(),
        ]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

        if (SystemAccount::TYPE_ROTH_IRA !== $account->getSystemType() &&
            !$account->hasGroup(AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)
        ) {
            return $this->createAccessDeniedException('Current account has not this step.');
        }

        /** @var CustodianMessage $rolloverMessage */
        $rolloverMessage = $custodianMessagesRepo->findOneByCustodianIdAndType(
            $riaCompanyInformation->getCustodianId(),
            CustodianMessage::TYPE_ROLLOVER
        );

        if (!$this->get('session')->get('is_send_email', false)) {
            if ($rolloverMessage) {
                $this->get('wealthbot.mailer')->sendClientRolloverInstruction401Email($account, $rolloverMessage->getMessage());
                $this->get('session')->set('is_send_email', true);
            }
        }

        if ($request->isMethod('post')) {
            $account->setStep(ClientAccount::STEP_ACTION_ROLLOVER);
            $account->setIsPreSaved(false);

            $em->persist($account);
            $em->flush();

            $this->get('session')->remove('is_send_email');

            return $this->redirect($this->getRedirectUrl($account, ClientAccount::STEP_ACTION_ROLLOVER));
        }

        return $this->render($this->getTemplate('rollover.html.twig'), [
            'client' => $client,
            'account' => $account,
            'rollover_message' => $rolloverMessage,
        ]);
    }

    // TODO: Method needs refactoring. Move common code with the method reviewAction
    public function credentials(Request $request)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $client = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $isCurrentRetirement = $repo->findRetirementAccountById($account->getId()) ? true : false;
        if (!$isCurrentRetirement) {
            return $this->createAccessDeniedException('Not current retirement accounts has not this step.');
        }

        $planInfo = $account->getRetirementPlanInfo();
        if (!$planInfo) {
            $planInfo = new RetirementPlanInformation();
            $planInfo->setAccount($account);
            $planInfo->setFinancialInstitution($account->getFinancialInstitution());
        }

        $isPreSaved = $request->isXmlHttpRequest();
        $form = $this->createForm(RetirementPlanInfoFormType::class, $planInfo);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $planInfo = $form->getData();

                $account->setProcessStep(ClientAccount::PROCESS_STEP_COMPLETED_CREDENTIALS);
                $account->setStep(ClientAccount::STEP_ACTION_CREDENTIALS);
                $account->setIsPreSaved($isPreSaved);

                $em->persist($planInfo);
                $em->persist($account);
                $em->flush();

                $event = new WorkflowEvent($client, $account, Workflow::TYPE_ALERT);
                $this->get('event_dispatcher')->dispatch(ClientEvents::CLIENT_WORKFLOW, $event);

                // Create system account for client account
                /** @var $systemAccountManager SystemAccountManager */
                $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
                $systemAccountManager->createSystemAccountForClientAccount($account);

                // If client complete all accounts
                $hasNotOpenedAccounts = $repo->findOneNotOpenedAccountByClientId($client->getId()) ? true : false;
                $profile = $client->getProfile();
                if (!$hasNotOpenedAccounts && (7 !== $profile->getRegistrationStep())) {
                    $profile->setRegistrationStep(7);
                    $profile->setClientStatus(Profile::CLIENT_STATUS_CLIENT);
                    $em->persist($profile);
                    $em->flush();
                }

                $redirectUrl = $this->getRedirectUrl($account, ClientAccount::STEP_ACTION_CREDENTIALS);

                if ($isPreSaved) {
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl]);
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSaved) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render($this->getTemplate('credentials.html.twig'), [
            'client' => $client,
            'account' => $account,
            'form' => $form->createView(),
        ]);
    }

    // TODO: Method needs refactoring. Move common code with the method credentialsAction
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
        $form = $this->createForm(TransferReviewFormType::class, null, ['manager'=>$documentSignatureManager, 'account' => $account]);
        $notSignedApplicationsError = null;

        $isPreSaved = $request->isXmlHttpRequest();
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $account->setProcessStep(ClientAccount::PROCESS_STEP_FINISHED_APPLICATION);
                foreach ($account->getConsolidatedAccounts() as $consolidated) {
                    $consolidated->setProcessStep(ClientAccount::PROCESS_STEP_FINISHED_APPLICATION);
                }

                $account->setStep(ClientAccount::STEP_ACTION_REVIEW);
                $account->setIsPreSaved($isPreSaved);

                $em->persist($account);
                $em->flush($account);

                // Create system account for client account
                /** @var $systemAccountManager SystemAccountManager */
                $systemAccountManager = $this->get('wealthbot_client.system_account_manager');
                $systemAccountManager->createSystemAccountForClientAccount($account);

                // If client complete all accounts
                $hasNotOpenedAccounts = $repo->findOneNotOpenedAccountByClientId($client->getId()) ? true : false;
                $profile = $client->getProfile();
                if (!$hasNotOpenedAccounts && (7 !== $profile->getRegistrationStep())) {
                    $profile->setRegistrationStep(7);
                    $profile->setClientStatus(Profile::CLIENT_STATUS_CLIENT);

                    // Update client type
                    $clientSettings = $client->getClientSettings();
                    $clientSettings->setClientTypeCurrent();

                    $em->persist($profile);
                    $em->persist($clientSettings);
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

    public function reviewOwnerInformation(Request $request, $owner_id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $repository = $em->getRepository('App\Entity\ClientAccountOwner');

        $accountOwner = $repository->find($owner_id);
        if (!$accountOwner) {
            return $this->json(['status' => 'error', 'message' => 'Owner does not exist.']);
        }

        $owner = $accountOwner->getOwner();
        $form = $this->createForm(AccountOwnerReviewInformationFormType::class, $owner, [
            'owner' => $owner,
            'primaryAccount'=> $this->getUser(),
            'isPreSaved'=>true
        ]);

        $status = 'success';
        $content = $this->renderView(
            '/Client/Transfer/_review_owner_information_form.html.twig',
            ['form' => $form->createView(), 'owner' => $accountOwner]
        );

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var AccountOwnerInterface $data */
                $data = $form->getData();

                $em->persist($data->getObjectToSave());
                $em->flush();

                return $this->json(['status' => 'success']);
            } else {
                $status = 'error';
                $content = $this->renderView(
                    '/Client/Transfer/_review_owner_information_form.html.twig',
                    ['form' => $form->createView(), 'owner' => $accountOwner]
                );
            }
        }

        return $this->json(['status' => $status, 'content' => $content]);
    }

    public function transfer(Request $request)
    {
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');
        $documentSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $client = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        if (!$account) {
            $this->createNotFoundException('You have not account with id: '.$request->get('account_id').'.');
        }

        $this->denyAccessForCurrentRetirementAccount($account);

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

        /** @var ClientAccount $currentAccount */
        $currentAccount = $transferAccounts->get($accountIndex);

        $information = $currentAccount->getTransferInformation();
        if (!$information) {
            $information = new TransferInformation();
            $information->setClientAccount($currentAccount);
        }

        $isPreSaved = $request->isXmlHttpRequest();
        $form = $this->createForm(TransferInformationFormType::class, $information, ['adm'=>$adm,'isPreSaved'=> $isPreSaved]);
        $formHandler = new TransferInformationFormHandler($form, $request, $em, ['client' => $client]);

        if ($request->isMethod('post')) {
            if ($formHandler->process()) {
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
                    return $this->json(['status' => 'success', 'redirect_url' => $redirectUrl, 'route' => $this->getRouteUrl($this->get('wealthbot_client.transfer_screen_step.manager')->getNextStep($account, ClientAccount::STEP_ACTION_TRANSFER))]);
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

    public function updateTransferForm(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');

        $account = $em->getRepository('App\Entity\ClientAccount')->find($request->get('account_id'));
        if (!$account || $account->getClient() !== $this->getUser()) {
            return $this->json(['status' => 'error', 'message' => 'Account does not exist.']);
        }

        $accountIndex = $request->get('account_index', 1);
        $consolidatedAccounts = $account->getConsolidatedAccountsCollection();
        $transferAccounts = $consolidatedAccounts->getTransferAccounts();

        if ($transferAccounts->isEmpty()) {
            $this->createNotFoundException('You have not transfer accounts.');
        }
        if (!$transferAccounts->containsKey($accountIndex)) {
            throw $this->createNotFoundException('Page not found.');
        }

        $currentAccount = $transferAccounts->get($accountIndex);
        $transferInfo = $currentAccount->getTransferInformation();
        if (!$transferInfo) {
            $transferInfo = new TransferInformation();
            $transferInfo->setClientAccount($currentAccount);
        }

        if ($request->isMethod('post')) {
            $form = $this->createForm(TransferInformationFormType::class, $transferInfo, ['adm'=> $adm, 'isPreSaved'=> true]);
            $form->handleRequest($request);

            /** @var TransferInformation $transferInfo */
            $transferInfo = $form->getData();
            $transferInfo->setStatementDocument(null);

            // Remove answer if it value is null
            /** @var TransferCustodianQuestionAnswer $answer */
            foreach ($transferInfo->getQuestionnaireAnswers() as $answer) {
                if (null === $answer->getValue()) {
                    $transferInfo->removeQuestionnaireAnswer($answer);
                }
            }

            $isDocusignAllowed = $adm->isDocusignAllowed($transferInfo, [
                new TransferInformationCustodianCondition(),
                new TransferInformationPolicyCondition(),
                new TransferInformationQuestionnaireCondition(),
                new TransferInformationConsolidatorCondition(),
            ]);

            $adm->setIsUsedDocusign($currentAccount, $isDocusignAllowed);

            $form = $this->createForm(TransferInformationFormType::class, $transferInfo, ['adm'=>$adm,'isPreSaved'=> true]);
            $formView = $form->createView();

            return $this->json([
                'status' => 'success',
                'custodian_questions_fields' => $this->renderView(
                    '/Client/Transfer/_transfer_form_custodian_questions_fields.html.twig',
                    [
                        'form' => $formView,
                    ]
                ),
                'account_discrepancies_fields' => $this->renderView(
                    '/Client/Transfer/_transfer_form_account_discrepancies_fields.html.twig',
                    [
                        'form' => $formView,
                    ]
                ),
            ]);
        }

        return $this->json(
            ['status' => 'error', 'message' => 'The operation failed due to some errors.']
        );
    }

    public function back($account_id, $action, $id = 0)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $transferStepManager = $this->get('wealthbot_client.transfer_screen_step.manager');

        $client = $this->getUser();

        /** @var $account ClientAccount */
        $account = $repo->findOneBy(['id' => $account_id, 'client_id' => $client->getId()]);
        if (!$account) {
            $this->createNotFoundException(sprintf('You have not account with id: %s.', $account_id));
        }

        if ($id > 0) {
            $consolidatedAccount = $repo->findOneBy(['id' => $id, 'consolidator_id' => $account->getId()]);
        } else {
            $consolidatedAccount = null;
        }

        if ($consolidatedAccount && $account->getConsolidatedAccounts()) {
            $group = $consolidatedAccount->getGroupName();

            if (AccountGroup::GROUP_FINANCIAL_INSTITUTION === $group) {
                $transferAccounts = $account->getTransferConsolidatedAccounts();
                $key = $transferAccounts->indexOf($consolidatedAccount);

                if ($transferAccounts->containsKey($key - 1)) {
                    return $this->redirect($this->generateUrl($this->getRoutePrefix().'transfer_transfer_account', [
                        'account_id' => $account->getId(),
                        'account_index' => ($key),
                    ]));
                }
            }
        }

        try {
            $route = $this->getRouteUrl($transferStepManager->getPreviousStep($account, $action));
        } catch (\Exception $e) {
            throw $this->createNotFoundException($e->getMessage(), $e);
        }

        if ($account->getConsolidatedAccounts()) {
            if ($route === ($this->getRoutePrefix().'transfer_transfer_account')) {
                $transferCount = $account->getTransferConsolidatedAccounts()->count();

                return $this->redirect($this->generateUrl($this->getRoutePrefix().'transfer_transfer_account', [
                    'account_id' => $account->getId(),
                    'account_index' => $transferCount,
                ]));
            } elseif ($route === ($this->getRoutePrefix().'transfer_rollover')) {
                $rolloverCount = $account->getRolloverConsolidatedAccounts()->count();

                return $this->redirect($this->generateUrl($this->getRoutePrefix().'transfer_rollover', [
                    'account_id' => $account->getId(),
                    'account_index' => $rolloverCount,
                ]));
            }
        }

        if ($route === ($this->getRoutePrefix().'transfer')) {
            return $this->redirect($this->generateUrl($route));
        }

        return $this->redirect($this->generateUrl($route, ['account_id' => $account->getId()]));
    }

    public function finished()
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository  */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $client = $this->getUser();
        $hasNotOpenedAccounts = $repo->findOneNotOpenedAccountByClientId($client->getId()) ? true : false;

        $riaCompanyInformation = $client->getRia()->getRiaCompanyInformation();

        $data = ['groups' => $this->get('session')->get('client.accounts.groups')];
        $this->get('session')->set('client.accounts.is_consolidate_account', false);

        $form = $this->createForm(AccountGroupsFormType::class);

        return $this->render($this->getTemplate('finished.html.twig'), [
            'client' => $client,
            'form' => $form->createView(),
            'has_not_opened_accounts' => $hasNotOpenedAccounts,
            'ria_company_information' => $riaCompanyInformation,
        ]);
    }

    /**
     * Get next step of the transfer account process.
     *
     * @param \App\Entity\ClientAccount $account
     * @param string                       $action  current step of the transfer account process
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function getRedirectUrl(ClientAccount $account, $action)
    {
        $transferStepManager = $this->get('wealthbot_client.transfer_screen_step.manager');
        $route = $this->getRouteUrl($transferStepManager->getNextStep($account, $action));

        if ($route === ($this->getRoutePrefix().'transfer_finished')) {
            return $this->generateUrl($route);
        }

        return $this->generateUrl($route, ['account_id' => $account->getId()]);
    }

    private function buildBeneficiaryByClient(User $client)
    {
        $spouse = $client->getSpouse();
        $profile = $client->getProfile();

        $beneficiary = new Beneficiary();

        $beneficiary->setFirstName($spouse->getFirstName());
        $beneficiary->setMiddleName($spouse->getMiddleName());
        $beneficiary->setLastName($spouse->getLastName());
        $beneficiary->setBirthDate($spouse->getBirthDate());
        $beneficiary->setStreet($profile->getStreet());
        $beneficiary->setState($profile->getState());
        $beneficiary->setCity($profile->getCity());
        $beneficiary->setZip($profile->getZip());
        $beneficiary->setRelationship('Spouse');
        $beneficiary->setShare(100);

        return $beneficiary;
    }

    /**
     * Ger route for action.
     *
     * @param string $action
     *
     * @return string route
     *
     * @throws \InvalidArgumentException
     */
    private function getRouteUrl($action)
    {
        switch ($action) {
            case '':
                $route = 'transfer';
                break;
            case ClientAccount::STEP_ACTION_BASIC:
                $route = 'transfer_basic';
                break;
            case ClientAccount::STEP_ACTION_ADDITIONAL_BASIC:
                $route = 'transfer_additional_basic';
                break;
            case ClientAccount::STEP_ACTION_PERSONAL:
                $route = 'transfer_personal';
                break;
            case ClientAccount::STEP_ACTION_ADDITIONAL_PERSONAL:
                $route = 'transfer_additional_personal';
                break;
            case ClientAccount::STEP_ACTION_BENEFICIARIES:
                $route = 'transfer_beneficiaries';
                break;
            case ClientAccount::STEP_ACTION_CREDENTIALS:
                $route = 'transfer_credentials';
                break;
            case ClientAccount::STEP_ACTION_FUNDING_DISTRIBUTING:
                $route = 'transfer_funding_distributing';
                break;
            case ClientAccount::STEP_ACTION_ROLLOVER:
                $route = 'transfer_rollover';
                break;
            case ClientAccount::STEP_ACTION_REVIEW:
                $route = 'transfer_review';
                break;
            case ClientAccount::STEP_ACTION_TRANSFER:
                $route = 'transfer_transfer_account';
                break;
            case ClientAccount::STEP_ACTION_FINISHED:
                $route = 'transfer_finished';
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid value for action : %s.', $action));
                break;
        }

        return $this->getRoutePrefix().$route;
    }

    protected function denyAccessForCurrentRetirementAccount(ClientAccount $account)
    {
        /** @var $em EntityManager */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $isCurrentRetirement = $repo->findRetirementAccountById($account->getId()) ? true : false;
        if ($isCurrentRetirement) {
            return $this->createAccessDeniedException('Current retirement accounts has not this step.');
        }
    }

    /**
     * Get prefix for routing.
     *
     * @return string
     */
    protected function getRoutePrefix()
    {
        return '';
    }

    /**
     * Get template.
     *
     * @param $templateName
     *
     * @return string
     */
    protected function getTemplate($templateName)
    {
        $params = [
            'Client',
            $this->getViewsDir(),
            $templateName,
        ];

        return implode('/', $params);
    }

    /**
     * Returns true if array contains ClientAccount objects with sas cache property value more than 0
     * and false otherwise.
     *
     * @param array $accounts array of ClientAccount objects
     *
     * @return bool
     */
    protected function containsSasCash(array $accounts = [])
    {
        foreach ($accounts as $account) {
            if ($account->getSasCash() && $account->getSasCash() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns class name without 'Controller' substring.
     *
     * @return string
     */
    private function getViewsDir()
    {
        $class = explode('\\', get_class($this));
        $class = end($class);

        return substr($class, 0, -strlen('Controller'));
    }
}
