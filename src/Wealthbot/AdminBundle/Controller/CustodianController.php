<?php

namespace Wealthbot\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\AdminBundle\Entity\CustodianMessage;
use Wealthbot\AdminBundle\Form\Type\CustodianMessageFormType;
use Wealthbot\UserBundle\Form\Handler\CustodianDocumentsFormHandler;
use Wealthbot\UserBundle\Form\Type\CustodianDocumentsFormType;

class CustodianController extends AclController
{
    public function indexAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $custodians = $em->getRepository('WealthbotAdminBundle:Custodian')->findAll();
        $tabs = [];

        foreach ($custodians as $custodian) {
            $custodianId = $custodian->getId();

            $tabs[$custodianId]['custodian'] = $custodian;
            $tabs[$custodianId]['form'] = $this->createForm(new CustodianDocumentsFormType($custodian))->createView();
            $tabs[$custodianId]['documents'] = $documentManager->getCustodianDocuments($custodianId);
        }

        return $this->render('WealthbotAdminBundle:Custodian:index.html.twig', [
            'tabs' => $tabs,
        ]);
    }

    public function uploadDocumentsAction($id, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $custodian = $em->getRepository('WealthbotAdminBundle:Custodian')->find($id);
        if (!$custodian) {
            throw $this->createNotFoundException(sprintf('Custodian with id: %s does not exist.', $id));
        }

        $form = $this->createForm(new CustodianDocumentsFormType($custodian));
        $formHandler = new CustodianDocumentsFormHandler($form, $request, $em, $this->get('wealthbot.mailer'), ['documents_owner' => $custodian]);

        if ($request->isMethod('post')) {
            $formHandler->process();
        }

        return $this->render('WealthbotAdminBundle:Custodian:_documents_form.html.twig', [
            'form' => $form->createView(),
            'custodian' => $custodian,
            'documents' => $documentManager->getCustodianDocuments($custodian->getId()),
        ]);
    }

    public function messageAction($id, $type, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $custodian = $em->getRepository('WealthbotAdminBundle:Custodian')->find($id);
        if (!$custodian) {
            return $this->getJsonResponse(
                ['status' => 'error', 'message' => sprintf('Custodian with id: %s does not exist.', $id)]
            );
        }

        $message = $em->getRepository('WealthbotAdminBundle:CustodianMessage')->findOneBy([
            'custodian_id' => $custodian->getId(),
            'type' => $type,
        ]);
        if (!$message) {
            $message = new CustodianMessage();
            $message->setType($type);
            $message->setCustodian($custodian);
        }

        $status = 'success';
        $form = $this->createForm(new CustodianMessageFormType(), $message);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $message = $form->getData();

                $em->persist($message);
                $em->flush();
            } else {
                $status = 'error';
            }
        }

        return $this->getJsonResponse([
            'status' => $status,
            'content' => $this->renderView(
                'WealthbotAdminBundle:Custodian:_message_form.html.twig',
                ['form' => $form->createView()]
            ),
        ]);
    }

    private function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
