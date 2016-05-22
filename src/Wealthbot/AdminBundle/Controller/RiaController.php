<?php

namespace Wealthbot\AdminBundle\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Entity\Fee;
use Wealthbot\AdminBundle\Form\Type\AdminFeesType;
use Wealthbot\AdminBundle\Form\Type\RiaRelationshipFormType;
use Wealthbot\AdminBundle\Manager\UserHistoryManager;
use Wealthbot\AdminBundle\Model\Acl;
use Wealthbot\AdminBundle\Repository\AssetClassRepository;
use Wealthbot\AdminBundle\Repository\CeModelRepository;
use Wealthbot\AdminBundle\Repository\SubclassRepository;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationFourType;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationThreeType;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationTwoFormType;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationType;
use Wealthbot\UserBundle\Entity\User;

class RiaController extends AclController
{
    public function indexAction(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var \Wealthbot\UserBundle\Repository\UserRepository $repository */
        $repository = $em->getRepository('WealthbotUserBundle:User');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $repository->findRiasQuery(),
            $request->get('page', 1),
            $this->container->getParameter('pager_per_page')/*limit per page*/
        );

        return $this->render('WealthbotAdminBundle:Ria:index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    public function specificDashboardAction(Request $request)
    {
        /** @var $em EntityManager */
        /* @var UserHistoryManager $historyManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $historyManager = $this->get('wealthbot_admin.user_history.manager');

        $ria = $em->getRepository('WealthbotUserBundle:User')->find($request->get('id'));
        if (!$ria) {
            throw $this->createNotFoundException('Ria does not exist.');
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $em->getRepository('WealthbotUserBundle:User')->findClientsByRiaIdQuery($ria->getId()),
            $request->get('page', 1),
            $this->container->getParameter('pager_per_page')
        );

        $historyPagination = $paginator->paginate(
            $historyManager->findBy(['user_id' => $ria->getId()], ['created' => 'DESC']),
            $request->get('history_page', 1),
            $this->container->getParameter('pager_per_page'),
            ['pageParameterName' => 'history_page']
        );

        if ($request->isXmlHttpRequest()) {
            if ($request->get('page')) {
                return $this->getJsonResponse([
                    'status' => 'success',
                    'content' => $this->renderView('WealthbotAdminBundle:Ria:_clients_list.html.twig', ['pagination' => $pagination]),
                    'pagination_type' => 'clients',
                ]);
            } elseif ($request->get('history_page')) {
                return $this->getJsonResponse([
                    'status' => 'success',
                    'content' => $this->renderView('WealthbotAdminBundle:Ria:_history.html.twig', ['history_pagination' => $historyPagination]),
                    'pagination_type' => 'history',
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                ]);
            }
        }

        $riaCompanyInfo = $ria->getRiaCompanyInformation();

        $basicInfo['companyInformation'] = $riaCompanyInfo;
        $basicInfo['riaUsers'] = $em->getRepository('WealthbotUserBundle:User')->getUsersByRiaId($ria->getId());

        $riaRelationshipForm = $this->createForm(new RiaRelationshipFormType(), $riaCompanyInfo);

        $basicInfo['relationship_form'] = $riaRelationshipForm->createView();

        if ($basicInfo['companyInformation'] && $basicInfo['companyInformation']->getPortfolioModel()) {
            /** @var $portfolio CeModel */
            $portfolio = $basicInfo['companyInformation']->getPortfolioModel();

            $basicInfo['modelType'] = $portfolio->getTypeName();
        } else {
            $basicInfo['modelType'] = 'No model';
        }

        return $this->render('WealthbotAdminBundle:Ria:specific_dashboard.html.twig', [
            'basicInfo' => $basicInfo,
            'pagination' => $pagination,
            'history_pagination' => $historyPagination,
        ]);
    }

    public function activateAction(Request $request)
    {
        $user = $this->getUser();

        $this->checkAccess(Acl::PERMISSION_EDIT, $user);

        /** @var $em EntityManager */
        /* @var $repo CeModelRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('WealthbotAdminBundle:CeModel');

        $activate = (bool) $request->get('activate');
        $ria = $em->getRepository('WealthbotUserBundle:User')->find($request->get('id'));
        if (!$ria) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Ria does not exist.',
            ]);
        }

        $companyInformation = $ria->getRiaCompanyInformation();
        if (!$companyInformation) {
            return $this->getJsonResponse([
                'status' => 'error',
                'message' => 'Ria have not company information.',
            ]);
        }

        if ($activate) {
            $errors = [];

            /** @var AssetClassRepository $assetClassRepository */
            $assetClassRepository = $em->getRepository('WealthbotAdminBundle:AssetClass');

            if (!$ria->getCustodian()) {
                $errors[] = 'Ria have not selected custodian';
            }

            if (!$companyInformation->getRebalancedMethod()) {
                $errors[] = 'Ria have not customize rebalancing setting';
            }

            $assetClasses = $assetClassRepository->findWithSubclassesByModelIdAndOwnerId($companyInformation->getPortfolioModel()->getId(), $ria->getId());
            if (empty($assetClasses)) {
                $errors[] = 'Ria have not created asset and subclasses';
            }

            $securityAssignments = $em->getRepository('WealthbotAdminBundle:SecurityAssignment')->findBy(['model_id' => $companyInformation->getPortfolioModel()->getId()]);
            if (empty($securityAssignments)) {
                $errors[] = 'Ria have not assigned classes and subclasses.';
            }

            $finishedModel = $repo->findCompletedModelByParentIdAndOwnerId($companyInformation->getPortfolioModelId(), $ria->getId());
            if (!$finishedModel) {
                $errors[] = 'Ria have not completed models.';
            }

            $modelWithoutRiskRating = $repo->findModelWithoutRiskRatingByRiaId($ria->getId());
            if (count($modelWithoutRiskRating) > 1) {
                $errors[] = 'Ria have models without risk rating.';
            }

            $existQuestions = $em->getRepository('WealthbotRiaBundle:RiskQuestion')->findOneBy(['owner_id' => $ria->getId()]);
            if (!$existQuestions) {
                $errors[] = 'Ria have not completed risk profiling section.';
            }

            if (count($errors)) {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'message' => implode(' ', $errors),
                ]);
            }

            $this->get('wealthbot.mailer')->sendRiaActivatedEmail($ria);
        }

        $companyInformation->setActivated($activate);
        $em->persist($companyInformation);

        $em->flush();

        return $this->getJsonResponse([
            'status' => 'success',
            'url' => $this->generateUrl('rx_admin_ria_activate', ['id' => $ria->getId(), 'activate' => (int) !$activate]),
        ]);
    }

    public function riaSettingsAction($ria_id)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $em->getRepository('WealthbotUserBundle:User')->find($ria_id);
        if (!$ria) {
            $this->createNotFoundException('Ria with id: '.$ria_id.', does not exists.');
        }

        $admin = $this->getUser();
        $data = $this->get('wealthbot.manager.fee')->getAdminFee($ria);

        $form = $this->createForm(new AdminFeesType($admin, $ria));
        $form->get('fees')->setData($data);

        return $this->render('WealthbotAdminBundle:Ria:_settings.html.twig', [
            'ria' => $ria,
            'form' => $form->createView(),
        ]);
    }

    public function updateFeesAction($ria_id, Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $em->getRepository('WealthbotUserBundle:User')->find($ria_id);
        if (!$ria) {
            return new Response(sprintf('Ria with id: %d, does not exists.', $ria_id));
        }

        $admin = $this->getUser();
        $data = $this->get('wealthbot.manager.fee')->getAdminFee($ria);

        $form = $this->createForm(new AdminFeesType($admin, $ria));

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            $fees = $form->get('fees')->getData();

            if ($form->isValid()) {
                if ($data !== $fees) {
                    foreach ($data as $riaFee) {
                        if ($riaFee->getAppointedUser() && $riaFee->getAppointedUser()->getId() === $ria->getId()) {
                            $em->remove($riaFee);
                        }
                    }

                    $em->flush();

                    $fees = $form->get('fees')->getData();
                    foreach ($fees as $fee) {
                        $em->persist($fee);
                    }

                    $em->flush();
                }

                $formNew = $this->createForm(new AdminFeesType($admin, $ria));
                $formNew->get('fees')->setData($fees);

                return $this->getJsonResponse([
                    'status' => 'success',
                    'content' => $this->renderView('WealthbotAdminBundle:Ria:_ria_fees_form.html.twig', [
                        'form' => $formNew->createView(),
                        'ria' => $ria,
                    ]),
                ]);
            } else {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'content' => $this->renderView('WealthbotAdminBundle:Ria:_ria_fees_form.html.twig', [
                        'form' => $form->createView(),
                        'ria' => $ria,
                    ]),
                ]);
            }
        }

        return $this->getJsonResponse(['status' => 'success']);
    }

    public function updateRelationshipAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $feeManager = $this->get('wealthbot.manager.fee');
        $userManager = $this->get('wealthbot.manager.user');

        /** @var User $ria */
        $ria = $userManager->find($request->get('id'));

        if (!$request->isXmlHttpRequest() || !$ria) {
            $this->createNotFoundException();
        }

        $form = $this->createForm(new RiaRelationshipFormType(), $ria->getRiaCompanyInformation());

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                /** @var RiaCompanyInformation $riaCompanyInformation */
                $riaCompanyInformation = $form->getData();

                if ($riaCompanyInformation->getRelationshipType() === RiaCompanyInformation::RELATIONSHIP_TYPE_LICENSE_FEE) {
                    $feeManager->resetRiaFee($ria);
                }

                $em->persist($riaCompanyInformation);
                $em->flush();

                $admin = $userManager->getAdmin();

                $fees = $feeManager->getAdminFee($ria);

                $feeForm = $this->createForm(new AdminFeesType($admin, $ria));
                $feeForm->get('fees')->setData($fees);

                return $this->getJsonResponse([
                    'status' => 'success',
                    'fees_content' => $this->renderView('WealthbotAdminBundle:Ria:_ria_fees_form.html.twig', [
                        'form' => $feeForm->createView(),
                        'ria' => $ria,
                    ]),
                ]);
            }
        }

        return $this->getJsonResponse([
            'status' => 'error',
        ]);
    }

    /**
     * Save company information.
     *
     * @param Request $request
     *
     * @return Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveCompanyProfileAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('WealthbotUserBundle:User')->find($request->get('ria_id'));
        if (!$user) {
            return new Response(printf('Ria with id: %d, does not exists.', $request->get('ria_id')));
        }

        $riaCompanyInfo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $companyForm = $this->createForm(new RiaCompanyInformationType($user, false), $riaCompanyInfo);

        if ($request->getMethod() === 'POST') {
            $companyForm->handleRequest($request);

            if ($companyForm->isValid()) {
                $riaCompanyInfo = $companyForm->getData();
                $em->persist($riaCompanyInfo);
                $em->flush();
            }
        }

        return $this->render('WealthbotAdminBundle:Ria:_company_profile_form.html.twig', ['form' => $companyForm->createView()]);
    }

    /**
     * Save marketing your firm information.
     *
     * @param Request $request
     *
     * @return Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveMarketingAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('WealthbotUserBundle:User')->find($request->get('ria_id'));
        if (!$user) {
            return new Response(printf('Ria with id: %d, does not exists.', $request->get('ria_id')));
        }

        $riaCompanyInfo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $marketingForm = $this->createForm(new RiaCompanyInformationFourType($user, false), $riaCompanyInfo);

        if ($request->isMethod('post')) {
            $marketingForm->handleRequest($request);

            if ($marketingForm->isValid()) {
                $riaCompanyInfo = $marketingForm->getData();
                $em->persist($riaCompanyInfo);
                $em->flush();
            }
        }

        return $this->render('WealthbotAdminBundle:Ria:_marketing_form.html.twig', ['form' => $marketingForm->createView()]);
    }

    public function saveBillingAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('WealthbotUserBundle:User')->find($request->get('ria_id'));
        if (!$user) {
            return new Response(printf('Ria with id: %d, does not exists.', $request->get('ria_id')));
        }

        $riaCompanyInfo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $billingAndAccountsForm = $this->createForm(new RiaCompanyInformationTwoFormType($user, false), $riaCompanyInfo);

        if ($request->getMethod() === 'POST') {
            $billingAndAccountsForm->handleRequest($request);

            if ($billingAndAccountsForm->isValid()) {
                $originalFees = [];
                foreach ($user->getFees() as $fee) {
                    $originalFees[] = $fee;
                }

                $fees = $billingAndAccountsForm['fees']->getData();
                foreach ($fees as $fee) {
                    $fee->setOwner($user);
                    $em->persist($fee);

                    foreach ($originalFees as $key => $toDel) {
                        if ($fee->getId() === $toDel->getId()) {
                            unset($originalFees[$key]);
                        }
                    }
                }

                foreach ($originalFees as $fee) {
                    $em->remove($fee);
                }
                $em->flush();

                $em->refresh($user);
                $em->refresh($riaCompanyInfo);
                $billingAndAccountsForm = $this->createForm(new RiaCompanyInformationTwoFormType($user, false), $riaCompanyInfo);
            }
        }

        return $this->render('WealthbotAdminBundle:Ria:_billing_n_accounts_form.html.twig', ['form' => $billingAndAccountsForm->createView(), 'show_alert' => true]);
    }

    public function savePortfolioManagementAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $subclassRepo SubclassRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $subclassRepo = $em->getRepository('WealthbotAdminBundle:Subclass');
        $userRepo = $em->getRepository('WealthbotUserBundle:User');
        $riaCompanyRepo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation');

        $ria = $userRepo->find($request->get('ria_id'));
        if (!$ria) {
            return new Response(printf('Ria with id: %d, does not exists.', $request->get('ria_id')));
        }

        $riaCompanyInfo = $riaCompanyRepo->findOneBy(
            ['ria_user_id' => $ria->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        /** @var $portfolioModel CeModel */
        $portfolioModel = $riaCompanyInfo->getPortfolioModel();

        $session = $this->get('session');
        $portfolioManagementForm = $this->createForm(
            new RiaCompanyInformationThreeType($em, $ria, false, false),
            $riaCompanyInfo,
            ['session' => $session]
        );

        if ($request->getMethod() === 'POST') {
            $portfolioManagementForm->handleRequest($request);

            if ($portfolioManagementForm->isValid()) {
                $riaCompanyInfo = $portfolioManagementForm->getData();
                $em->persist($riaCompanyInfo);

                $riaSubs = $subclassRepo->findRiaSubclasses($ria->getId());
                $subclasses = $subclassRepo->findAdminSubclasses();

                $riaSubclassCollection = [];
                foreach ($riaSubs as $sub) {
                    $riaSubclassCollection[] = $sub;
                }

                foreach ($riaSubclassCollection as $key => $riaSubclass) {
                    if ($riaCompanyInfo->getAccountManaged() === 1 && !$riaCompanyInfo->getIsAllowRetirementPlan()) {
                        $riaSubclass->setAccountType($subclasses[$key]->getAccountType());
                    }
                    $em->persist($riaSubclass);
                }

                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_ria_sd_save_portfolio_management', ['ria_id' => $ria->getId()]));
            }
        }

        return $this->render('WealthbotAdminBundle:Ria:_portfolio_management_form.html.twig', [
            'form' => $portfolioManagementForm->createView(),
            'company_information' => $riaCompanyInfo,
            'currentModel' => $portfolioModel,
        ]);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
