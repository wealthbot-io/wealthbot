<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\CeModel;
use App\Form\Type\AdminFeesType;
use App\Form\Type\RiaRelationshipFormType;
use App\Manager\UserHistoryManager;
use App\Model\Acl;
use App\Repository\AssetClassRepository;
use App\Repository\CeModelRepository;
use App\Repository\SubclassRepository;
use App\Entity\RiaCompanyInformation;
use App\Form\Type\RiaCompanyInformationFourType;
use App\Form\Type\RiaCompanyInformationThreeType;
use App\Form\Type\RiaCompanyInformationTwoFormType;
use App\Form\Type\RiaCompanyInformationType;
use App\Entity\User;

class RiaController extends AclController
{
    public function index(Request $request)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var \Repository\UserRepository $repository */
        $repository = $em->getRepository('App\Entity\User');

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $repository->findRiasQuery(),
            $request->get('page', 1),
            $this->container->getParameter('pager_per_page')/*limit per page*/
        );

        return $this->render('/Admin/Ria/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    public function specificDashboard(Request $request)
    {
        /** @var $em EntityManager */
        /* @var UserHistoryManager $historyManager */
        $em = $this->get('doctrine.orm.entity_manager');
        $historyManager = $this->get('wealthbot_admin.user_history.manager');

        $ria = $em->getRepository('App\Entity\User')->find($request->get('id'));
        if (!$ria) {
            throw $this->createNotFoundException('Ria does not exist.');
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $em->getRepository('App\Entity\User')->findClientsByRiaIdQuery($ria->getId()),
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
                return $this->json([
                    'status' => 'success',
                    'content' => $this->renderView('/Admin/Ria/_clients_list.html.twig', ['pagination' => $pagination]),
                    'pagination_type' => 'clients',
                ]);
            } elseif ($request->get('history_page')) {
                return $this->json([
                    'status' => 'success',
                    'content' => $this->renderView('/Admin/Ria/_history.html.twig', ['history_pagination' => $historyPagination]),
                    'pagination_type' => 'history',
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                ]);
            }
        }

        $riaCompanyInfo = $ria->getRiaCompanyInformation();

        $basicInfo['companyInformation'] = $riaCompanyInfo;
        $basicInfo['riaUsers'] = $em->getRepository('App\Entity\User')->getUsersByRiaId($ria->getId());

        $riaRelationshipForm = $this->createForm(RiaRelationshipFormType::class, $riaCompanyInfo);

        $basicInfo['relationship_form'] = $riaRelationshipForm->createView();

        if ($basicInfo['companyInformation'] && $basicInfo['companyInformation']->getPortfolioModel()) {
            /** @var $portfolio CeModel */
            $portfolio = $basicInfo['companyInformation']->getPortfolioModel();

            $basicInfo['modelType'] = $portfolio->getTypeName();
        } else {
            $basicInfo['modelType'] = 'No model';
        }

        return $this->render('/Admin/Ria/specific_dashboard.html.twig', [
            'basicInfo' => $basicInfo,
            'pagination' => $pagination,
            'history_pagination' => $historyPagination,
        ]);
    }

    public function activate(Request $request)
    {
        $user = $this->getUser();

        $this->checkAccess(Acl::PERMISSION_EDIT, $user);

        /** @var $em EntityManager */
        /* @var $repo CeModelRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository('App\Entity\CeModel');

        $activate = (bool) $request->get('activate');
        $ria = $em->getRepository('App\Entity\User')->find($request->get('id'));
        if (!$ria) {
            return $this->json([
                'status' => 'error',
                'message' => 'Ria does not exist.',
            ]);
        }

        $companyInformation = $ria->getRiaCompanyInformation();
        if (!$companyInformation) {
            return $this->json([
                'status' => 'error',
                'message' => 'Ria have not company information.',
            ]);
        }

        if ($activate) {
            $errors = [];

            /** @var AssetClassRepository $assetClassRepository */
            $assetClassRepository = $em->getRepository('App\Entity\AssetClass');

            if (!$ria->getCustodian()) {
                $errors[] = 'Ria have not selected custodian';
            }

            if (!$companyInformation->getRebalancedMethod()) {
                $errors[] = 'Ria have not customize rebalancing setting';
            }

            $assetClasses = $assetClassRepository->findWithSubclassesByModelIdAndOwnerId($companyInformation->getPortfolioModel()->getId(), $ria->getId());
            if (empty($assetClasses)) {
                //  $errors[] = 'Ria have not created asset and subclasses';
            }

            $securityAssignments = $em->getRepository('App\Entity\SecurityAssignment')->findBy(['ria_user_id' => $ria]);
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

            $existQuestions = $em->getRepository('App\Entity\RiskQuestion')->findOneBy(['owner_id' => $ria->getId()]);
            if (!$existQuestions) {
                $errors[] = 'Ria have not completed risk profiling section.';
            }

            if (count($errors)) {
                return $this->json([
                    'status' => 'error',
                    'message' => implode(' ', $errors),
                ]);
            }

            $this->get('wealthbot.mailer')->sendRiaActivatedEmail($ria);
        }

        $companyInformation->setActivated($activate);
        $em->persist($companyInformation);

        $em->flush();

        return $this->json([
            'status' => 'success',
            'url' => $this->generateUrl('rx_admin_ria_activate', ['id' => $ria->getId(), 'activate' => (int) !$activate]),
        ]);
    }

    public function riaSettings($ria_id)
    {
        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $em->getRepository('App\Entity\User')->find($ria_id);



        if (!$ria) {
            $this->createNotFoundException('Ria with id: '.$ria_id.', does not exists.');
        }

        $admin = $this->getUser();
        $data = $this->get('wealthbot.manager.fee')->getAdminFee($ria);

        $form = $this->createForm(AdminFeesType::class, null, ['owner' => $admin, 'appointedUser' => $ria]);
        $form->get('fees')->setData($data);

        return $this->render('/Admin/Ria/_settings.html.twig', [
            'ria' => $ria,
            'form' => $form->createView(),
        ]);
    }

    public function updateFees($ria_id, Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var $em EntityManager */
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $em->getRepository('App\Entity\User')->find($ria_id);
        if (!$ria) {
            return new Response(sprintf('Ria with id: %d, does not exists.', $ria_id));
        }

        $admin = $this->getUser();
        $data = $this->get('wealthbot.manager.fee')->getAdminFee($ria);

        $form = $this->createForm(AdminFeesType::class, $this->getUser(), [ 'owner' => $admin , 'appointedUser' => $ria]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->getErrors()->count()==0) {
                $fees = $form->get('fees')->getData();

                if ($data !== $fees) {
                    foreach ($data as $riaFee) {
                        //   if ($riaFee->getAppointedUser() && $riaFee->getAppointedUser()->getId() === $ria->getId()) {
                     ///       $em->remove($riaFee);
                     //   }
                    };

                    foreach ($fees as $fee) {
                        $em->persist($fee);
                        $ria->setAppointedBillingSpec($fee->getBillingSpec());
                        $em->persist($ria);
                    };

                    $em->flush();
                }

                $formNew = $this->createForm(AdminFeesType::class, $this->getUser(), ['owner' =>$admin,'appointedUser'=> $ria]);
                $formNew->get('fees')->setData($fees);

                return $this->json([
                    'status' => 'success',
                    'content' => $this->renderView('/Admin/Ria/_ria_fees_form.html.twig', [
                        'form' => $formNew->createView(),
                        'ria' => $ria,
                    ]),
                ]);
            } else {
                return $this->json([
                    'status' => 'error',
                    'content' => $this->renderView('/Admin/Ria/_ria_fees_form.html.twig', [
                        'form' => $form->createView(),
                        'ria' => $ria,
                    ]),
                ]);
            }
        }

        return $this->json(['status' => 'success']);
    }

    public function updateRelationship(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $feeManager = $this->get('wealthbot.manager.fee');
        $userManager = $this->get('wealthbot.manager.user');

        /** @var User $ria */
        $ria = $userManager->find($request->get('id'));

        if (!$request->isXmlHttpRequest() || !$ria) {
            $this->createNotFoundException();
        }

        $form = $this->createForm(RiaRelationshipFormType::class, $ria->getRiaCompanyInformation());

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var RiaCompanyInformation $riaCompanyInformation */
                $riaCompanyInformation = $form->getData();

                if (RiaCompanyInformation::RELATIONSHIP_TYPE_LICENSE_FEE === $riaCompanyInformation->getRelationshipType()) {
                    $feeManager->resetRiaFee($ria);
                }

                $em->persist($riaCompanyInformation);
                $em->flush();

                $admin = $userManager->getAdmin();

                $fees = $feeManager->getAdminFee($ria);

                $feeForm = $this->createForm(AdminFeesType::class, null, ['owner' =>$admin,'appointedUser'=> $ria]);

                $feeForm->get('fees')->setData($fees);

                return $this->json([
                    'status' => 'success',
                    'fees_content' => $this->renderView('/Admin/Ria/_ria_fees_form.html.twig', [
                        'form' => $feeForm->createView(),
                        'ria' => $ria,
                    ]),
                ]);
            }
        }

        return $this->json([
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
    public function saveCompanyProfile(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('App\Entity\User')->find($request->get('ria_id'));
        if (!$user) {
            return new Response(printf('Ria with id: %d, does not exists.', $request->get('ria_id')));
        }

        $riaCompanyInfo = $em->getRepository('App\Entity\RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $companyForm = $this->createForm(RiaCompanyInformationType::class, $riaCompanyInfo, [
            'user' => $user, 'isPreSave'=> false]);

        if ('POST' === $request->getMethod()) {
            $companyForm->handleRequest($request);

            if ($companyForm->isValid()) {
                $riaCompanyInfo = $companyForm->getData();
                $em->persist($riaCompanyInfo);
                $em->flush();
            }
        }

        return $this->render('/Admin/Ria/_company_profile_form.html.twig', ['form' => $companyForm->createView()]);
    }

    /**
     * Save marketing your firm information.
     *
     * @param Request $request
     *
     * @return Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveMarketing(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('App\Entity\User')->find($request->get('ria_id'));
        if (!$user) {
            return new Response(printf('Ria with id: %d, does not exists.', $request->get('ria_id')));
        }

        $riaCompanyInfo = $em->getRepository('App\Entity\RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $marketingForm = $this->createForm(RiaCompanyInformationFourType::class, $riaCompanyInfo, [ 'user' => $user, 'isPreSave' => false]);

        if ($request->isMethod('post')) {
            $marketingForm->handleRequest($request);

            if ($marketingForm->isValid()) {
                $riaCompanyInfo = $marketingForm->getData();
                $em->persist($riaCompanyInfo);
                $em->flush();
            }
        }

        return $this->render('/Admin/Ria/_marketing_form.html.twig', ['form' => $marketingForm->createView()]);
    }

    public function saveBilling(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('App\Entity\User')->find($request->get('ria_id'));
        if (!$user) {
            return new Response(printf('Ria with id: %d, does not exists.', $request->get('ria_id')));
        }

        $riaCompanyInfo = $em->getRepository('App\Entity\RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $billingAndAccountsForm = $this->createForm(RiaCompanyInformationTwoFormType::class, $riaCompanyInfo, ['user'=>$user, 'is_pre_save' => false]);

        if ('POST' === $request->getMethod()) {
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
                $billingAndAccountsForm = $this->createForm(RiaCompanyInformationTwoFormType::class, $riaCompanyInfo, ['user'=>$user, 'is_pre_save' => false]);
            }
        }

        return $this->render('/Admin/Ria/_billing_n_accounts_form.html.twig', ['form' => $billingAndAccountsForm->createView(), 'show_alert' => true]);
    }

    public function savePortfolioManagement(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $subclassRepo SubclassRepository */
        $em = $this->get('doctrine.orm.entity_manager');
        $subclassRepo = $em->getRepository('App\Entity\Subclass');
        $userRepo = $em->getRepository('App\Entity\User');
        $riaCompanyRepo = $em->getRepository('App\Entity\RiaCompanyInformation');

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
            RiaCompanyInformationThreeType::class,
            $riaCompanyInfo,
            ['session' => $session, 'em' => $em, 'user'=>$ria, 'isChangeProfile' => false, 'isModels' => false]
        );

        if ('POST' === $request->getMethod()) {
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
                    if (1 === $riaCompanyInfo->getAccountManaged() && !$riaCompanyInfo->getIsAllowRetirementPlan()) {
                        $riaSubclass->setAccountType($subclasses[$key]->getAccountType());
                    }
                    $em->persist($riaSubclass);
                }

                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_ria_sd_save_portfolio_management', ['ria_id' => $ria->getId()]));
            }
        }

        return $this->render('/Admin/Ria/_portfolio_management_form.html.twig', [
            'form' => $portfolioManagementForm->createView(),
            'company_information' => $riaCompanyInfo,
            'currentModel' => $portfolioModel,
        ]);
    }
}
