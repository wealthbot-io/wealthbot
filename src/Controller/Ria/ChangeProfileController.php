<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 09.01.13
 * Time: 17:15
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Ria;

use App\Form\Type\AdvisorCodeFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Event\AdminEvents;
use App\Entity\CeModel;
use App\Event\UserHistoryEvent;
use App\Repository\SubclassRepository;
use App\Entity\RiaCompanyInformation;
use App\Form\Handler\RiaProposalFormHandler;
use App\Form\Type\AdvisorCodesCollectionFormType;
use App\Form\Type\ProfileFormType;
use App\Form\Type\RegistrationStepOneFormType;
use App\Form\Type\RiaAlertsConfigurationFormType;
use App\Form\Type\RiaCompanyInformationFourType;
use App\Form\Type\RiaCompanyInformationThreeType;
use App\Form\Type\RiaCompanyInformationTwoFormType;
use App\Form\Type\RiaCompanyInformationType;
use App\Form\Type\RiaCustodianFormType;
use App\Form\Type\RiaProposalFormType;
use App\Entity\User;
use App\Form\Handler\DocumentsFormHandler;
use App\Form\Type\RiaDocumentsFormType;

class ChangeProfileController extends Controller
{
    public function index(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $documentManager = $this->get('wealthbot_user.document_manager');
        $documentForm = $this->createForm(RiaDocumentsFormType::class);

        /** @var User $user */
        $user = $this->getUser();
        $riaCompanyInfo = $user->getRiaCompanyInformation();


        $admin = $this->get('wealthbot.manager.user')->getAdmin();

        if (!$riaCompanyInfo) {
            throw $this->createNotFoundException('Company profile with id %s not found');
        }

        $riaAlertsConfiguration = $user->getAlertsConfiguration();

        if (!$riaAlertsConfiguration) {
            $riaAlertsConfiguration = $this->get('wealthbot_mailer.alerts_configuration_manager')->saveDefaultConfiguration($user);
        }

        /** @var $parentModel CeModel */
        $parentModel = $riaCompanyInfo->getPortfolioModel();
        $isCustomModel = $parentModel->isCustom();
        $session = $this->get('session');

        $companyForm = $this->createForm(RiaCompanyInformationType::class, $riaCompanyInfo, ['user'=>$user,'isPreSave'=>true]);
        ///$proposalForm = $this->createForm(RiaProposalFormType::class, $user);
        $marketingForm = $this->createForm(RiaCompanyInformationFourType::class, $riaCompanyInfo, ['user'=>$user,'isPreSave'=> false]);
        $alertsConfigurationForm = $this->createForm(RiaAlertsConfigurationFormType::class, $riaAlertsConfiguration);
        $billingAndAccountsForm  = $this->createForm(RiaCompanyInformationTwoFormType::class, $riaCompanyInfo, ['user'=>$user,'is_pre_save'=> false]);
        $portfolioManagementForm = $this->createForm(
            RiaCompanyInformationThreeType::class,
            $riaCompanyInfo,
            ['em'=>$em, 'user'=>$user,'isPreSave' =>false, 'isModels' => false, 'isChangeProfile' =>true,'session' => $session]
        );


        $documentForm = $this->createForm(RiaDocumentsFormType::class);

        return $this->render(
            '/Ria/ChangeProfile/index.html.twig',
            [
                'company_information' => $riaCompanyInfo,
                'currentModel' => $parentModel,
                'isCustomModel' => $isCustomModel,
                'modelType' => $parentModel->isCustom() ? 'Custom' : 'Strategy',
                'companyForm' => $companyForm->createView(),
                //'proposal_form' => $proposalForm->createView(),
                'marketingForm' => $marketingForm->createView(),
                'billingAndAccountsForm'  => $billingAndAccountsForm->createView(),
                'portfolioManagementForm' => $portfolioManagementForm->createView(),
                'updatePasswordForm' => $this->get('wealthbot_user.update_password.form')->createView(),
                'admin_documents' => $documentManager->getUserDocuments($admin->getId()),
                'documents' => $documentManager->getUserDocuments($user->getId()),
                'documents_form' => $documentForm->createView(),
                'alertsConfigurationForm' => $alertsConfigurationForm->createView(),
                'active_tab' => $request->get('tab', null),
            ]
        );
    }

    public function custodianTab()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $this->getUser();
        $riaCompanyInfo = $user->getRiaCompanyInformation();

        $custodianForm = $this->createForm(
            RiaCustodianFormType::class,
            null,
            [
                'ria' => $riaCompanyInfo
            ]
        );

        $advisorCodes = $em->getRepository('App\Entity\AdvisorCode')
            ->findBy([
                'riaCompany' => $riaCompanyInfo,
                'custodianId' => $riaCompanyInfo->getCustodianId(),
            ]);


        $advisorCodesForm = $this->createForm(AdvisorCodesCollectionFormType::class, ['advisorCodes' => $advisorCodes]);

        /* @var \App\Repository\CustodianRepository $custodianRepo */
        $custodianRepo = $em->getRepository('App\Entity\Custodian');
        $custodians = $custodianRepo->findAll();

        return $this->render(
            '/Ria/ChangeProfile/_custodians_form.html.twig',
            [
                'custodianForm' => $custodianForm->createView(),
                'advisorCodesForm' => $advisorCodesForm->createView(),
                'custodians' => $custodians,
            ]
        );
    }

    public function advisorCodes(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $custodianId = $request->query->get('custodian_id');
        $companyInformation = $this->getUser()->getRiaCompanyInformation();

        $advisorCodes = $em->getRepository('App\Entity\AdvisorCode')
            ->findBy([
                'riaCompany' => $companyInformation,
                'custodianId' => $custodianId,
        ]);

        $advisorCodesForm = $this->createForm(AdvisorCodesCollectionFormType::class, ['advisorCodes' => $advisorCodes]);

        return $this->render(
            '/Ria/ChangeProfile/advisor_codes.html.twig',
            [
                'advisorCodesForm' => $advisorCodesForm->createView(),
            ]
        );
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
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();

        $riaCompanyInfo = $em->getRepository('App\Entity\RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $companyForm = $this->createForm(RiaCompanyInformationType::class, $riaCompanyInfo, [ 'user' => $this->getUser(),'isPreSave' => false]);
        $companyForm->get('name')->setData($companyForm->get('name')->getData());

        if ('POST' === $request->getMethod()) {
            $companyForm->handleRequest($request);

            if ($companyForm->isValid()) {
                $riaCompanyInformation = $companyForm->getData();
                if (null !== $companyForm->get('logo_file')->getData()) {
                    $riaCompanyInformation->preUpload();
                }
                $em->persist($riaCompanyInformation);
                $em->flush();

                $this->dispatchHistoryEvent($user, 'Updated company profile.');
            }
        }

        return $this->redirectToRoute('rx_ria_change_profile');
    }

    public function saveProposal(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $emailService = $this->get('wealthbot.mailer');

        /** @var User $ria */
        $ria = $this->getUser();

        /** @var RiaCompanyInformation $riaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();

        $riaProposalForm = $this->createForm(RiaProposalFormType::class, $riaCompanyInformation);
        $riaProposalFormHandler = new RiaProposalFormHandler($riaProposalForm, $request, $em, ['email_service' => $emailService]);

        $riaProposalFormHandler->process();

        return $this->render('/Ria/ChangeProfile/_proposal_form.html.twig', [
            'form' => $riaProposalForm->createView(),
            'documents' => $documentManager->getUserDocuments($ria->getId()),
        ]);
    }

//    /**
//     * Save marketing your firm information
//     *
//     * @param Request $request
//     * @return Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
//     */
//    public function saveMarketing(Request $request)
//    {
//        /** @var \Doctrine\ORM\EntityManager $em */
//        $em = $this->get('doctrine.orm.entity_manager');
//        $user = $this->getUser();
//
//        $riaCompanyInfo = $em->getRepository('App\Entity\RiaCompanyInformation')->findOneBy(
//            array('ria_user_id' => $user->getId())
//        );
//
//        if(!$riaCompanyInfo){
//            return $this->createNotFoundException("Company profile with id %s not found");
//        }
//
//        $marketingForm = $this->createForm(new RiaCompanyInformationFourType($user, false), $riaCompanyInfo);
//
//        if ($request->isMethod('post')) {
//
//            $marketingForm->handleRequest($request);
//
//            if($marketingForm->isValid()){
//                $riaCompanyInfo = $marketingForm->getData();
//                $em->persist($riaCompanyInfo);
//                $em->flush();
//
//                $this->dispatchHistoryEvent($user, 'Updated marketing settings.');
//            }
//        }
//
//        return $this->render('/Ria/ChangeProfile/_marketing_form.html.twig', array('form' => $marketingForm->createView()));
//    }

    public function saveBilling(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();

        $riaCompanyInfo = $em->getRepository('App\Entity\RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $billingAndAccountsForm = $this->createForm(RiaCompanyInformationTwoFormType::class, $riaCompanyInfo, ['user'=>$user,'is_pre_save'=> false]);

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
                $billingAndAccountsForm = $this->createForm(RiaCompanyInformationTwoFormType::class, $riaCompanyInfo, ['user' => $user, 'is_pre_save' => false]);

                $this->dispatchHistoryEvent($user, 'Updated billing and accounts settings.');
            }
        }

        return $this->render('/Ria/ChangeProfile/_billing_n_accounts_form.html.twig', ['form' => $billingAndAccountsForm->createView(), 'show_alerts' => true]);
    }

    public function savePortfolioManagement(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $subclassRepo SubclassRepository */
        $subclassRepo = $em->getRepository('App\Entity\Subclass');

        $user = $this->getUser();

        $riaCompanyInfo = $em->getRepository('App\Entity\RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        /** @var $parentModel CeModel */
        $parentModel = $riaCompanyInfo->getPortfolioModel();

        $isCustomModel = $parentModel->isCustom();

        $riaSubs = $subclassRepo->findRiaSubclasses($user->getId());
        $subclasses = $subclassRepo->findAdminSubclasses();

        $riaSubclassCollection = [];
        foreach ($riaSubs as $sub) {
            $riaSubclassCollection[] = $sub;
        }

        $session = $this->get('session');

        $portfolioManagementForm = $this->createForm(
            RiaCompanyInformationThreeType::class,
            $riaCompanyInfo,
            [
               'em'=> $em,
               'user' => $user,
               'isPreSave'=> false,
               'isModels' => false,
               'isChangeProfile'=> true,
                'session' => $session
        ]
        );

        if ($request->isMethod('POST')) {
            $portfolioManagementForm->handleRequest($request);
            if ($portfolioManagementForm->isValid()) {
                $riaCompanyInfo = $portfolioManagementForm->getData();

                $em->persist($riaCompanyInfo);

                foreach ($riaSubclassCollection as $key => $riaSubclass) {
                    if (1 === $riaCompanyInfo->getAccountManaged() &&
                        !$riaCompanyInfo->getIsAllowRetirementPlan() &&
                        isset($subclasses[$key])) {
                        $riaSubclass->setAccountType($subclasses[$key]->getAccountType());
                    }
                    $em->persist($riaSubclass);
                }
                $em->flush();

                $this->dispatchHistoryEvent($user, 'Updated portfolio management settings.');

                return $this->redirect($this->generateUrl('rx_ria_change_profile_save_portfolio_management'));
            }
        }

        return $this->redirectToRoute('rx_ria_change_profile');
    }

    public function updatePassword()
    {
        $user = $this->getUser();

        /** @var $form \FOS\UserBundle\Form\Type\ChangePasswordFormType */
        $form = $this->get('wealthbot_user.update_password.form');
        /** @var $formHandler \FOS\UserBundle\Form\Handler\ChangePasswordFormHandler */
        $formHandler = $this->get('wealthbot_user.update_password.form.handler');
        $process = $formHandler->process($user);

        if ($process) {
            $this->dispatchHistoryEvent($user, 'Updated password.');

            $this->get('wealthbot.mailer')->sendRiaChangePasswordEmail($user);
            $this->get('session')->getFlashBag()->add('success', 'Password successfully updated.');

            return $this->redirect($this->generateUrl('rx_ria_change_profile_update_password'));
        }

        return $this->render('/Ria/ChangeProfile/_update_password.html.twig', ['form' => $form->createView()]);
    }

    public function uploadDocuments(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $user = $this->getUser();

        $form = $this->createForm(RiaDocumentsFormType::class);
        $formHandler = new DocumentsFormHandler($form, $request, $em, $this->get('wealthbot.mailer'), array('documents_owner' => $user));

        if ($request->isMethod('post')) {
            $formHandler->process();
            return $this->redirectToRoute('rx_ria_change_profile');
        }

        $admin = $this->get('wealthbot.manager.user')->getAdmin();
        $documentManager = $this->container->get('wealthbot_user.document_manager');

        return $this->render('Ria/ChangeProfile/_documents.html.twig', [
            'form' => $form->createView(),
            'admin_documents' => $documentManager->getUserDocuments($admin->getId()),
            'documents' => $documentManager->getUserDocuments($user->getId()),
        ]);
    }

    public function saveAlertsConfiguration(Request $request)
    {
        if (!$request->isXmlHttpRequest() || !$request->isMethod('post')) {
            throw $this->createNotFoundException();
        }

        /** @var User $ria */
        $ria = $this->getUser();

        if (!$ria->getAlertsConfiguration()) {
            return $this->createNotFoundException('Ria alerts configuration not found');
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $alertsConfigurationForm = $this->createForm(RiaAlertsConfigurationFormType::class, $ria->getAlertsConfiguration());

        $alertsConfigurationForm->handleRequest($request);
        if ($alertsConfigurationForm->isValid()) {
            $alertsConfiguration = $alertsConfigurationForm->getData();

            $em->persist($alertsConfiguration);
            $em->flush();

            return $this->json(['status' => 'success']);
        }

        return $this->json([
            'status' => 'error',
            'content' => $this->renderView('/Ria/ChangeProfile/_alerts_configuration_form_fields.html.twig', [
                'form' => $alertsConfigurationForm->createView(),
            ]),
        ]);
    }

    public function profile(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $profileForm = $this->createForm(ProfileFormType::class, $user);
        $riaAlertsConfiguration = $user->getAlertsConfiguration();
        $alertsConfigurationForm = $this->createForm(RiaAlertsConfigurationFormType::class, $riaAlertsConfiguration);

        if ($request->isMethod('post')) {
            $profileForm->handleRequest($request);
            if ($profileForm->isValid()) {
                $em->persist($profileForm->getData());
                $em->flush();
            }
        }

        $updatePasswordForm = $this->createForm('App\Form\Type\UpdatePasswordFormType', $user);

        return $this->render(
            '/Ria/ChangeProfile/profile.html.twig',
            [
                'profileForm' => $profileForm->createView(),
                'updatePasswordForm' => $updatePasswordForm->createView(),
                'alertsConfigurationForm' => $alertsConfigurationForm->createView(),
                'active_tab' => $request->get('tab', null),
            ]
        );
    }

    public function saveCustodians(Request $request)
    {
        $user = $this->getUser();
        $custodianId = $request->get('custodian_id');
        $em = $this->get('doctrine.orm.entity_manager');
        /* @var \App\Repository\CustodianRepository $custodianRepo */
        $custodianRepo = $em->getRepository('App\Entity\Custodian');
        $companyInformation = $user->getRiaCompanyInformation();
        $custodian = $custodianRepo->find($custodianId);
        $custodians = $custodianRepo->findAll();

        $originalAdvisorCodes = [];

        $advisorCodes = $em->getRepository('App\Entity\AdvisorCode')
            ->findBy([
                'riaCompany' => $companyInformation,
                'custodian' => $custodian,
        ]);

        foreach ($advisorCodes as $advisorCode) {
            $originalAdvisorCodes[] = $advisorCode;
        };

        $advisorCodesForm = $this->createForm(AdvisorCodesCollectionFormType::class, $advisorCodes, [
            'em' => $em, 'riaCompany' => $companyInformation, 'custodian' => $custodian
        ]);
        $custodianForm = $this->createForm(RiaCustodianFormType::class, null, [
            'ria' => $companyInformation
        ]);
        if ($request->isMethod('post')) {
            $custodianForm->handleRequest($request);
            $companyInformation->setCustodian($custodian);
            $em->persist($companyInformation);
            $em->flush();


            /*
            $advisorCodesForm->handleRequest($request);

            if ($custodianForm->isValid() && $advisorCodesForm->isValid()) {
                $advisorCodesData = $advisorCodesForm->getData();
                foreach ($advisorCodesData['advisorCodes'] as $advisorCode) {
                    foreach ($originalAdvisorCodes as $key => $toDel) {
                        if ($toDel->getId() === $advisorCode->getId()) {
                            unset($originalAdvisorCodes[$key]);
                        }
                    }
                }
                foreach ($originalAdvisorCodes as $advisorCode) {
                    $em->remove($advisorCode);
                }
                foreach ($advisorCodesData['advisorCodes'] as $advisorCodeEntity) {
                    $em->persist($advisorCodeEntity);
                }
            */

            return $this->redirect($this->generateUrl('rx_ria_change_profile'));
            // }
        }

        return $this->render(
            '/Ria/ChangeProfile/_custodians_form.html.twig',
            [
                'custodianForm' => $custodianForm->createView(),
                'advisorCodesForm' => $advisorCodesForm->createView(),
                'custodians' => $custodians,
            ]
        );
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
