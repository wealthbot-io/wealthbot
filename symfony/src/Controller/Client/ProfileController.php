<?php

namespace App\Controller\Client;

use Doctrine\ORM\EntityManager;
use http\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Exception\DataAlreadyExistsException;
use App\Entity\AccountGroupType;
use App\Entity\AccountOutsideFund;
use App\Entity\ClientAccount;
use App\Entity\TransferCustodian;
use App\Form\Handler\ClientAccountFormHandler;
use App\Form\Handler\ClientAccountOwnerFormHandler;
use App\Form\Handler\ClientQuestionsFormHandler;
use App\Form\Handler\OutsideFundFormHandler;
use App\Form\Type\AccountGroupsFormType;
use App\Form\Type\AccountTypesFormType;
use App\Form\Type\ClientAccountFormType;
use App\Form\Type\ClientAccountOwnerFormType;
use App\Form\Type\ClientProfileFormType;
use App\Form\Type\ClientQuestionsFormType;
use App\Form\Type\OutsideFundFormType;
use App\Form\Type\TypedClientAccountFormType;
use App\Model\AccountGroup;
use App\Repository\AccountOutsideFundRepository;
use App\Repository\ClientAccountRepository;
use App\Entity\Profile;
use App\Entity\User;

class ProfileController extends Controller
{
    use AclController;

    const ACCOUNT_STEP_ACCOUNT_GROUP = 1;
    const ACCOUNT_STEP_ACCOUNT_GROUP_TYPE = 2;
    const ACCOUNT_STEP_ACCOUNT_UPDATE_FORM = 3;
    const ACCOUNT_STEP_ACCOUNT_OWNER_FORM = 4;

    public function index($name)
    {
        return $this->render('/Client/Default/index.html.twig', ['name' => $name]);
    }

    public function stepOne(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var User $user */
        $user = $this->getUser();

        $profile = $user->getProfile();
        if (!$profile) {
            $profile = new Profile();
            $profile->setUser($user);
        };

        $isPreSave = ($request->isXmlHttpRequest() || $request->get('is_pre_save'));
        $form = $this->createForm(ClientProfileFormType::class, $user->getProfile(), ['is_pre_save'=> $isPreSave, 'profile' => $profile]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $profile = $form->getData();
                $spouse = $user->getSpouse();

                if (Profile::CLIENT_MARITAL_STATUS_MARRIED === $profile->getMaritalStatus()) {
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
                    return $this->json(['status' => 'success']);
                }

                if (Profile::CLIENT_SOURCE_IN_HOUSE === $profile->getClientSource()) {
                    $redirectUrl = $this->generateUrl('rx_client_portfolio');
                } else {
                    $redirectUrl = $this->generateUrl('rx_client_profile_step_two');
                }

                return $this->redirect($redirectUrl);
            } elseif ($isPreSave && $request->isXmlHttpRequest()) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render('/Client/Profile/step_one.html.twig', [
            'form' => $form->createView(),
            'ria_company_information' => $user->getProfile()->getRia()->getRiaCompanyInformation(),
        ]);
    }

    public function stepTwo(Request $request)
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
        $form = $this->createForm(ClientQuestionsFormType::class, $user, ['user' => $user, 'em'=>$em, 'is_pre_save'=>$isPreSave]);

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
                    return $this->json(['status' => 'success']);
                }

                return $this->redirect($this->generateUrl('rx_client_profile_step_three'));
            } elseif ($isPreSave) {
                return $this->json(['status' => 'error']);
            }
        }

        return $this->render('/Client/Profile/step_two.html.twig', [
            'form' => ($form ? $form->createView() : $form),
            'ria_company_information' => $user->getRiaCompanyInformation(),
        ]);
    }

    public function stepThree(Request $request)
    {
        $this->removeAccountStep();

        /** @var User $user */
        $user = $this->getUser();
        $ria = $user->getRia();
        $form = $this->createForm(AccountGroupsFormType::class, null, [
            'ria' => $ria
        ]);
        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $group = $data['groups'];
                $this->setAccountGroup($group);
                $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_GROUP);
                return $this->json(
                    [
                        'status' => 'success',
                        'form' => $this->getAccountFormByGroup($group),
                    ]
                );
            } else {
                return $this->json(['status' => 'error']);
            }
        }
        return $this->render('/Client/Profile/step_three.html.twig', [
            'form' => $form->createView(),
            'client' => $user,
            'ria_company_information' => $user->getProfile()->getRia()->getRiaCompanyInformation(),
        ]);
    }

    public function checkAccountsSum(Request $request)
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
        $accountsRepo = $em->getRepository('App\Entity\ClientAccount');
        $riaMinAssetSize = round($ria->getRiaCompanyInformation()->getMinAssetSize(), 2);
        $total = $accountsRepo->getTotalScoreByClientId($client->getId());

        if ($riaMinAssetSize > round($total['value'], 2)) {
            return $this->json(
                [
                    'status' => 'error',
                    'message' => sprintf('You must invest at least $%s with us.', number_format($riaMinAssetSize)),
                ]
            );
        }

        return $this->json(['status' => 'success']);
    }

    public function showAccountOwnerForm(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();
        $group = $this->getAccountGroup();

        if (!$client->isMarried() && 'joint account' !== $this->getAccountType()) {
            return $this->json(['status' => 'error', 'message' => 'Client does not married.']);
        }

        if (null === $group) {
            return $this->json(['status' => 'error', 'message' => 'Select type of account.']);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $form = $this->createForm(
            ClientAccountOwnerFormType::class,
            $client,
            [
                'client' => $client,
                'em' => $em,
                'isJoint' => ('joint account' === $this->getAccountType())]
        );
        $formHandler = new ClientAccountOwnerFormHandler($form, $request, $em, $client);

        $owners = $formHandler->process();

        if (empty($owners)) {
            return $this->json([
                'status' => 'error',
                'form' => $this->renderView('/Client/Profile/_account_owner_form.html.twig', [
                    'form' => $form->createView(),
                    'client' => $client,
                ]),
            ]);
        } else {
            $this->setAccountOwners($owners);
            $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_UPDATE_FORM);

            return $this->json([
                'status' => 'success',
                'content' => $this->getAccountOwnerFormByGroup($group),
            ]);
        }
    }

    public function updateAccountOwnerForm(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();
        $form = $this->createForm(
            ClientAccountOwnerFormType::class,
            $client,
            [
                'em' => $this->get('doctrine.orm.entity_manager'),
                'isJoint' => ('joint account' === $this->getAccountType()),
                'client' => $client
            ]
        );

        $form->handleRequest($request);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/Profile/_other_account_owner_form.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
            ]),
        ]);
    }

    public function showDepositAccountForm(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        /** @var User $client */
        $user = $this->getUser();
        $group = $this->getAccountGroup();

        $depositAccountGroupForm = $this->createForm(AccountTypesFormType::class, null, ['user' => $user, 'group' => $group]);
        $depositAccountGroupForm->handleRequest($request);

        if ($depositAccountGroupForm->isValid()) {
            /** @var AccountGroupType $groupType */
            $groupType = $depositAccountGroupForm->get('group_type')->getData();

            $this->setAccountType($groupType->getType()->getName());
            $this->setAccountGroupType($groupType);
            $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_GROUP_TYPE);

            $accountForm = $this->getAccountFormByGroupAndGroupType($group, $groupType);

            return $this->json([
                'status' => 'success',
                'form' => $accountForm,
            ]);
        }

        return $this->json(['status' => 'error']);
    }

    public function completeStepThree()
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $client = $this->getUser();

        $client->getProfile()->setRegistrationStep(3);
        $em->persist($client);
        $em->flush();

        $this->get('wealthbot.mailer')->sendSuggestedPortfolioEmailMessage($client);

        $url = $this->generateUrl('rx_client_portfolio');

        return $this->redirect($url);
    }

    public function showAccountForm(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();

        $accountGroupForm = $this->createForm(AccountGroupsFormType::class, $client);
        $accountGroupForm->handleRequest($request);

        if ($accountGroupForm->isValid()) {
            $group = $this->getAccountGroup();

            if (AccountGroup::GROUP_DEPOSIT_MONEY === $group) {
                $form = $this->createForm(AccountTypesFormType::class, $client);

                return $this->json([
                    'status' => 'success',
                    'form' => $this->renderView('/Client/Profile/_select_account_type_form.html.twig', [
                        'form' => $form->createView(),
                        'group' => $group,
                    ]),
                ]);
            }

            return $this->json([
                'status' => 'success',
                'form' => $this->getAccountFormView($client, $group),
            ]);
        }

        return $this->json(['status' => 'error']);
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

    public function updateAccountForm(Request $request, $group)
    {
        $client = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ClientAccountFormType::class, null, [
            'client' => $client,
            'em'=> $em,
            'validateAdditionalFields' => false,
            'group' => $group
        ]);
        $form->handleRequest($request);

        $step = $this->getAccountStep();
        if (self::ACCOUNT_STEP_ACCOUNT_UPDATE_FORM === $step) {
            $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_OWNER_FORM);
        } else {
            $this->setAccountStep(self::ACCOUNT_STEP_ACCOUNT_UPDATE_FORM);
        }

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/Profile/_client_accounts_form_fields.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function accounts(Request $request, $group)
    {
        $client = $this->getUser();
        $groups = $this->get('session')->get('client.accounts.groups');

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
        $clientAccountRepo = $em->getRepository('App\Entity\ClientAccount');

        $accounts = $clientAccountRepo->findBy(['client_id' => $client->getId()]);

        $accountTypes = $em->getRepository('App\Entity\AccountType')->findAll();
        $accountTypesArray = [];
        foreach ($accountTypes as $object) {
            $accountTypesArray[$object->getId()] = $object->getType();
        }

        $clientAccount = new ClientAccount();

        $form = $this->createForm(ClientAccountFormType::class, $clientAccount, [
            'group'=> $group,
            'em'=> $em,
            'isAllowRetirementPlan'=>false,
            'client' => $this->getUser(),
            'validateAdditionalFields' => false
        ]);
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

        return $this->render('/Client/Profile/step_three_accounts.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
            'accounts' => $accounts,
            'group' => $group,
            'prev_group' => $prevGroup,
            'account_types' => $accountTypesArray,
            'total' => $total,
        ]);
    }

    public function accountsFunds(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var $repo ClientAccountRepository */
        $repo = $em->getRepository('App\Entity\ClientAccount');

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

        return $this->render('/Client/Profile/step_three_accounts_funds.html.twig', [
            'client' => $client,
            'accounts' => $accounts,
            'retirement_accounts' => $retirementAccounts,
            'total' => $total,
        ]);
    }

    public function createAccount(Request $request, $group)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();
        if (!$client || !$client->hasRole('ROLE_CLIENT')) {
            return $this->json(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');
        $groupType = $this->getAccountGroupType();
        $form = $this->createForm(ClientAccountFormType::class, null, [
            'client' => $client,
            'em'=> $em,
            'group' => $groupType->getId(),
            'isAllowRetirementPlan'=>false,
            'validateAdditionalFields' => false
        ]);

        $formHandler = new ClientAccountFormHandler($form, $request, $adm, $this->getAccountOwners(), $this->getIsConsolidateAccount(), $this->getUser(), $groupType);

        $process = $formHandler->process();
        if ($process) {
            $this->removeAccountGroup();
            $this->removeAccountType();
            $this->removeAccountGroupType();
            $this->removeAccountOwners();

            /** @var ClientAccount $clientAccount */
            $clientAccount = $form->getData();

            if ('employer_retirement' === $group) {
                $responseData = $this->processEmployerRetirementAccountForm($clientAccount);
            } else {
                $responseData = $this->processAccountForm();

                $isType = AccountGroup::GROUP_DEPOSIT_MONEY === $clientAccount->getGroupName();
                $systemAccounts = $em->getRepository('App\Entity\SystemAccount')->findByClientIdAndType($client->getId(), $clientAccount->getSystemType());

                $responseData['in_right_box'] = ($isType || count($systemAccounts) < 1) ? false : true;
                $responseData['transfer_url'] = $this->generateUrl(
                    'rx_client_dashboard_select_system_account',
                    ['account_id' => $clientAccount->getId()]
                );
            }

            $this->removeIsConsolidateAccount();

            $this->removeAccountStep();

            return $this->json($responseData);
        }

        $message = $this->getTitleMessageForAccountForm($group, $groupType);

        return $this->json([
            'status' => 'error',
            'form' => $this->renderView('/Client/Profile/_client_accounts_form.html.twig', [
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
            'content' => $this->renderView('/Client/Profile/_create_account_success.html.twig'),
            'show_accounts_table' => 1,
            'show_portfolio_button' => 1,
        ];
    }

    private function processEmployerRetirementAccountForm(ClientAccount $account)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $form = $this->createForm(OutsideFundFormType::class, null, ['em'=>$em,'account'=> $account]);

        return [
            'status' => 'success',
            'content' => $this->renderView('/Client/Profile/_retirement_account_funds.html.twig', [
                'account' => $account,
                'form' => $form->createView(),
                'show_title_message' => true,
            ]),
        ];
    }

    public function showSuccessMessage()
    {
        return $this->json($this->processAccountForm());
    }

    public function showAccountsTable()
    {
        $client = $this->getUser();
        if (!$client || !$client->hasRole('ROLE_CLIENT')) {
            return $this->json(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $clientAccountRepo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $clientAccountRepo = $em->getRepository('App\Entity\ClientAccount');
        $total = $clientAccountRepo->getTotalScoreByClientId($client->getId());

        return $this->render('/Client/Profile/_accounts_list.html.twig', [
            'client' => $client,
            'total' => $total,
            'show_action_btn' => true,
        ]);
    }

    public function editAccount(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $client = $this->getUser();
        if (!$client || !$client->hasRole('ROLE_CLIENT')) {
            return $this->json(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $clientAccountRepo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');

        $clientAccountRepo = $em->getRepository('App\Entity\ClientAccount');

        $account = $clientAccountRepo->find($request->get('id'));
        if (!$account) {
            return $this->json(['status' => 'error', 'message' => 'Account does not exist.']);
        }

        $form = $this->createForm(ClientAccountFormType::class, $account, [
            'group'=> $account->getGroupName(),
            'em'=> $em,
            'isAllowRetirementPlan'=>false,
            'client' => $this->getUser(),
            'validateAdditionalFields' => false
        ]);
        $formHandler = new ClientAccountFormHandler($form, $request, $adm, [], true, $this->getUser(), null);
        $process = $formHandler->process();

        if ($request->isMethod('post')) {
            if ($process) {
                $retirementAccounts = $clientAccountRepo->findByClientIdAndGroup($client->getId(), AccountGroup::GROUP_EMPLOYER_RETIREMENT);
                $total = $clientAccountRepo->getTotalScoreByClientId($client->getId());

                return $this->json([
                        'status' => 'success',
                        'accounts' => $this->renderView('/Client/Profile/_accounts_list.html.twig', [
                            'client' => $client,
                            'total' => $total,
                            'show_action_btn' => true,
                        ]),
                        'retirement_accounts' => $this->renderView('/Client/Profile/_retirement_accounts_list.html.twig', [
                            'retirement_accounts' => $retirementAccounts,
                        ]),
                    ]);
            } else {
                $status = 'error';
            }
        } else {
            $status = 'success';
        }

        return $this->json([
                'status' => $status,
                'form' => $this->renderView('/Client/Profile/_edit_client_account_form.html.twig', [
                    'form' => $form->createView(),
                    'account' => $account,
                ]),
            ]);
    }

    public function deleteAccount(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        /** @var $account ClientAccount */
        $account = $em->getRepository('App\Entity\ClientAccount')->find($request->get('id'));

        if ($account) {
            $outsideFunds = $account->getAccountOutsideFunds();
            foreach ($outsideFunds as $fund) {
                $em->remove($fund);
            }
            $em->remove($account);
        } else {
            $message = 'Client account with id: '.$request->get('id').' does not exist.';

            if ($request->isXmlHttpRequest()) {
                return $this->json(['status' => 'error', 'message' => $message]);
            }

            $this->container->get('session')->getFlashBag()->add('error', $message);

            return $this->redirect($this->generateUrl('rx_client_profile_step_three'));
        }

        $em->flush();
        $message = 'Account has been deleted successfully.';

        $total = $repo->getTotalScoreByClientId($this->getUser()->getId());

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'message' => $message,
                'total' => $total,
            ]);
        }

        $this->container->get('session')->getFlashBag()->add('success', $message);

        return $this->redirect($this->generateUrl('rx_client_profile_step_three'));
    }

    public function outsideFund(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $user = $this->getUser();

        if (!$user || !$user->hasRole('ROLE_CLIENT')) {
            return $this->json(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $account = $em->getRepository('App\Entity\ClientAccount')->findOneBy([
            'id' => $request->get('account_id'),
        ]);

        if (!$account) {
            return $this->json([
                'status' => 'error',
                'message' => 'Retirement Account with id: '.$request->get('id').' does not exist.',
            ]);
        }

        $form = $this->createForm(OutsideFundFormType::class, null, ['em'=>$em, 'account'=>$account]);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Client/Profile/_retirement_account_funds.html.twig', [
                'account' => $account,
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function createOutsideFund(Request $request)
    {
        if (!$request->isMethod('post') || !$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        $client = $this->getUser();
        if (!$client || !$client->hasRole('ROLE_CLIENT')) {
            return $this->json(['status' => 'error', 'message' => 'Client does not exist.']);
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $formData = $request->get('outside_fund');
        $accountId = isset($formData['account_id']) ? $formData['account_id'] : null;
        if (!$accountId) {
            return $this->json(['status' => 'error', 'message' => 'Not defined parameter account_id.']);
        }

        /** @var $account \Entity\ClientAccount $retirementAccount */
        $account = $em->getRepository('App\Entity\ClientAccount')->findOneBy([
            'id' => $accountId,
        ]);
        if (!$account) {
            return $this->json(['status' => 'error', 'message' => 'Retirement Account with id: '.$accountId.' does not exist.']);
        }

        $form = $this->createForm(OutsideFundFormType::class, null, ['em'=>$em, 'account'=>$account]);
        $formHandler = new OutsideFundFormHandler($form, $request, $em);

        try {
            $process = $formHandler->process($account);

            if ($process) {
                return $this->json([
                    'status' => 'success',
                    'content' => $this->renderView('/Client/Profile/retirement_funds_list.html.twig', ['account' => $account]),
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Not valid.',
                    'content' => $this->renderView('/Client/Profile/retirement_account_fund_form.html.twig', ['form' => $form->createView()]),
                ]);
            }
        } catch (DataAlreadyExistsException $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'Account already has this fund.',
            ]);
        }
    }

    public function deleteOutsideAccountFund(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo AccountOutsideFundRepository */
        /* @var $accountRepo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\AccountOutsideFund');
        $accountRepo = $em->getRepository('App\Entity\ClientAccount');

        $client = $this->getUser();

        $accountId = $request->get('account_id');
        $fundId = $request->get('fund_id');

        $account = $accountRepo->findOneBy(['id' => $accountId, 'client_id' => $client->getId()]);
        if (!$account) {
            return $this->json(
                ['status' => 'error', 'message' => sprintf('You have not account with id: %s.', $accountId)]
            );
        }

        /** @var AccountOutsideFund $accountOutsideFund */
        $accountOutsideFund = $repo->findOneBySecurityIdAndAccountId($fundId, $accountId);
        if (!$accountOutsideFund) {
            return $this->json(
                [
                    'status' => 'error',
                    'message' => sprintf('Account outside fund for account: %s does not exist.', $accountId),
                ]
            );
        }

        $em->remove($accountOutsideFund);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    public function completeTransferCustodian(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $query = $request->get('query');

        $transferCustodians = $em->getRepository('App\Entity\TransferCustodian')->createQueryBuilder('tc')
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

        return $this->json($result);
    }

    public function updateTransferInformationForm(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $client = $this->getUser();

        $form = $this->createForm(TypedClientAccountFormType::class, null, ['em' =>$em,'client'=> $client,'group'=> $this->getAccountGroupType(),'validateAdditionalFields' => null]);
        $form->handleRequest($request);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView(
                '/Client/Profile/_transfer_information_questionnaire_form_fields.html.twig',
                ['form' => $form->createView(), 'hide_errors' => true]
            ),
        ]);
    }

    public function stepThreeBack()
    {
        $step = $this->getAccountStep();

        if (!$step) {
            return $this->json([
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
                    $content = $this->renderView('/Client/Profile/_create_account_success.html.twig');
                } else {
                    $content = '';
                }
                break;
            case self::ACCOUNT_STEP_ACCOUNT_GROUP_TYPE:
                $content = $this->getAccountFormByGroup($group);
                break;
            case self::ACCOUNT_STEP_ACCOUNT_UPDATE_FORM:
                $groupType = $this->getAccountGroupType();
                $content = $this->getAccountFormByGroupAndGroupType($group, $groupType);
                break;
            case self::ACCOUNT_STEP_ACCOUNT_OWNER_FORM:
                $content = $this->getAccountOwnerFormByGroup($group);
                break;
            default:
                $content = '';
                break;
        }

        $this->setAccountStep($step - 1);

        return $this->json([
            'status' => 'success',
            'step' => $step,
            'content' => $content,
        ]);
    }

    private function getAccountFormByGroup($group)
    {
        $user = $this->getUser();

        if (AccountGroup::GROUP_FINANCIAL_INSTITUTION === $group || AccountGroup::GROUP_DEPOSIT_MONEY === $group) {
            $content = $this->renderView('/Client/Profile/_select_account_type_form.html.twig', [
                'form' => $this->createForm(AccountTypesFormType::class, null, ['user'=> $user, 'group'=>$group])->createView(),
                'group' => $group
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

        if (!$user->isMarried() && 'joint account' !== $this->getAccountType()) {
            $form = $this->createForm(TypedClientAccountFormType::class, null, [
                'em' => $this->get('doctrine.orm.entity_manager'),
                'user' => $user,
                'group' => $groupType,
                'client' => $user,
                'validateAdditionalFields' => false
            ]);
            $message = $this->getTitleMessageForAccountForm($group, $groupType);

            $content = $this->renderView('/Client/Profile/_client_accounts_form.html.twig', [
                'form' => $form->createView(),
                'group' => $groupType,
                'hide_submit_button' => true,
                'title_message' => $message,
            ]);
        } else {
            $content = $this->getAccountOwnerFormView(
                $user,
                $this->get('doctrine.orm.entity_manager'),
                ('joint account' === $this->getAccountType())
            );
        }

        return $content;
    }

    private function getAccountOwnerFormByGroup($group)
    {
        $user = $this->getUser();
        $clientAccount = new ClientAccount();

        $accountForm = $this->createForm(
            TypedClientAccountFormType::class,
            $clientAccount,
            [
                'em' => $this->get('doctrine.orm.entity_manager'),
                'client' => $user,
                'group' => $group
            ]
        );

        $content = $this->renderView('/Client/Profile/_client_accounts_form.html.twig', [
            'form' => $accountForm->createView(),
            'group' => $group,
            'hide_submit_button' => true,
            'title_message' => $this->getTitleMessageForAccountForm($group),
        ]);

        return $content;
    }

    private function getAccountOwnerFormView(User $client, EntityManager $em, $isJoint = false)
    {
        $form = $this->createForm(
            ClientAccountOwnerFormType::class,
            $client,
            [
                'em' => $this->get('doctrine.orm.entity_manager'),
                'isJoint' => $isJoint,
                'client' => $client
            ]
        );
        return $this->renderView('/Client/Profile/_account_owner_form.html.twig', [
            'form' => $form->createView(),
            'client' => $client,
        ]);
    }

    private function getAccountFormView(User $client, $group)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(ClientAccountFormType::class, $client, [
            'group'=> $group,
            'em'=> $em,
            'isAllowRetirementPlan'=>false,
            'client' => $this->getUser(),
            'validateAdditionalFields' => false
        ]);
        $message = $this->getTitleMessageForAccountForm($group);

        return $this->renderView('/Client/Profile/_client_accounts_form.html.twig', [
            'form' => $form->createView(),
            'group' => $group,
            'hide_submit_button' => true,
            'title_message' => $message,
        ]);
    }

    /**
     * Save account group inf the session.
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
     * @return AccountGroupType|null
     */
    private function getAccountGroupType()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $groupTypeId = $this->get('session')->get('client.accounts.account_group_type_id');

        return $em->getRepository('App\Entity\AccountGroupType')->find($groupTypeId);
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
