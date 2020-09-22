<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.11.12
 * Time: 12:30
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Ria;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Repository\SecurityAssignmentRepository;
use App\Entity\ClientAccount;
use App\Entity\Workflow;
use App\Manager\ClientPortfolioManager;
use App\Repository\AccountOutsideFundRepository;
use App\Repository\ClientAccountRepository;
use App\Form\Handler\InviteProspectFormHandler;
use App\Form\Handler\OutsideFundAssociationFormHandler;
use App\Form\Handler\RiaClientAccountFormHandler;
use App\Form\Handler\SuggestedPortfolioFormHandler;
use App\Form\Type\InviteProspectFormType;
use App\Form\Type\OutsideFundAssociationFormType;
use App\Form\Type\RiaClientAccountFormType;
use App\Form\Type\SuggestedPortfolioFormType;
use App\Entity\Profile;
use App\Entity\User;
use App\Repository\UserRepository;

class ProspectsController extends Controller
{
    public function index(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $ria = $this->getUser();

        $clientsData = $em->getRepository('App\Entity\User')->findOrderedProspectsByRia(
            $ria,
            $request->get('sort'),
            $request->get('order')
        );

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Ria/Prospects/index.html.twig', [
                    'clients_data' => $clientsData,
                ]),
            ]);
        }

        return $this->render('/Ria/Prospects/index.html.twig', [
            'clients_data' => $clientsData,
        ]);
    }

    public function invite(Request $request)
    {
        $ria = $this->getUser();
        if (!$request->isXmlHttpRequest()) {
            return $this->createAccessDeniedException();
        }

        $user = new User();
        $user->setProfile(new Profile());
        $form = $this->createForm(InviteProspectFormType::class, $ria, ['ria' => $ria, 'user'=> $user]);

        if ($request->isMethod('POST')) {
            $em = $this->get('doctrine.orm.entity_manager');
            $inviteFormHandler = new InviteProspectFormHandler(
                $form,
                $request,
                $em,
                [
                    'email_service' => $this->get('wealthbot.mailer'),
                    'ria' => $this->getUser(),]
            );

            $form->submit($request->get('invite_prospect'));
            $process = $inviteFormHandler->process();
            if ($process) {
                $data = [
                    'status' => 'success',
                    'status_message' => 'User was inviting successfully',
                    'content' => $this->renderView('/Ria/Prospects/_invite_prospect_form_fields.html.twig', [
                        'form' => $this->createForm(InviteProspectFormType::class, $ria)->createView(),
                    ]),
                ];

                if ('internal' === $form->get('type')->getData()) {
                    $prospectsList = $em->getRepository('App\Entity\User')->findOrderedProspectsByRia(
                        $ria,
                        $request->get('sort'),
                        $request->get('order')
                    );

                    $data['prospectsList'] = $this->renderView('/Ria/Prospects/index.html.twig', [
                        'clients_data' => $prospectsList,
                    ]);
                }

                return $this->json($data);
            }
        }

        return $this->json([
            'status' => 'error',
            'status_message' => 'User was not inviting, check the input data',
            'content' => $this->renderView('/Ria/Prospects/_invite_prospect_form_fields.html.twig', [
                'form' => $form->createView(),
            ]),
        ]);
    }

    public function delete(Request $request)
    {
        if ($request->isMethod('post')) {
            /** @var \Doctrine\ORM\EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');

            $ria = $this->getUser();
            $clientIds = $request->get('clients_ids');

            if (!empty($clientIds)) {
                $qb = $em->getRepository('App\Entity\User')->createQueryBuilder('c');

                $clients = $qb->leftJoin('c.profile', 'p')
                    ->where('p.ria_user_id = :ria_id')
                    ->andWhere($qb->expr()->in('c.id', $clientIds))
                    ->setParameter('ria_id', $ria->getId())
                    ->getQuery()
                    ->getResult();

                foreach ($clients as $client) {
                    $em->remove($client);
                }

                $em->flush();
            }

            return $this->json([
                'status' => 'success',
            ]);
        }

        return $this->json([
            'status' => 'error',
        ]);
    }

    public function suggestedPortfolio(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var ClientPortfolioManager $clientPortfolioManager */
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        /** @var $ria User */
        /* @var $client User */
        $ria = $this->getUser();
        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));

        $clientPortfolio = $clientPortfolioManager->getProposedClientPortfolio($client);

        if (!$clientPortfolio) {
            throw $this->createNotFoundException('This client does not have suggested portfolio.');
        }

        $mailer = $this->get('wealthbot.mailer');
        $accountsRepo = $em->getRepository('App\Entity\ClientAccount');
        $questionnaireAnswerRepo = $em->getRepository('App\Entity\ClientQuestionnaireAnswer');
        $portfolio = $clientPortfolio->getPortfolio();

        $settingsForm = $this->createForm(SuggestedPortfolioFormType::class, null, ['em' =>$em,'clientPortfolio'=> $clientPortfolio,'client'=>$client]);
        $settingsFormHandler = new SuggestedPortfolioFormHandler($settingsForm, $request, $em, [
            'mailer' => $mailer,
            'client_portfolio_manager' => $clientPortfolioManager,
        ]);

        if ($request->isMethod('post')) {
            $process = $settingsFormHandler->success();
            if ($process) {
                $tradier = $this->get("App\Api\Rebalancer");
                $tradier->initialRebalance($clientPortfolio);
                $workflowManager = $this->get('wealthbot.workflow.manager');
                $workflow = $workflowManager->findOneByClientAndTypeAndObjectType($client, Workflow::TYPE_PAPERWORK, $portfolio);

                if ($workflow) {
                    $workflowManager->archive($workflow);
                }

                $portfolio = $settingsForm->get('client')->get('portfolio')->getData();
            };
        }

        $companyInformation = $ria->getRiaCompanyInformation();
        $isUseQualified = $companyInformation->getIsUseQualifiedModels();
        $isQualified = false;

        if ($isUseQualified) {
            if ($settingsForm->has('is_qualified')) {
                $this->setIsQualifiedModel($settingsForm->get('is_qualified')->getData());
            }

            $isQualified = $this->getIsQualifiedModel();
        };

        $clientAccounts = $accountsRepo->findConsolidatedAccountsByClientId($client->getId());
        $portfolioInformationManager = $this->get('wealthbot_client.portfolio_information_manager');


        $form = $this->createForm(RiaClientAccountFormType::class, $clientAccounts[0], [
            'client'=>$client, 'em' => $em]);
        $formHandler = new  RiaClientAccountFormHandler($form, $request, $this->get('wealthbot_docusign.account_docusign.manager'), $this->getUser());
        $formHandler->process();

        $em->refresh($clientPortfolio);
        $data = [
            'is_client_view' => (bool) $request->get('client_view'),
            'user' => $ria,
            'form' => $form->createView(),
            'total' => $accountsRepo->getTotalScoreByClientId($client->getId()),
            'client' => $client,
            'client_accounts' => $clientAccounts,
            'settings_form' => $settingsForm->createView(),
            'client_answers' => $questionnaireAnswerRepo->findBy(['client_id' => $client->getId()]),
            'has_retirement_account' => $accountsRepo->hasRetirementAccount($client),
            'ria_company_information' => $ria->getRiaCompanyInformation(),
            'client_has_final_portfolio' => $clientPortfolio->isAdvisorApproved(),
            'portfolio_information' => $portfolioInformationManager->getPortfolioInformation($client, $portfolio, $isQualified),
            'is_use_qualified_models' => $isUseQualified,
            'action' => 'ria_suggested_portfolio',
            'billing_spec' => $client->getAppointedBillingSpec(),
        ];

        return $this->render('/Ria/Prospects/suggested_portfolio.html.twig', $data);
    }

    public function updateAssetLocationField(Request $request)
    {
        $ria = $this->getUser();
        $em = $this->get('doctrine.orm.entity_manager');
        $clientPortfolioManager = $this->get('wealthbot_client.client_portfolio.manager');

        /** @var $client User */
        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getRia() !== $ria) {
            throw $this->createNotFoundException();
        }

        $proposedPortfolio = $clientPortfolioManager->getProposedClientPortfolio($client);
        $settingsForm = $this->createForm(SuggestedPortfolioFormType::class, $client->getProfile(), [
            'em'=>$em,
            'clientPortfolio'=>$proposedPortfolio,
            'client' => $client
        ]);

        if ($request->isXmlHttpRequest()) {
            return $this->renderView('/Ria/Prospects/_asset_location_field.html.twig', ['settings_form' => $settingsForm->createView(), 'client_has_final_portfolio' => false]);
        }

        return $this->render('/Ria/Prospects/_asset_location_field.html.twig', ['settings_form' => $settingsForm->createView(), 'client_has_final_portfolio' => false]);
    }

    public function createClientAccount(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');

        $repo = $em->getRepository('App\Entity\ClientAccount');

        $ria = $this->getUser();

        /** @var User $client */
        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $ria->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Client with id: '.$request->get('client_id').' does not exist.',
            ]);
        }

        $form = $this->createForm(RiaClientAccountFormType::class, null, [
            'em'=>$em,
            'client' => $client
        ]);
        $formHandler = new RiaClientAccountFormHandler($form, $request, $adm, $ria);

        if ($request->isMethod('post')) {
            $process = $formHandler->process();

            if ($process) {
                /** @var $clientAccount ClientAccount */
                $clientAccount = $form->getData();
                $client = $clientAccount->getClient();
                $newForm = $this->createForm(RiaClientAccountFormType::class, $clientAccount, [
                    'client'=>$client, 'em' => $em]);


                $retirementAccounts = $repo->getRetirementAccountsByClientId($client->getId());
                $total = $repo->getTotalScoreByClientId($client->getId());
                $withEdit = $client->hasApprovedPortfolio() ? false : true;
                $consolidated = $repo->findConsolidatedAccountsByClientId($client->getId());

                if (!$clientAccount->getConsolidator()) {
                    $account = $this->renderView('/Ria/Prospects/_client_account_row.html.twig', [
                        'client' => $client,
                        'account' => $clientAccount,
                        'index' => count($consolidated),
                        'with_edit' => $withEdit,
                    ]);
                } else {
                    $account = $this->renderView('/Ria/Prospects/_client_account_row.html.twig', [
                        'client' => $client,
                        'account' => $clientAccount->getConsolidator(),
                        'index' => count($consolidated),
                        'with_edit' => $withEdit,
                    ]);
                }

                return $this->json([
                    'status' => 'success',
                    'account' => $account,
                    'consolidator_id' => $clientAccount->getConsolidator() ? $clientAccount->getConsolidator()->getId() : null,
                    'form' => $this->renderView('/Ria/Prospects/_create_client_account_form.html.twig', [
                        'form' => $newForm->createView(),
                        'client' => $client,
                    ]),
                    'outside_accounts' => $this->renderView('/Ria/Prospects/_client_outside_accounts_list.html.twig', [
                        'retirement_accounts' => $retirementAccounts,
                    ]),
                    'asset_location' => $this->updateAssetLocationField($request),
                    'total' => $total,
                ]);
            }
        }

        return $this->json([
            'status' => 'error',
            'form' => $this->renderView('/Ria/Prospects/_create_client_account_form.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
            ]),
        ]);
    }

    public function editClientAccount(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $adm = $this->get('wealthbot_docusign.account_docusign.manager');

        $repo = $em->getRepository('App\Entity\ClientAccount');

        $ria = $this->getUser();

        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $ria->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Client with id: '.$request->get('client_id').' does not exist.',
            ]);
        }

        /** @var $clientAccount ClientAccount */
        $clientAccount = $em->getRepository('App\Entity\ClientAccount')->findOneBy([
            'id' => $request->get('account_id'),
            'client_id' => $client->getId(),
        ]);
        if (!$clientAccount) {
            return $this->json([
                'status' => 'error',
                'message' => 'Client account with id: '.$request->get('account_id').' does not exist.',
            ]);
        }

        $form = $this->createForm(RiaClientAccountFormType::class, $clientAccount, [
            'client'=>$client, 'em' => $em]);
        $formHandler = new RiaClientAccountFormHandler($form, $request, $adm, $clientAccount->getClient());

        if ($request->isMethod('post')) {
            $process = $formHandler->process();

            if ($process) {
                $clientAccount = $form->getData();

                $newForm =  $this->createForm(RiaClientAccountFormType::class, null, [
                    'client'=>$client, 'em' => $em]);
                $retirementAccounts = $repo->getRetirementAccountsByClientId($client->getId());

                $total = $repo->getTotalScoreByClientId($client->getId());

                return $this->json([
                    'status' => 'success',
                    'account_id' => $clientAccount->getId(),
                    'form' => $this->renderView('/Ria/Prospects/_create_client_account_form.html.twig', [
                        'form' => $newForm->createView(),
                        'client' => $client,
                    ]),
                    'content' => $this->renderView('/Ria/Prospects/_client_account_row.html.twig', [
                        'client' => $client,
                        'account' => $clientAccount,
                        'index' => $client->getClientAccounts()->count(),
                        'with_edit' => $clientAccount->getConsolidatedAccounts()->count() ? false : true,
                    ]),
                    'outside_accounts' => $this->renderView('/Ria/Prospects/_client_outside_accounts_list.html.twig', [
                        'retirement_accounts' => $retirementAccounts,
                    ]),
                    'total' => $total,
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'form' => $this->renderView('/Ria/Prospects/_edit_client_account_form.html.twig', [
                        'form' => $form->createView(),
                        'client' => $client,
                    ]),
                    'group' => $clientAccount->getGroupName(),
                ]);
            }
        }

        return $this->json([
            'status' => 'success',
            'form' => $this->renderView('/Ria/Prospects/_edit_client_account_form.html.twig', [
                'form' => $form->createView(),
                'client' => $client,
            ]),
            'group' => $clientAccount->getGroupName(),
        ]);
    }

    public function updateClientAccountForm(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $this->getUser()->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Client does not exist.',
            ]);
        }

        $formType = $this->createForm(RiaClientAccountFormType::class, null, ['client'=> $client, 'em'=>$em]);
        $data = $request->get($formType->getName());

        $data['groupType'] = '';

        $form = $this->createForm($formType);
        $form->submit($data);

        $result = [
            'status' => 'success',
            'content' => $this->renderView('/Ria/Prospects/_account_form_fields.html.twig', [
                'form' => $form->createView(),
            ]),
            'group' => $form->get('group')->getData(),
        ];

        return $this->json($result);
    }

    public function updateClientAccountOwnersForm(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $this->getUser()->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Client does not exist.',
            ]);
        }

        $formType = $this->createForm(RiaClientAccountFormType::class, null, [
            'client'=>$client, 'em' => $em]);


        $data = $request->get($formType->getName());

        $form = $this->createForm($formType);
        $form->submit($data);

        $result = [
            'status' => 'success',
            'content' => $this->renderView('/Ria/Prospects/_account_owners_fields.html.twig', [
                'form' => $form->createView(),
            ]),
        ];

        return $this->json($result);
    }

    public function deleteClientAccount(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');

        $ria = $this->getUser();

        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $ria->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => 'Client with id: '.$request->get('client_id').' does not exist.',
            ]);
        }

        /** @var $clientAccount ClientAccount */
        $clientAccount = $repo->findOneBy([
            'id' => $request->get('account_id'),
            'client_id' => $client->getId(),
        ]);
        if (!$clientAccount) {
            return $this->json([
                'status' => 'error',
                'message' => 'Client account with id: '.$request->get('account_id').' does not exist.',
            ]);
        }

        foreach ($clientAccount->getAccountOutsideFunds() as $fund) {
            $em->remove($fund);
        }

        $em->remove($clientAccount);
        $em->flush();

        $total = $repo->getTotalScoreByClientId($client->getId());

        return $this->json([
            'status' => 'success',
            'asset_location' => $this->updateAssetLocationField($request),
            'total' => $total,
        ]);
    }

    public function clientOutsideFunds(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        /* @var $securityAssignmentRepo SecurityAssignmentRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $securityAssignmentRepo = $em->getRepository('App\Entity\SecurityAssignment');

        $ria = $this->getUser();

        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $ria->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => sprintf('You does not have client with id: %s.', $request->get('client_id')),
            ]);
        }

        $account = $repo->findOneRetirementAccountByIdAndClientId($request->get('account_id'), $client->getId());
        if (!$account) {
            return $this->json([
                'status' => 'error',
                'message' => 'Account does not exist or does not have a retirement type.',
            ]);
        }

        $securities = $securityAssignmentRepo->findByAccountIdAndRiaId($account->getId(), $ria->getId());
        $accountSecurities = $em->getRepository('App\Entity\AccountOutsideFund')->findBy(['account_id' => $account->getId()]);
        $form = $this->createForm(OutsideFundAssociationFormType::class, null, ['em'=>$em, 'ria'=>$ria, 'account'=>$account]);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Prospects/_client_outside_funds_list.html.twig', [
                'securityAssignments' => $securities,
                'accountSecurities' => $accountSecurities,
                'form' => $form->createView(),
                'account' => $account,
                'client' => $client,
            ]),
        ]);
    }

    public function createClientOutsideFund(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        /* @var $securityAssignmentRepo SecurityAssignmentRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $securityAssignmentRepo = $em->getRepository('App\Entity\SecurityAssignment');

        $ria = $this->getUser();

        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $ria->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => sprintf('You does not have client with id: %s.', $request->get('client_id')),
            ]);
        }

        $account = $repo->findOneRetirementAccountByIdAndClientId($request->get('account_id'), $client->getId());
        if (!$account) {
            return $this->json([
                'status' => 'error',
                'message' => 'Account does not exist or does not have a retirement type.',
            ]);
        }

        $form = $this->createForm(OutsideFundAssociationFormType::class, ['em' => $em, 'ria' => $ria, 'account'=> $account]);
        $formHandler = new OutsideFundAssociationFormHandler($form, $request, $em);
        $process = $formHandler->process($account, $ria);

        if ($process) {
            $form = $this->createForm(OutsideFundAssociationFormType::class, ['em' => $em, 'ria' => $ria, 'account'=> $account]);
            $status = 'success';
        } else {
            $status = 'error';
        }

        $securities = $securityAssignmentRepo->findByAccountIdAndRiaId($account->getId(), $ria->getId());
        $accountSecurities = $em->getRepository('App\Entity\AccountOutsideFund')->findBy(['account_id' => $account->getId()]);

        return $this->json([
            'status' => $status,
            'content' => $this->renderView('/Ria/Prospects/_client_outside_funds_list.html.twig', [
                'securityAssignments' => $securities,
                'accountSecurities' => $accountSecurities,
                'form' => $form->createView(),
                'account' => $account,
                'client' => $client,
            ]),
        ]);
    }

    public function editClientOutsideFund(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        /* @var $securityAssignmentRepo SecurityAssignmentRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $securityAssignmentRepo = $em->getRepository('App\Entity\SecurityAssignment');

        $ria = $this->getUser();

        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $ria->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => sprintf('You does not have client with id: %s.', $request->get('client_id')),
            ]);
        }

        $account = $repo->findOneRetirementAccountByIdAndClientId($request->get('account_id'), $client->getId());
        if (!$account) {
            return $this->json([
                'status' => 'error',
                'message' => 'Account does not exist or does not have a retirement type.',
            ]);
        }

        $securityAssignment = $securityAssignmentRepo->find($request->get('security_id'));
        if (!$securityAssignment) {
            return $this->json([
                'status' => 'error',
                'message' => sprintf('SecurityAssignment with id %d does not exist.', $request->get('security_id')),
            ]);
        }

        $form = $this->createForm(OutsideFundAssociationFormType::class, $securityAssignment, ['em'=>$em, 'ria'=>$ria, 'account'=>$account]);
        $formHandler = new OutsideFundAssociationFormHandler($form, $request, $em);
        $process = $formHandler->process($account, $ria);

        if ($request->isMethod('post')) {
            if ($process) {
                $form = $this->createForm(OutsideFundAssociationFormType::class, ['em' => $em, 'ria' => $ria, 'account'=> $account]);

                $status = 'success';
            } else {
                $status = 'error';
            }

            $securityAssignments = $securityAssignmentRepo->findByAccountIdAndRiaId($account->getId(), $ria->getId());
            $accountSecurities = $em->getRepository('App\Entity\AccountOutsideFund')->findBy(['account_id' => $account->getId()]);

            $result = [
                'status' => $status,
                'content' => $this->renderView('/Ria/Prospects/_client_outside_funds_list.html.twig', [
                    'accountSecurities' => $accountSecurities,
                    'form' => $form->createView(),
                    'account' => $account,
                    'client' => $client,
                ]),
            ];
        } else {
            $result = [
                'status' => 'success',
                'content' => $this->renderView('/Ria/Prospects/_edit_client_outside_fund_form.html.twig', [
                    'form' => $form->createView(),
                    'account' => $account,
                    'client' => $client,
                ]),
            ];
        }

        return $this->json($result);
    }

    public function deleteClientOutsideFund(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $repo ClientAccountRepository */
        /* @var $accountOutsideFundRepo AccountOutsideFundRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $accountOutsideFundRepo = $em->getRepository('App\Entity\AccountOutsideFund');

        $ria = $this->getUser();

        $client = $em->getRepository('App\Entity\User')->find($request->get('client_id'));
        if (!$client || $client->getProfile()->getRia()->getId() !== $ria->getId()) {
            return $this->json([
                'status' => 'error',
                'message' => sprintf('You does not have client with id: %s.', $request->get('client_id')),
            ]);
        }

        $account = $repo->findOneRetirementAccountByIdAndClientId($request->get('account_id'), $client->getId());
        if (!$account) {
            return $this->json([
                'status' => 'error',
                'message' => 'Account does not exist or does not have a retirement type.',
            ]);
        }

        $security = $em->getRepository('App\Entity\Security')->find($request->get('fund_id'));
        if (!$security) {
            return $this->json([
                'status' => 'error',
                'message' => sprintf('Security with id: %s does not exist.', $request->get('fund_id')),
            ]);
        }

        $accountOutsideFund = $accountOutsideFundRepo->findOneBySecurityIdAndAccountId($security->getId(), $account->getId());
        if (!$accountOutsideFund) {
            return $this->json([
                'status' => 'error',
                'message' => sprintf('Account outside fund for account: %s does not exist.', $account->getId()),
            ]);
        }

        $em->remove($accountOutsideFund);
        $em->flush();

        return $this->json(['status' => 'success']);
    }

    // has asset classes have preferred subclass
    protected function validateClientAssetClasses(EntityManager $em, User $client)
    {
        $ria = $client->getProfile()->getRia();

        $q = '
        SELECT aof.account_id, aof.is_preferred, s.*
            FROM client_accounts ca
                LEFT JOIN account_outside_funds aof ON (ca.id = aof.account_id)
                LEFT JOIN outside_funds fund ON (fund.id = aof.outside_fund_id)
                LEFT JOIN outside_fund_associations ofa ON (ofa.outside_fund_id = fund.id)
                LEFT JOIN ria_subclasses rs ON (rs.id = ofa.ria_subclass_id)
                LEFT JOIN subclasses s ON (rs.subclass_id = s.id)
            WHERE ca.client_id = :client_id AND ofa.ria_user_id = :ria_id AND aof.is_preferred = :is_preferred
            GROUP BY s.asset_class_id
        ';

        $stmt = $em->getConnection()->prepare($q);
        $stmt->bindValue('ria_id', $ria->getId());
        $stmt->bindValue('client_id', $client->getId());
        $stmt->bindValue('is_preferred', 0);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $unPreferred = [];
        foreach ($results as $result) {
            $unPreferred[$result['account_id']][] = $result['asset_class_id'];
        }

        $stmt = $em->getConnection()->prepare($q);
        $stmt->bindValue('ria_id', $ria->getId());
        $stmt->bindValue('client_id', $client->getId());
        $stmt->bindValue('is_preferred', 1);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $preferred = [];
        foreach ($results as $result) {
            $preferred[$result['account_id']][] = $result['asset_class_id'];
        }

        $badAsset = [];

        foreach ($unPreferred as $account => $assetClasses) {
            if (isset($preferred[$account])) {
                // Search asset classes that's doesn't have preferred subclass
                $diff = array_diff($assetClasses, $preferred[$account]);
                // If exists than add to array with not valid asset classes
                if ($diff) {
                    $badAsset[$account] = $diff;
                }
            } else {
                // All asset classes doesn't have preferred subclass
                $badAsset[$account] = $assetClasses;
            }
        }

        $data['error'] = false;
        if (count($badAsset) > 0) {
            $data['error'] = true;
            $data['bad_asset_classes'] = $badAsset;
        }
        //die;
        return $data;
    }

    public function consolidatedAccounts(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException('Page not found.');
        }

        /** @var EntityManager $em */
        /* @var ClientAccountRepository $repo */
        /* @var UserRepository $userRepo */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\ClientAccount');
        $userRepo = $em->getRepository('App\Entity\User');

        $ria = $this->getUser();

        /** @var User $client */
        $client = $userRepo->find($request->get('client_id'));
        if (!$client || $client->getRia()->getId() !== $ria->getId()) {
            $this->json([
                'status' => 'error',
                'message' => 'The client does not exist or belong to another ria.',
            ]);
        }

        /** @var ClientAccount $account */
        $account = $repo->findOneBy(['id' => $request->get('account_id'), 'client_id' => $client->getId()]);
        $consolidatedAccounts = $account->getConsolidatedAccounts();

        if (!$account || !$consolidatedAccounts->count()) {
            $this->json([
               'status' => 'error',
                'message' => 'Account does not exist or does not have consolidated accounts.',
            ]);
        }

        $allConsolidatedAccounts = $this->arrayCollectionPrepend($consolidatedAccounts, $account);

        return $this->json([
            'status' => 'success',
            'content' => $this->renderView('/Ria/Prospects/_consolidated_accounts_list.html.twig', [
                'client' => $client,
                'consolidated_accounts' => $allConsolidatedAccounts,
                'total' => $repo->getTotalScoreByClientId($client->getId(), $account->getid()),
            ]),
        ]);
    }

    /**
     * Add element to the beginning of the array collection.
     *
     * @param \App\Collection $collection
     * @param $element
     *
     * @return ArrayCollection
     */
    private function arrayCollectionPrepend(Collection $collection, $element)
    {
        $result = new ArrayCollection();
        $result->add($element);

        foreach ($collection as $item) {
            $result->add($item);
        }

        return $result;
    }

    /**
     * Set what type of models RIA will be used (qualified or non-qualified).
     *
     * @param bool $value
     */
    protected function setIsQualifiedModel($value)
    {
        /** @var Session $session */
        $session = $this->get('session');
        $session->set('prospect.is_qualified', (bool) $value);
    }

    /**
     * Set what type of models RIA will be used (qualified or non-qualified).
     *
     * @return bool
     */
    protected function getIsQualifiedModel()
    {
        /** @var Session $session */
        $session = $this->get('session');

        return (bool) $session->get('prospect.is_qualified', false);
    }
}
