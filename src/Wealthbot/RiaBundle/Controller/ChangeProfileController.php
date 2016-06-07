<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 09.01.13
 * Time: 17:15
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\AdminEvents;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Event\UserHistoryEvent;
use Wealthbot\AdminBundle\Repository\SubclassRepository;
use Wealthbot\RiaBundle\Entity\AdvisorCode;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\RiaBundle\Form\Handler\RiaProposalFormHandler;
use Wealthbot\RiaBundle\Form\Type\AdvisorCodesCollectionFormType;
use Wealthbot\RiaBundle\Form\Type\ProfileFormType;
use Wealthbot\RiaBundle\Form\Type\RegistrationStepOneFormType;
use Wealthbot\RiaBundle\Form\Type\RiaAlertsConfigurationFormType;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationFourType;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationThreeType;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationTwoFormType;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationType;
use Wealthbot\RiaBundle\Form\Type\RiaCustodianFormType;
use Wealthbot\RiaBundle\Form\Type\RiaProposalFormType;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Form\Handler\DocumentsFormHandler;
use Wealthbot\UserBundle\Form\Type\RiaDocumentsFormType;

class ChangeProfileController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $documentManager = $this->get('wealthbot_user.document_manager');
//        $documentForm = $this->createForm(new RiaDocumentsFormType());

        /** @var User $user */
        $user = $this->getUser();
        $riaCompanyInfo = $user->getRiaCompanyInformation();

//        $admin = $this->get('wealthbot.manager.user')->getAdmin();

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

        $companyForm = $this->createForm(new RiaCompanyInformationType($user, false), $riaCompanyInfo);
        $proposalForm = $this->createForm(new RiaProposalFormType($documentManager), $riaCompanyInfo);
//        $marketingForm = $this->createForm(new RiaCompanyInformationFourType($user, false), $riaCompanyInfo);
        //$alertsConfigurationForm = $this->createForm(new RiaAlertsConfigurationFormType(), $riaAlertsConfiguration);
//        $billingAndAccountsForm  = $this->createForm(new RiaCompanyInformationTwoFormType($user, false), $riaCompanyInfo);
        $portfolioManagementForm = $this->createForm(
            new RiaCompanyInformationThreeType($em, $user, false, false, true),
            $riaCompanyInfo,
            ['session' => $session]
        );

//        RegistrationStepOneFormType
        return $this->render('WealthbotRiaBundle:ChangeProfile:index.html.twig',
            [
                'company_information' => $riaCompanyInfo,
                'currentModel' => $parentModel,
                'isCustomModel' => $isCustomModel,
                'modelType' => $parentModel->isCustom() ? 'Custom' : 'Strategy',
                'companyForm' => $companyForm->createView(),
                'proposal_form' => $proposalForm->createView(),
//                'marketingForm' => $marketingForm->createView(),
//                'billingAndAccountsForm'  => $billingAndAccountsForm->createView(),
                'portfolioManagementForm' => $portfolioManagementForm->createView(),
                //'updatePasswordForm' => $this->get('wealthbot_user.update_password.form')->createView(),
//                'admin_documents' => $documentManager->getUserDocuments($admin->getId()),
                'documents' => $documentManager->getUserDocuments($user->getId()),
//                'documents_form' => $documentForm->createView(),
                //'alertsConfigurationForm' => $alertsConfigurationForm->createView(),
                'active_tab' => $request->get('tab', null),
            ]
        );
    }

    public function custodianTabAction()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var User $user */
        $user = $this->getUser();
        $riaCompanyInfo = $user->getRiaCompanyInformation();

        $custodianForm = $this->createForm(new RiaCustodianFormType($em), $riaCompanyInfo);
        $advisorCodesForm = $this->createForm(new AdvisorCodesCollectionFormType($em));

        /* @var \Wealthbot\AdminBundle\Repository\CustodianRepository $custodianRepo */
        $custodianRepo = $em->getRepository('WealthbotAdminBundle:Custodian');
        $custodians = $custodianRepo->findAll();

        return $this->render('WealthbotRiaBundle:ChangeProfile:_custodians_form.html.twig',
            [
                'custodianForm' => $custodianForm->createView(),
                'advisorCodesForm' => $advisorCodesForm->createView(),
                'custodians' => $custodians,
            ]);
    }

    public function advisorCodesAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        $custodianId = $request->query->get('custodian_id');
        $companyInformation = $this->getUser()->getRiaCompanyInformation();

        $advisorCodes = $em->getRepository('WealthbotRiaBundle:AdvisorCode')
            ->findBy([
                'riaCompany' => $companyInformation,
                'custodianId' => $custodianId,
        ]);

        $advisorCodesForm = $this->createForm(new AdvisorCodesCollectionFormType($em), ['advisorCodes' => $advisorCodes]);

        return $this->render('WealthbotRiaBundle:ChangeProfile:advisor_codes.html.twig',
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
    public function saveCompanyProfileAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();

        $riaCompanyInfo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findOneBy(
            ['ria_user_id' => $user->getId()]
        );

        if (!$riaCompanyInfo) {
            return $this->createNotFoundException('Company profile with id %s not found');
        }

        $companyForm = $this->createForm(new RiaCompanyInformationType($this->getUser(), false), $riaCompanyInfo);
        $companyForm->get('name')->setData($companyForm->get('name')->getData());

        if ($request->getMethod() === 'POST') {
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

        return $this->render('WealthbotRiaBundle:ChangeProfile:_company_profile_form.html.twig', ['form' => $companyForm->createView()]);
    }

    public function saveProposalAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $emailService = $this->get('wealthbot.mailer');

        /** @var User $ria */
        $ria = $this->getUser();

        /** @var RiaCompanyInformation $riaCompanyInformation */
        $riaCompanyInformation = $ria->getRiaCompanyInformation();

        $riaProposalForm = $this->createForm(new RiaProposalFormType($documentManager), $riaCompanyInformation);
        $riaProposalFormHandler = new RiaProposalFormHandler($riaProposalForm, $request, $em, ['email_service' => $emailService]);

        $riaProposalFormHandler->process();

        return $this->render('WealthbotRiaBundle:ChangeProfile:_proposal_form.html.twig', [
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
//    public function saveMarketingAction(Request $request)
//    {
//        /** @var \Doctrine\ORM\EntityManager $em */
//        $em = $this->get('doctrine.orm.entity_manager');
//        $user = $this->getUser();
//
//        $riaCompanyInfo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findOneBy(
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
//        return $this->render('WealthbotRiaBundle:ChangeProfile:_marketing_form.html.twig', array('form' => $marketingForm->createView()));
//    }

    public function saveBillingAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->getUser();

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

                $this->dispatchHistoryEvent($user, 'Updated billing and accounts settings.');
            }
        }

        return $this->render('WealthbotRiaBundle:ChangeProfile:_billing_n_accounts_form.html.twig', ['form' => $billingAndAccountsForm->createView(), 'show_alerts' => true]);
    }

    public function savePortfolioManagementAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');

        /** @var $subclassRepo SubclassRepository */
        $subclassRepo = $em->getRepository('WealthbotAdminBundle:Subclass');

        $user = $this->getUser();

        $riaCompanyInfo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findOneBy(
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
            new RiaCompanyInformationThreeType($em, $user, false, false, true),
            $riaCompanyInfo,
            ['session' => $session]
        );

        if ($request->isMethod('POST')) {
            $portfolioManagementForm->handleRequest($request);
            if ($portfolioManagementForm->isValid()) {
                $riaCompanyInfo = $portfolioManagementForm->getData();

                $em->persist($riaCompanyInfo);

                foreach ($riaSubclassCollection as $key => $riaSubclass) {
                    if ($riaCompanyInfo->getAccountManaged() === 1 &&
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

        return $this->render('WealthbotRiaBundle:ChangeProfile:_rebalancing_form.html.twig', [
            'form' => $portfolioManagementForm->createView(),
            'isCustomModel' => $isCustomModel,
            'company_information' => $riaCompanyInfo,
            'currentModel' => $parentModel,
            'modelType' => $isCustomModel ? 'Custom' : 'Strategy',
        ]);
    }

    public function updatePasswordAction()
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

        return $this->render('WealthbotRiaBundle:ChangeProfile:_update_password.html.twig', ['form' => $form->createView()]);
    }

//    public function uploadDocumentsAction(Request $request)
//    {
//        $em = $this->get('doctrine.orm.entity_manager');
//        $documentManager = $this->get('wealthbot_user.document_manager');
//
//        $user = $this->getUser();
//
//        $form = $this->createForm(new RiaDocumentsFormType());
//        $formHandler = new DocumentsFormHandler($form, $request, $em, $this->get('wealthbot.mailer'), array('documents_owner' => $user));
//
//        if ($request->isMethod('post')) {
//            $formHandler->process();
//        }
//
//        return $this->render('WealthbotRiaBundle:ChangeProfile:_documents_form.html.twig', array(
//            'form' => $form->createView(),
//            'documents' => $documentManager->getUserDocuments($user->getId())
//        ));
//    }

    public function saveAlertsConfigurationAction(Request $request)
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

        $alertsConfigurationForm = $this->createForm(new RiaAlertsConfigurationFormType(), $ria->getAlertsConfiguration());

        $alertsConfigurationForm->handleRequest($request);
        if ($alertsConfigurationForm->isValid()) {
            $alertsConfiguration = $alertsConfigurationForm->getData();

            $em->persist($alertsConfiguration);
            $em->flush();

            return $this->getJsonResponse(['status' => 'success']);
        }

        return $this->getJsonResponse([
            'status' => 'error',
            'content' => $this->renderView('WealthbotRiaBundle:ChangeProfile:_alerts_configuration_form_fields.html.twig', [
                'form' => $alertsConfigurationForm->createView(),
            ]),
        ]);
    }

    public function profileAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $profileForm = $this->createForm(new ProfileFormType('\Wealthbot\UserBundle\Entity\User'), $user);
        $riaAlertsConfiguration = $user->getAlertsConfiguration();
        $alertsConfigurationForm = $this->createForm(new RiaAlertsConfigurationFormType(), $riaAlertsConfiguration);

        if ($request->isMethod('post')) {
            $profileForm->handleRequest($request);
            if ($profileForm->isValid()) {
                $em->persist($profileForm->getData());
                $em->flush();
            }
        }

        return $this->render('WealthbotRiaBundle:ChangeProfile:profile.html.twig',
            [
                'profileForm' => $profileForm->createView(),
                'updatePasswordForm' => $this->get('wealthbot_user.update_password.form')->createView(),
                'alertsConfigurationForm' => $alertsConfigurationForm->createView(),
                'active_tab' => $request->get('tab', null),
            ]
        );
    }

    public function saveCustodiansAction(Request $request)
    {
        $user = $this->getUser();
        $custodianId = $request->get('custodian_id');
        $em = $this->get('doctrine.orm.entity_manager');
        /* @var \Wealthbot\AdminBundle\Repository\CustodianRepository $custodianRepo */
        $custodianRepo = $em->getRepository('WealthbotAdminBundle:Custodian');
        $companyInformation = $user->getRiaCompanyInformation();
        $custodian = $custodianRepo->find($custodianId);
        $custodians = $custodianRepo->findAll();

        $originalAdvisorCodes = [];

        $advisorCodes = $em->getRepository('WealthbotRiaBundle:AdvisorCode')
            ->findBy([
                'riaCompany' => $companyInformation,
                'custodian' => $custodian,
        ]);

        foreach ($advisorCodes as $advisorCode) {
            $originalAdvisorCodes[] = $advisorCode;
        }

        $advisorCodesCollectionFormType = new AdvisorCodesCollectionFormType($em, $companyInformation, $custodian);
        $advisorCodesCollectionFormType->setRiaCompany($companyInformation);
        $advisorCodesCollectionFormType->setCustodian($custodian);

        $advisorCodesForm = $this->createForm(
            $advisorCodesCollectionFormType,
            [
                'advisorCodes' => $advisorCodes,
            ]
        );

        $custodianForm = $this->createForm(new RiaCustodianFormType($em), $companyInformation);

        if ($request->isMethod('post')) {
            $custodianForm->handleRequest($request);
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
                $em->persist($custodianForm->getData());
                $em->flush();

                return $this->redirect($this->generateUrl('rx_ria_change_profile_custodian_tab', ['custodian_id' => $custodianId]));
            }
        }

        return $this->render('WealthbotRiaBundle:ChangeProfile:_custodians_form.html.twig',
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

    private function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
