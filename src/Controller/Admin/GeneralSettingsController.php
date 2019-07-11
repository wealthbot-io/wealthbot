<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\BillingSpec;
use App\Entity\Fee;
use App\Form\Type\AdminFeesType;
use App\Form\Type\FeeFormType;
use App\Form\Type\SubclassType;
use App\Model\Acl;
use App\Entity\Document;
use App\Entity\User;
use App\Form\Handler\DocumentFormHandler;
use App\Form\Type\DocumentFormType;

// Todo: This controller must be refactored for BillingSpecs.
class GeneralSettingsController extends AclController
{
    public function index()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        //Todo: Check if there can be several billingSpecs...
        $billingSpec = $em->getRepository('App\Entity\BillingSpec')->findOneBy(['owner' => null]);
        if ($billingSpec) {
            $fees = $billingSpec->getFees()->getValues();
        } else {
            $fees = [];
        }

        $form = $this->createForm(AdminFeesType::class, $superAdmin);
        $form->get('fees')->setData($fees);

        $documents = $documentManager->getUserDocuments($superAdmin->getId());
        $documentForm = $this->createForm(DocumentFormType::class);

        $documentTypes = [];
        foreach (Document::getAdminTypeChoices() as $key) {
            $type = str_replace('_', ' ', $key);
            if ('adv' === $type) {
                $type = 'ADV';
            }
            $documentTypes[$key] = ucwords($type);
        }

        return $this->render('/Admin/GeneralSettings/index.html.twig', [
            'fees' => $fees,
            'form' => $form->createView(),
            'document_form' => $documentForm->createView(),
            'documents' => $documents,
            'document_types' => $documentTypes,
        ]);
    }

    public function updateFees(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $superAdmin User */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        /** @var BillingSpec $billingSpec */
        $billingSpec = $em->getRepository('App\Entity\BillingSpec')->findOneBy(['owner' => null]);
        if ($billingSpec) {
            $fees = $billingSpec->getFees()->getValues();
        } else {
            $fees = [];
        }

        $form = $this->createForm(AdminFeesType::class, $superAdmin);
        $form->get('fees')->setData($fees);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                foreach ($fees as $riaFee) {
                    $billingSpec->removeFee($riaFee);
                    $em->remove($riaFee);
                }
                $em->flush();

                $data = $form['fees']->getData();
                foreach ($data as $fee) {
                    $em->persist($fee);
                    $billingSpec->addFee($fee);
                }

                $em->flush();

                if ($request->isXmlHttpRequest()) {
                    $formNew = $this->createForm(AdminFeesType::class, $superAdmin);
                    $formNew->get('fees')->setData($form['fees']->getData());

                    return $this->json([
                        'status' => 'success',
                        'content' => $this->renderView('/Admin/GeneralSettings/_fees_form.html.twig', ['form' => $formNew->createView()]),
                    ]);
                } else {
                    return $this->redirect($this->generateUrl('rx_ria_profile_step_four'));
                }
            } elseif ($request->isXmlHttpRequest()) {
                return $this->json([
                    'status' => 'error',
                    'content' => $this->renderView('/Admin/GeneralSettings/_fees_form.html.twig', ['form' => $form->createView()]),
                ]);
            }
        } elseif ($request->isXmlHttpRequest()) {
            return $this->json(['status' => 'success']);
        }

        return $this->redirect($this->generateUrl('rx_admin_general_settings'));
    }

    public function newFee(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $superAdmin User */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        $form = $this->createForm(FeeFormType::class, null, [
            'user' => $superAdmin,
        ]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $fee = $form->getData();

                $em->persist($fee);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_general_settings'));
            }
        }

        return $this->render('/Admin/GeneralSettings/fee_new.html.twig', ['form' => $form->createView()]);
    }

    public function editFee($id, Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        /* @var $superAdmin User */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        $fee = $em->getRepository('App\Entity\Fee')->find($id);
        $form = $this->createForm(FeeFormType::class, $fee, [
            'user' => $superAdmin,
        ]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $fee = $form->getData();

                $em->persist($fee);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_general_settings'));
            }
        }

        return $this->render('/Admin/GeneralSettings/fee_edit.html.twig', ['form' => $form->createView()]);
    }

    public function deleteFee($id)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var Fee $fee */
        $fee = $em->getRepository('App\Entity\Fee')->find($id);

        if ($fee) {
            $fee->getBillingSpec()->removeFee($fee);
            $em->remove($fee);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('rx_admin_general_settings'));
    }

    public function uploadDocuments(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        /** @var User $superAdmin */
        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();
        $form = $this->createForm(DocumentFormType::class);
        $formHandler = new DocumentFormHandler($form, $request, $em, $this->get('wealthbot.mailer'), ['user' => $superAdmin]);

        if ($request->isMethod('post')) {
            $process = $formHandler->process();

            if (!$process) {
                return $this->json([
                    'status' => 'error',
                    'content' => $this->renderView('/Admin/GeneralSettings/_document_form.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ]);
            }
        }

        $documentTypes = [];
        foreach (Document::getAdminTypeChoices() as $key) {
            $type = str_replace('_', ' ', $key);
            if ('adv' === $type) {
                $type = 'ADV';
            }
            $documentTypes[$key] = ucwords($type);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'content' => $this->renderView('/Admin/GeneralSettings/_documents.html.twig', [
                    'form' => $form->createView(),
                    'documents' => $documentManager->getUserDocuments($superAdmin->getId()),
                    'types' => $documentTypes,
                ]),
            ]);
        }

        return $this->render('/Admin/GeneralSettings/_documents.html.twig', [
            'form' => $form->createView(),
            'documents' => $documentManager->getUserDocuments($superAdmin->getId()),
            'types' => $documentTypes,
        ]);
    }

    public function newSubclass(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $form = $this->createForm(SubclassType::class);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $subclass = $form->getData();

                $em->persist($subclass);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_general_settings'));
            }
        }

        return $this->render('/Admin/GeneralSettings/subclass_new.html.twig', ['form' => $form->createView()]);
    }

    public function editSubclass($id, Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $subclass = $em->getRepository('App\Entity\Subclass')->find($id);

        $form = $this->createForm(SubclassType::class, $subclass);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $subclass = $form->getData();

                $em->persist($subclass);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_general_settings'));
            }
        }

        return $this->render('/Admin/GeneralSettings/subclass_edit.html.twig', ['form' => $form->createView()]);
    }

    public function deleteSubclass($id)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $subclass = $em->getRepository('App\Entity\Subclass')->find($id);

        if ($subclass) {
            $em->remove($subclass);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('rx_admin_general_settings'));
    }
}
