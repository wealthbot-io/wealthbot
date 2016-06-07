<?php

namespace Wealthbot\ClientBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Wealthbot\AdminBundle\Exception\DataAlreadyExistsException;
use Wealthbot\ClientBundle\Entity\AccountGroupType;
use Wealthbot\ClientBundle\Entity\AccountOutsideFund;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\TransferCustodian;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Form\Handler\ClientAccountFormHandler;
use Wealthbot\ClientBundle\Form\Handler\ClientAccountOwnerFormHandler;
use Wealthbot\ClientBundle\Form\Handler\ClientQuestionsFormHandler;
use Wealthbot\ClientBundle\Form\Handler\OutsideFundFormHandler;
use Wealthbot\ClientBundle\Form\Type\AccountGroupsFormType;
use Wealthbot\ClientBundle\Form\Type\AccountTypesFormType;
use Wealthbot\ClientBundle\Form\Type\ClientAccountFormType;
use Wealthbot\ClientBundle\Form\Type\ClientAccountOwnerFormType;
use Wealthbot\ClientBundle\Form\Type\ClientProfileFormType;
use Wealthbot\ClientBundle\Form\Type\ClientQuestionsFormType;
use Wealthbot\ClientBundle\Form\Type\OutsideFundFormType;
use Wealthbot\ClientBundle\Form\Type\TypedClientAccountFormType;
use Wealthbot\ClientBundle\Model\AccountGroup;
use Wealthbot\ClientBundle\Repository\AccountOutsideFundRepository;
use Wealthbot\ClientBundle\Repository\ClientAccountRepository;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Entity\User;

class ProfileController extends Controller
{
    use AclController;

    const ACCOUNT_STEP_ACCOUNT_GROUP = 1;
    const ACCOUNT_STEP_ACCOUNT_GROUP_TYPE = 2;
    const ACCOUNT_STEP_ACCOUNT_UPDATE_FORM = 3;
    const ACCOUNT_STEP_ACCOUNT_OWNER_FORM = 4;

    public function indexAction($name)
    {
        return $this->render('WealthbotClientBundle:Default:index.html.twig', ['name' => $name]);
    }

    public function stepOneAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $this->getUser();

        $profile = $user->getProfile();
        if (!$profile) {
            $profile = new Profile();
            $profile->setUser($user);
        }

        $isPreSave = ($request->isXmlHttpRequest() || $request->get('is_pre_save'));

        $form = $this->createForm(new ClientProfileFormType($isPreSave), $profile);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $profile = $form->getData();
                $spouse = $user->getSpouse();

                if ($profile->getMaritalStatus() === Profile::CLIENT_MARITAL_STATUS_MARRIED) {
                    $spouse->setClient($user);
                } else {
                    $user->removeAdditionalContact($spouse);
                    $em->remove($spouse);
                }

                if (!$isPreSave) {
                    $profile->setRegistrationStep(1);
                }

                $em->persist($profile);
                $em->persist($user);
                $em->flush();

                if ($isPreSave && $request->isXmlHttpRequest()) {
                    return $this->getJsonResponse(['status' => 'success']);
                }

                if ($profile->getClientSource() === Profile::CLIENT_SOURCE_IN_HOUSE) {
                    $redirectUrl = $this->generateUrl('rx_client_portfolio');
                } else {
                    $redirectUrl = $this->generateUrl('rx_client_profile_step_two');
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSave && $request->isXmlHttpRequest()) {
                return $this->getJsonResponse(['status' => 'error']);
            }
        }

        return $this->render('WealthbotClientBundle:Profile:step_one.html.twig', [
            'form' => $form->createView(),
            'ria_company_information' => $user->getProfile()->getRia()->getRiaCompanyInformation(),
        ]);
    }

    public function stepTwoAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user || !$user->hasRole('ROLE_CLIENT')) {
            throw $this->createNotFoundException('Client does not exist.');
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');
        $workflowManager = $this->get('wealthbot.workflow.manager');

        $isPreSave = $request->isXmlHttpRequest();
        $form = $this->createForm(new ClientQuestionsFormType($em, $user, $isPreSave));

        if ($request->isMethod('post')) {
            $formHandler = new ClientQuestionsFormHandler($form, $request, $em, [
                'client_portfolio_manager' => $clientPortfolioManager,
            ]);

            try {
                $isProcess = $formHandler->process($user);
            } catch (NotFoundHttpException $e) {
                throw $this->createNotFoundException($e->getMessage(), $e);
            }

            if ($isProcess) {
                if (!$isPreSave) {
                    $user->getProfile()->setRegistrationStep(2);
                }

                $em->persist($user);
                $em->flush();

                if ($isPreSave) {
                    return $this->getJsonResponse(['status' => 'success']);
                }

                return $this->redirect($this->generateUrl('rx_client_profile_step_three'));
            } elseif ($isPreSave) {
                return $this->getJsonResponse(['status' => 'error']);
            }
        }

        return $this->render('WealthbotClientBundle:Profile:step_two.html.twig', [
            'form' => ($form ? $form->createView() : $form),
            'ria_company_information' => $user->getRiaCompanyInformation(),
        ]);
    }

    public function stepThreeAction(Request $request)
    {
        $this->removeAccountStep();

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(new AccountGroupsFormType($user));

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $group = $data['groups'];

                $this->setAccountGroup($group);

                $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_GROUP);

                return $this->getJsonResponse(
                    [
                        'status' => 'success',
                        'form' => $this->getAccountFormByGroup($group),
                    ]
                );
            } else {
                return $this->getJsonResponse(['status' => 'error']);
            }
        }

        return $this->render('WealthbotClientBundle:Profile:step_three.html.twig', [
            'form' => $form->createView(),
            'client' => $user,
            'ria_company_information' => $user->getProfile()->getRia()->getRiaCompanyInformation(),

        ]);
    }

    public function checkAccountsSumAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $em = $this->get('doctrine.orm.entity_manager');

        /** @var User $client */
        /* @var User $ria */
        $client = $this->getUser();
        $ria = $client->getRia();

        /** @var ClientAccountRepository $accountsRepo */
        $accountsRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');
        $riaMinAssetSize = round($ria->getRiaCompanyInformation()->getMinAssetSize(), 2);
        $total = $accountsRepo->getTotalScoreByClientId($client->getId());

        if ($riaMinAssetSize > round($total['value'], 2)) {
            return $this->getJsonResponse(
                [
                    'status' => 'error',
                    'message' => sprintf('You must invest at least $%s with us.', number_format($riaMinAssetSize)),
                ]
            );
        }

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function showAccountOwnerFormAction(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();
        $group = $this->getAccountGroup();

        if (!$client->isMarried() && $this->getAccountType() !== 'joint account') {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Client does not married.']);
        }

        if (null === $group) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Select type of account.']);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $form = $this->createForm(new ClientAccountOwnerFormType($client, $em, ($this->getAccountType() === 'joint account')));
        $formHandler = new ClientAccountOwnerFormHandler($form, $request, $em, $client);

        $owners = $formHandler->process();

        if (empty($owners)) {
            return $this->getJsonResponse([
                'status' => 'error',
                'form' => $this->renderView('WealthbotClientBundle:Profile:_account_owner_form.html.twig', [
                    'form' => $form->createView(),
                    'client' => $client,
                ]),
            ]);
        } else {
            $this->setAccountOwners($owners);
            $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_UPDATE_FORM);

            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $this->getAccountOwnerFormByGroup($group),
            ]);
        }
    }

    public function updateAccountOwnerFormAction(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();
        $form = $this->createForm(
            new ClientAccountOwnerFormType(
                $client,
                $this->get('doctrine.orm.entity_manager'),
                ($this->getAccountType() === 'joint account')
            )
        );

        $form->handleRequest($request);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Profile:_other_account_owner_form.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
            ]),
        ]);
    }

    public function showDepositAccountFormAction(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        /** @var User $client */
        $client = $this->getUser();
        $group = $this->getAccountGroup();

        $depositAccountGroupForm = $this->createForm(new AccountTypesFormType($client, $group));
        $depositAccountGroupForm->handleRequest($request);

        if ($depositAccountGroupForm->isValid()) {

            /** @var AccountGroupType $groupType */
            $groupType = $depositAccountGroupForm->get('group_type')->getData();

            $this->setAccountType($groupType->getType()->getName());
            $this->setAccountGroupType($groupType);
            $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_GROUP_TYPE);

            $accountForm = $this->getAccountFormByGroupAndGroupType($group, $groupType);

            return $this->getJsonResponse([
                'status' => 'success',
                'form' => $accountForm,
            ]);
        }

        return $this->getJsonResponse(['status' => 'error']);
    }

    public function completeStepThreeAction()
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $client = $this->getUser();

        $client->getProfile()->setRegistrationStep(3);
        $em->persist($client);
        $em->flush();

        $this->get('wealthbot.mailer')->sendRiaClientSuggestedPortfolioEmail($client);

        $url = $this->generateUrl('rx_client_portfolio');

        return $this->redirect($url);
    }

    public function showAccountFormAction(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();

        $accountGroupForm = $this->createForm(new AccountGroupsFormType($client));
        $accountGroupForm->handleRequest($request);

        if ($accountGroupForm->isValid()) {
            $group = $this->getAccountGroup();

            if ($group === AccountGroup::GROUP_DEPOSIT_MONEY) {
                $form = $this->createForm(new AccountTypesFormType($client));

                return $this->getJsonResponse([
                    'status' => 'success',
                    'form' => $this->renderView('WealthbotClientBundle:Profile:_select_account_type_form.html.twig', [
                        'form' => $form->createView(),
                        'group' => $group,
                    ]),
                ]);
            }

            return $this->getJsonResponse([
                'status' => 'success',
                'form' => $this->getAccountFormView($client, $group),
            ]);
        }

        return $this->getJsonResponse(['status' => 'error']);
    }

    private function getTitleMessageForAccountForm($group, $groupType = null)
    {
        switch ($group) {
            case AccountGroup::GROUP_FINANCIAL_INSTITUTION:
                $message = 'Tell us about the account you will be transferring:';
                break;
            case AccountGroup::GROUP_DEPOSIT_MONEY:
                $message = 'Tell us about the '.($groupType ? $groupType->getType()->getName() : $this->getAccountType()).' account you will be opening:';
                break;
            case AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT:
                $message = 'Tell us about the account you will be rolling over:';
                break;
            case AccountGroup::GROUP_EMPLOYER_RETIREMENT:
                $message = 'Tell us about the account you would like advice for:';
                break;
            default:
                $message = 'Tell us about the account:';
                break;
        }

        return $message;
    }

    public function updateAccountFormAction(Request $request)
    {
        $client = $this->getUser();
        $group = $request->get('group');

        $form = $this->createForm(new ClientAccountFormType($client, $group, false));
        $form->handleRequest($request);

        $step = $this->getAccountStep();
        if ($step === self::ACCOUNT_STEP_ACCOUNT_UPDATE_FORM) {
            $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_OWNER_FORM);
        } else {
            $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_UPDATE_FORM);
        }

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Profile:_client_accounts_form_fields.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function accountsAction(Request $request)
    {
        $client = $this->getUser();
        $groups = $this->get('session')->get('client.accounts.groups');
        $group = $request->get('group');

        if (!is_array($groups) || !count($group)) {
            $this->get('session')->getFlashBag()->add('error', 'Choose types of accounts will we be managing for you.');

            return $this->redirect($this->generateUrl('rx_client_profile_step_three'));
        }

        if (!in_array($group, $groups)) {
            return $this->redirect($this->generateUrl('rx_client_profile_step_three_accounts', [
                'group' => $groups[0],
            ]));
        }

        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $clientAccountRepo ClientAccountRepository */
        $clientAccountRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        $accounts = $clientAccountRepo->findBy(['client_id' => $client->getId()]);

        $accountTypes = $em->getRepository('WealthbotClientBundle:AccountType')->findAll();
        $accountTypesArray = [];
        foreach ($accountTypes as $object) {
            $accountTypesArray[$object->getId()] = $object->getType();
        }

        $form = $this->createForm(new ClientAccountFormType($client, $group));
        $prevGroup = null;

        if ($request->isMethod('post')) {
            $groupIndex = array_search($group, $groups);

            if (array_key_exists($groupIndex + 1, $groups)) {
                $url = $this->generateUrl('rx_client_profile_step_three_accounts', ['group' => $groups[$groupIndex + 1]]);
            } else {
                $retirementAccounts = $clientAccountRepo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);

                if (is_array($retirementAccounts) && count($retirementAccounts)) {
                    $url = $this->generateUrl('rx_client_profile_step_three_accounts_funds');
                } else {
                    $client->getProfile()->setRegistrationStep(3);
                    $em->persist($client);
                    $em->flush();

                    // TODO: move to the mailer or remove if will not be used
                    //$this->sendRiaEmail($client);

                    $url = $this->generateUrl('rx_client_portfolio');
                }
            }

            return $this->redirect($url);
        }

        $groupValues = array_values($groups);
        $tmp = array_flip($groupValues);
        $tmpIndex = $tmp[$group];
        $prevGroup = array_key_exists($tmpIndex - 1, $groupValues) ? $groupValues[$tmpIndex - 1] : null;

        $total = $clientAccountRepo->getTotalScoreByClientId($client->getId());

        return $this->render('WealthbotClientBundle:Profile:step_three_accounts.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
            'accounts' => $accounts,
            'group' => $group,
            'prev_group' => $prevGroup,
            'account_types' => $accountTypesArray,
            'total' => $total,
        ]);
    }

    public function accountsFundsAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $repo ClientAccountRepository */
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        $client = $this->getUser();

        $retirementAccounts = $repo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);
        $accounts = $repo->findBy(['client_id' => $client->getId()]);
        $total = $repo->getTotalScoreByClientId($client->getId());

        if ($request->isMethod('post')) {
            $client->getProfile()->setRegistrationStep(3);
            $em->persist($client);
            $em->flush();

            // TODO: move to the mailer or remove if will not be used
            //$this->sendRiaEmail($client);

            return $this->redirect($this->generateUrl('rx_client_portfolio'));
        }

        return $this->render('WealthbotClientBundle:Profile:step_three_accounts_funds.html.twig', [
            'client' => $client,
            'accounts' => $accounts,
            'retirement_accounts' => $retirementAccounts,
            'total' => $total,
        ]);
    }

    public function createAccountAction(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();
        if (!$client || !$client->hasRole('ROLE_CLIENT')) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');

        $group = $request->get('group');
        $allowedGroups = AccountGroup::getGroupChoices();
        if (!in_array($group, $allowedGroups)) {
            throw new HttpException(400, 'Invalid group type');
        }
        $groupTypeId = null;
        $groupType = null;

        $clientAccount = new ClientAccount();

        if ($group === AccountGroup::GROUP_DEPOSIT_MONEY || $group === AccountGroup::GROUP_FINANCIAL_INSTITUTION) {
            $groupType = $this->getAccountGroupType();
            $clientAccount->setGroupType($groupType);
        }

        $form = $this->createForm(new TypedClientAccountFormType($em, $client, $groupType, $group), $clientAccount);
        $formHandler = new ClientAccountFormHandler($form, $request, $adm, $this->getAccountOwners(), $this->getIsConsolidateAccount());

        $process = $formHandler->process();
        if ($process) {
            $this->removeAccountGroup();
            $this->removeAccountType();
            $this->removeAccountGroupType();
            $this->removeAccountOwners();

            /** @var ClientAccount $clientAccount */
            $clientAccount = $form->getData();

            if ($group === 'employer_retirement') {
                $responseData = $this->processEmployerRetirementAccountForm($clientAccount);
            } else {
                $responseData = $this->processAccountForm();

                $isType = $clientAccount->getGroupName() === AccountGroup::GROUP_DEPOSIT_MONEY;
                $systemAccounts = $em->getRepository('WealthbotClientBundle:SystemAccount')->findByClientIdAndType($client->getId(), $clientAccount->getSystemType());

                $responseData['in_right_box'] = ($isType || count($systemAccounts) < 1) ? false : true;
                $responseData['transfer_url'] = $this->generateUrl(
                    'rx_client_dashboard_select_system_account',
                    ['account_id' => $clientAccount->getId()]
                );
            }

            $this->removeIsConsolidateAccount();

            $this->removeAccountStep();

            return $this->getJsonResponse($responseData);
        }

        $message = $this->getTitleMessageForAccountForm($group, $groupType);

        return $this->getJsonResponse([
            'status' => 'error',
            'form' => $this->renderView('WealthbotClientBundle:Profile:_client_accounts_form.html.twig', [
                'form' => $form->createView(),
                'group' => $group,
                'hide_submit_button' => true,
                'title_message' => $message,
            ]),
        ]);
    }

    private function processAccountForm()
    {
        return [
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Profile:_create_account_success.html.twig'),
            'show_accounts_table' => 1,
            'show_portfolio_button' => 1,
        ];
    }

    private function processEmployerRetirementAccountForm(ClientAccount $account)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->createForm(new OutsideFundFormType($em, $account));

        return [
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Profile:_retirement_account_funds.html.twig', [
                'account' => $account,
                'form' => $form->createView(),
                'show_title_message' => true,
            ]),
        ];
    }

    public function showSuccessMessageAction()
    {
        return $this->getJsonResponse($this->processAccountForm());
    }

    public function showAccountsTableAction()
    {
        $client = $this->getUser();
        if (!$client || !$client->hasRole('ROLE_CLIENT')) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $clientAccountRepo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $clientAccountRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');
        $total = $clientAccountRepo->getTotalScoreByClientId($client->getId());

        return $this->render('WealthbotClientBundle:Profile:_accounts_list.html.twig', [
            'client' => $client,
            'total' => $total,
            'show_action_btn' => true,
        ]);
    }

    public function editAccountAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $client = $this->getUser();
        if (!$client || !$client->hasRole('ROLE_CLIENT')) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $clientAccountRepo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');

        $clientAccountRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        $account = $clientAccountRepo->find($request->get('id'));
        if (!$account) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Account does not exist.']);
        }

        $form = $this->createForm(new ClientAccountFormType($client, $account->getGroupName()), $account);
        $formHandler = new ClientAccountFormHandler($form, $request, $adm);
        $process = $formHandler->process();

        if ($request->isMethod('post')) {
            if ($process) {
                $retirementAccounts = $clientAccountRepo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);
                $total = $clientAccountRepo->getTotalScoreByClientId($client->getId());

                return $this->getJsonResponse([
                        'status' => 'success',
                        'accounts' => $this->renderView('WealthbotClientBundle:Profile:_accounts_list.html.twig', [
                            'client' => $client,
                            'total' => $total,
                            'show_action_btn' => true,
                        ]),
                        'retirement_accounts' => $this->renderView('WealthbotClientBundle:Profile:_retirement_accounts_list.html.twig', [
                            'retirement_accounts' => $retirementAccounts,
                        ]),
                    ]);
            } else {
                $status = 'error';
            }
        } else {
            $status = 'success';
        }

        return $this->getJsonResponse([
                'status' => $status,
                'form' => $this->renderView('WealthbotClientBundle:Profile:_edit_client_account_form.html.twig', [
                    'form' => $form->createView(),
                    'account' => $account,
                ]),
            ]);
    }

    public function deleteAccountAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        /** @var $account ClientAccount */
        $account = $em->getRepository('WealthbotClientBundle:ClientAccount')->find($request->get('id'));

        if ($account) {
            $outsideFunds = $account->getAccountOutsideFunds();
            foreach ($outsideFunds as $fund) {
                $em->remove($fund);
            }
            $em->remove($account);
        } else {
            $message = 'Client account with id: '.$request->get('id').' does not exist.';

            if ($request->isXmlHttpRequest()) {
                return $this->getJsonResponse(['status' => 'error', 'message' => $message]);
            }

            $this->container->get('session')->getFlashBag()->add('error', $message);

            return $this->redirect($this->generateUrl('rx_client_profile_step_three'));
        }

        $em->flush();
        $message = 'Account has been deleted successfully.';

        $total = $repo->getTotalScoreByClientId($this->getUser()->getId());

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse([
                'status' => 'success',
                'message' => $message,
                'total' => $total,
            ]);
        }

        $this->container->get('session')->getFlashBag()->add('success', $message);

        return $this->redirect($this->generateUrl('rx_client_profile_step_three'));
    }

    public function outsideFundAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $user = $this->getUser();

        if (!$user || !$user->hasRole('ROLE_CLIENT')) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $account = $em->getRepository('WealthbotClientBundle:ClientAccount')->findOneBy([
            'id' => $request->get('account_id'),
        ]);

        if (!$account) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Retirement Account with id: '.$request->get('id').' does not exist.',
            ]);
        }

        $form = $this->createForm(new OutsideFundFormType($em, $account));

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView('WealthbotClientBundle:Profile:_retirement_account_funds.html.twig', [
                'account' => $account,
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function createOutsideFundAction(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();
        if (!$client || !$client->hasRole('ROLE_CLIENT')) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $formData = $request->get('outside_fund');
        $accountId = isset($formData['account_id']) ? $formData['account_id'] : null;
        if (!$accountId) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Not defined parameter account_id.']);
        }

        /** @var $account \Wealthbot\ClientBundle\Entity\ClientAccount $retirementAccount */
        $account = $em->getRepository('WealthbotClientBundle:ClientAccount')->findOneBy([
            'id' => $accountId,
        ]);
        if (!$account) {
            return $this->getJsonResponse(['status' => 'error', 'message' => 'Retirement Account with id: '.$accountId.' does not exist.']);
        }

        $form = $this->createForm(new OutsideFundFormType($em, $account));
        $formHandler = new OutsideFundFormHandler($form, $request, $em);

        try {
            $process = $formHandler->process($account);

            if ($process) {
                return $this->getJsonResponse([
                    'status' => 'success',
                    'content' => $this->renderView('WealthbotClientBundle:Profile:retirement_funds_list.html.twig', ['account' => $account]),
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'message' => 'Not valid.',
                    'content' => $this->renderView('WealthbotClientBundle:Profile:retirement_account_fund_form.html.twig', ['form' => $form->createView()]),
                ]);
            }
        } catch (DataAlreadyExistsException $e) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Account already has this fund.',
            ]);
        }
    }

    public function deleteOutsideAccountFundAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo AccountOutsideFundRepository */
        /* @var $accountRepo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotClientBundle:AccountOutsideFund');
        $accountRepo = $em->getRepository('WealthbotClientBundle:ClientAccount');

        $client = $this->getUser();

        $accountId = $request->get('account_id');
        $fundId = $request->get('fund_id');

        $account = $accountRepo->findOneBy(['id' => $accountId, 'client_id' => $client->getId()]);
        if (!$account) {
            return $this->getJsonResponse(
                ['status' => 'error', 'message' => sprintf('You have not account with id: %s.', $accountId)]
            );
        }

        /** @var AccountOutsideFund $accountOutsideFund */
        $accountOutsideFund = $repo->findOneBySecurityIdAndAccountId($fundId, $accountId);
        if (!$accountOutsideFund) {
            return $this->getJsonResponse(
                [
                    'status' => 'error',
                    'message' => sprintf('Account outside fund for account: %s does not exist.', $accountId),
                ]
            );
        }

        $em->remove($accountOutsideFund);
        $em->flush();

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function completeTransferCustodianAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $query = $request->get('query');

        $transferCustodians = $em->getRepository('WealthbotClientBundle:TransferCustodian')->createQueryBuilder('tc')
            ->where('tc.name LIKE :name')
            ->setParameter('name', '%'.$query.'%')
            ->getQuery()
            ->execute();

        $result = [];

        /** @var TransferCustodian $item */
        foreach ($transferCustodians as $item) {
            $card = [
                'id' => $item->getId(),
                'name' => $item->getName(),
            ];

            $result[] = $card;
        }

        return $this->getJsonResponse($result);
    }

    public function updateTransferInformationFormAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $client = $this->getUser();

        $form = $this->createForm(new TypedClientAccountFormType($em, $client, $this->getAccountGroupType(), null, false));
        $form->handleRequest($request);

        return $this->getJsonResponse([
            'status' => 'success',
            'content' => $this->renderView(
                'WealthbotClientBundle:Profile:_transfer_information_questionnaire_form_fields.html.twig',
                ['form' => $form->createView(), 'hide_errors' => true]
            ),
        ]);
    }

    public function stepThreeBackAction()
    {
        $step = $this->getAccountStep();

        if (!$step) {
            return $this->getJsonResponse([
                'status' => 'error',
                'redirect_url' => $this->generateUrl('rx_client_profile_step_two'),
            ]);
        }

        $group = $this->getAccountGroup();

        switch ($step) {
            case self::ACCOUNT_STEP_ACCOUNT_GROUP:
                /** @var User $user */
                $user = $this->getUser();
                if ($user->getClientAccounts()->count() > 0) {
                    $content = $this->renderView('WealthbotClientBundle:Profile:_create_account_success.html.twig');
                } else {
                    $content = '';
                }
                break;
            case self::ACCOUNT_STEP_ACCOUNT_GROUP_TYPE;
                $content = $this->getAccountFormByGroup($group);
                break;
            case self::ACCOUNT_STEP_ACCOUNT_UPDATE_FORM:
                $groupType = $this->getAccountGroupType();
                $content = $this->getAccountFormByGroupAndGroupType($group, $groupType);
                break;
            case self::ACCOUNT_STEP_ACCOUNT_OWNER_FORM:
                $content = $this->getAccountOwnerFormByGroup($group);
                break;
            default;
                $content = '';
                break;
        }

        $this->setAccountStep($step - 1);

        return $this->getJsonResponse([
            'status' => 'success',
            'step' => $step,
            'content' => $content,
        ]);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }

    private function getAccountFormByGroup($group)
    {
        $user = $this->getUser();

        if ($group === AccountGroup::GROUP_FINANCIAL_INSTITUTION || $group === AccountGroup::GROUP_DEPOSIT_MONEY) {
            $content = $this->renderView('WealthbotClientBundle:Profile:_select_account_type_form.html.twig', [
                'form' => $this->createForm(new AccountTypesFormType($user, $group), ['groups' => $group])->createView(),
                'group' => $group,
            ]);
        } elseif ($user->isMarried()) {
            $content = $this->getAccountOwnerFormView($user, $this->get('doctrine.orm.entity_manager'));
        } else {
            $content = $this->getAccountFormView($user, $group);
        }

        return $content;
    }

    private function getAccountFormByGroupAndGroupType($group, $groupType)
    {
        $user = $this->getUser();

        if (!$user->isMarried() && $this->getAccountType() !== 'joint account') {
            $form = $this->createForm(new TypedClientAccountFormType($this->get('doctrine.orm.entity_manager'), $user, $groupType, $group));
            $message = $this->getTitleMessageForAccountForm($group, $groupType);

            $content = $this->renderView('WealthbotClientBundle:Profile:_client_accounts_form.html.twig', [
                'form' => $form->createView(),
                'group' => $group,
                'hide_submit_button' => true,
                'title_message' => $message,
            ]);
        } else {
            $content = $this->getAccountOwnerFormView(
                $user,
                $this->get('doctrine.orm.entity_manager'),
                ($this->getAccountType() === 'joint account')
            );
        }

        return $content;
    }

    private function getAccountOwnerFormByGroup($group)
    {
        $user = $this->getUser();

        $accountForm = $this->createForm(new TypedClientAccountFormType($this->get('doctrine.orm.entity_manager'), $user, $this->getAccountGroupType(), $group));

        $content = $this->renderView('WealthbotClientBundle:Profile:_client_accounts_form.html.twig', [
            'form' => $accountForm->createView(),
            'group' => $group,
            'hide_submit_button' => true,
            'title_message' => $this->getTitleMessageForAccountForm($group),
        ]);

        return $content;
    }

    private function getAccountOwnerFormView(User $client, EntityManager $em, $isJoint = false)
    {
        $form = $this->createForm(new ClientAccountOwnerFormType($client, $em, $isJoint));

        return $this->renderView('WealthbotClientBundle:Profile:_account_owner_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    private function getAccountFormView(User $client, $group)
    {
        $form = $this->createForm(new ClientAccountFormType($client, $group));
        $message = $this->getTitleMessageForAccountForm($group);

        return $this->renderView('WealthbotClientBundle:Profile:_client_accounts_form.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
            'hide_submit_button' => true,
            'title_message' => $message,
        ]);
    }

    /**
     * Save account group in the session.
     *
     * @param $group
     */
    private function setAccountGroup($group)
    {
        $this->get('session')->set('client.accounts.account_group', $group);
    }

    /**
     * Get account group from session.
     *
     * @return mixed
     */
    private function getAccountGroup()
    {
        return $this->get('session')->get('client.accounts.account_group');
    }

    /**
     * Remove account group from session.
     */
    private function removeAccountGroup()
    {
        $this->get('session')->remove('client.accounts.account_group');
    }

    /**
     * Save account group type in the session.
     *
     * @param AccountGroupType $groupType
     */
    private function setAccountGroupType(AccountGroupType $groupType)
    {
        $this->get('session')->set('client.accounts.account_group_type_id', $groupType->getId());
    }

    /**
     * Get account group type from session.
     *
     * @return null|AccountGroupType
     */
    private function getAccountGroupType()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $groupTypeId = $this->get('session')->get('client.accounts.account_group_type_id');

        return $em->getRepository('WealthbotClientBundle:AccountGroupType')->find($groupTypeId);
    }

    /**
     * Remove account group type from session.
     */
    private function removeAccountGroupType()
    {
        $this->get('session')->remove('client.accounts.account_group_type_id');
    }

    /**
     * Save account type in the session.
     *
     * @param $type
     */
    private function setAccountType($type)
    {
        $this->get('session')->set('client.accounts.account_type', strtolower($type));
    }

    /**
     * Get account type from session.
     *
     * @return mixed
     */
    private function getAccountType()
    {
        return $this->get('session')->get('client.accounts.account_type');
    }

    /**
     * Remove account type from session.
     */
    private function removeAccountType()
    {
        $this->get('session')->remove('client.accounts.account_type');
    }

    /**
     * Save account owners in the session.
     *
     * @param $owners
     */
    private function setAccountOwners($owners)
    {
        $this->get('session')->set('client.accounts.account_owners', $owners);
    }

    /**
     * Get account owners from session.
     *
     * @return mixed
     */
    private function getAccountOwners()
    {
        return $this->get('session')->get('client.accounts.account_owners', []);
    }

    /**
     * Remove account owners from session.
     */
    private function removeAccountOwners()
    {
        $this->get('session')->remove('client.accounts.account_owners');
    }

    /**
     * Get is consolidate account from session.
     *
     * @return mixed
     */
    private function getIsConsolidateAccount()
    {
        return $this->get('session')->get('client.accounts.is_consolidate_account', true);
    }

    /**
     * Remove is consolidate account from session.
     */
    private function removeIsConsolidateAccount()
    {
        $this->get('session')->remove('client.accounts.is_consolidate_account');
    }

    private function setAccountStep($step)
    {
        $this->get('session')->set('clients.accounts.step', $step);
    }

    private function getAccountStep()
    {
        return $this->get('session')->get('clients.accounts.step');
    }

    private function removeAccountStep()
    {
        $this->get('session')->remove('clients.accounts.step');
    }
}
