<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\CustodianMessage;
use App\Form\Type\CustodianMessageFormType;
use App\Form\Handler\CustodianDocumentsFormHandler;
use App\Form\Type\CustodianDocumentsFormType;

class CustodianController extends AclController
{
    public function index()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $custodians = $em->getRepository('App\Entity\Custodian')->findAll();
        $tabs = [];

        foreach ($custodians as $custodian) {
            $custodianId = $custodian->getId();

            $tabs[$custodianId]['custodian'] = $custodian;
            $tabs[$custodianId]['form'] = $this->createForm(CustodianDocumentsFormType::class, null, [ 'custodian' => $custodian])->createView();
            $tabs[$custodianId]['documents'] = $documentManager->getCustodianDocuments($custodianId);
        }

        return $this->render('/Admin/Custodian/index.html.twig', [
            'tabs' => $tabs,
        ]);
    }

    public function uploadDocuments($id, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $documentManager = $this->get('wealthbot_user.document_manager');

        $custodian = $em->getRepository('App\Entity\Custodian')->find($id);
        if (!$custodian) {
            throw $this->createNotFoundException(sprintf('Custodian with id: %s does not exist.', $id));
        }

        $form = $this->createForm(CustodianDocumentsFormType::class, null, ['custodian' => $custodian ]);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $formHandler = new CustodianDocumentsFormHandler($form, $request, $em, $this->get('wealthbot.mailer'), $custodian);
                $formHandler->success();
            }
        }

        return $this->render('/Admin/Custodian/_documents_form.html.twig', [
            'form' => $form->createView(),
            'custodian' => $custodian,
            'documents' => $documentManager->getCustodianDocuments($custodian->getId()),
        ]);
    }

    public function message($id, $type, Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        $custodian = $em->getRepository('App\Entity\Custodian')->find($id);
        if (!$custodian) {
            return $this->json(
                ['status' => 'error', 'message' => sprintf('Custodian with id: %s does not exist.', $id)]
            );
        }

        $message = $em->getRepository('App\Entity\CustodianMessage')->findOneBy([
            'custodian_id' => $custodian->getId(),
            'type' => $type,
        ]);
        if (!$message) {
            $message = new CustodianMessage();
            $message->setType($type);
            $message->setCustodian($custodian);
        }

        $status = 'success';
        $form = $this->createForm(CustodianMessageFormType::class, $message);

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

        return $this->json([
            'status' => $status,
            'content' => $this->renderView(
                '/Admin/Custodian/_message_form.html.twig',
                ['form' => $form->createView()]
            ),
        ]);
    }
}
