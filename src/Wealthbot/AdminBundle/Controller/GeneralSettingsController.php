<?php

namespace Wealthbot\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\AdminBundle\Entity\Fee;
use Wealthbot\AdminBundle\Form\Type\AdminFeesType;
use Wealthbot\AdminBundle\Form\Type\FeeFormType;
use Wealthbot\AdminBundle\Form\Type\SubclassType;
use Wealthbot\AdminBundle\Model\Acl;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\Form\Handler\DocumentFormHandler;
use Wealthbot\UserBundle\Form\Type\DocumentFormType;

// Todo: This controller must be refactored for BillingSpecs.
class GeneralSettingsController extends AclController
{
    public function indexAction()
    {
        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        //Todo: Check if there can be several billingSpecs...
        $billingSpec = $em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['owner' => null]);
        if ($billingSpec) {
            $fees = $billingSpec->getFees()->getValues();
        } else {
            $fees = [];
        }

        $form = $this->createForm(new AdminFeesType($superAdmin));
        $form->get('fees')->setData($fees);

        $documents = $documentManager->getUserDocuments($superAdmin->getId());
        $documentForm = $this->createForm(new DocumentFormType());

        $documentTypes = [];
        foreach (Document::getAdminTypeChoices() as $key) {
            $type = str_replace('_', ' ', $key);
            if ($type === 'adv') {
                $type = 'ADV';
            }
            $documentTypes[$key] = ucwords($type);
        }

        return $this->render('WealthbotAdminBundle:GeneralSettings:index.html.twig', [
            'fees' => $fees,
            'form' => $form->createView(),
            'document_form' => $documentForm->createView(),
            'documents' => $documents,
            'document_types' => $documentTypes,
        ]);
    }

    public function updateFeesAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em  */
        /* @var $superAdmin User */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        /** @var BillingSpec $billingSpec */
        $billingSpec = $em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['owner' => null]);
        if ($billingSpec) {
            $fees = $billingSpec->getFees()->getValues();
        } else {
            $fees = [];
        }

        $form = $this->createForm(new AdminFeesType($superAdmin));
        $form->get('fees')->setData($fees);

        if ($request->getMethod() === 'POST') {
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
                    $formNew = $this->createForm(new AdminFeesType($superAdmin));
                    $formNew->get('fees')->setData($form['fees']->getData());

                    return $this->getJsonResponse([
                        'status' => 'success',
                        'content' => $this->renderView('WealthbotAdminBundle:GeneralSettings:_fees_form.html.twig', ['form' => $formNew->createView()]),
                    ]);
                } else {
                    return $this->redirect($this->generateUrl('rx_ria_profile_step_four'));
                }
            } elseif ($request->isXmlHttpRequest()) {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'content' => $this->renderView('WealthbotAdminBundle:GeneralSettings:_fees_form.html.twig', ['form' => $form->createView()]),
                ]);
            }
        } elseif ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse(['status' => 'success']);
        }

        return $this->redirect($this->generateUrl('rx_admin_general_settings'));
    }

    public function newFeeAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em  */
        /* @var $superAdmin User */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        $form = $this->createForm(new FeeFormType($superAdmin));

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $fee = $form->getData();

                $em->persist($fee);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_general_settings'));
            }
        }

        return $this->render('WealthbotAdminBundle:GeneralSettings:fee_new.html.twig', ['form' => $form->createView()]);
    }

    public function editFeeAction($id, Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em  */
        /* @var $superAdmin User */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();

        $fee = $em->getRepository('WealthbotAdminBundle:Fee')->find($id);
        $form = $this->createForm(new FeeFormType($superAdmin), $fee);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $fee = $form->getData();

                $em->persist($fee);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_general_settings'));
            }
        }

        return $this->render('WealthbotAdminBundle:GeneralSettings:fee_edit.html.twig', ['form' => $form->createView()]);
    }

    public function deleteFeeAction($id)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->container->get('doctrine.orm.entity_manager');
        /** @var Fee $fee */
        $fee = $em->getRepository('WealthbotAdminBundle:Fee')->find($id);

        if ($fee) {
            $fee->getBillingSpec()->removeFee($fee);
            $em->remove($fee);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('rx_admin_general_settings'));
    }

    public function uploadDocumentsAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        /** @var User $superAdmin */
        $superAdmin = $this->get('wealthbot.manager.user')->getAdmin();
        $form = $this->createForm(new DocumentFormType());
        $formHandler = new DocumentFormHandler($form, $request, $em, $this->get('wealthbot.mailer'), ['user' => $superAdmin]);

        if ($request->isMethod('post')) {
            $process = $formHandler->process();

            if (!$process) {
                return $this->getJsonResponse([
                    'status' => 'error',
                    'content' => $this->renderView('WealthbotAdminBundle:GeneralSettings:_document_form.html.twig', [
                        'form' => $form->createView(),
                    ]),
                ]);
            }
        }

        $documentTypes = [];
        foreach (Document::getAdminTypeChoices() as $key) {
            $type = str_replace('_', ' ', $key);
            if ($type === 'adv') {
                $type = 'ADV';
            }
            $documentTypes[$key] = ucwords($type);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse([
                'status' => 'success',
                'content' => $this->renderView('WealthbotAdminBundle:GeneralSettings:_documents.html.twig', [
                    'form' => $form->createView(),
                    'documents' => $documentManager->getUserDocuments($superAdmin->getId()),
                    'types' => $documentTypes,
                ]),
            ]);
        }

        return $this->render('WealthbotAdminBundle:GeneralSettings:_documents.html.twig', [
            'form' => $form->createView(),
            'documents' => $documentManager->getUserDocuments($superAdmin->getId()),
            'types' => $documentTypes,
        ]);
    }

    public function newSubclassAction(Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $form = $this->createForm(new SubclassType());

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $subclass = $form->getData();

                $em->persist($subclass);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_general_settings'));
            }
        }

        return $this->render('WealthbotAdminBundle:GeneralSettings:subclass_new.html.twig', ['form' => $form->createView()]);
    }

    public function editSubclassAction($id, Request $request)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $subclass = $em->getRepository('WealthbotAdminBundle:Subclass')->find($id);

        $form = $this->createForm(new SubclassType(), $subclass);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $subclass = $form->getData();

                $em->persist($subclass);
                $em->flush();

                return $this->redirect($this->generateUrl('rx_admin_general_settings'));
            }
        }

        return $this->render('WealthbotAdminBundle:GeneralSettings:subclass_edit.html.twig', ['form' => $form->createView()]);
    }

    public function deleteSubclassAction($id)
    {
        $this->checkAccess(Acl::PERMISSION_EDIT);

        /** @var \Doctrine\ORM\EntityManager $em  */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $subclass = $em->getRepository('WealthbotAdminBundle:Subclass')->find($id);

        if ($subclass) {
            $em->remove($subclass);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('rx_admin_general_settings'));
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
