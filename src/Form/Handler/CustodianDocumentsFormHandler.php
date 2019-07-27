<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.08.13
 * Time: 12:27
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Entity\Custodian;
use App\Entity\Document;
use App\Entity\User;
use App\Mailer\TwigSwiftMailer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class CustodianDocumentsFormHandler
{
    protected $mailer;
    protected $form;
    protected $request;
    protected $em;
    protected $custodian;

    public function __construct(Form $form, Request $request, EntityManager $em, TwigSwiftMailer $mailer, $custodian)
    {
        $this->mailer = $mailer;
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
        $this->custodian = $custodian;
    }

    protected function getExistDocuments($custodian)
    {
        $documents = [];
        foreach ($custodian->getCustodianDocuments() as $doc) {
            $documents[$doc->getType()] = $doc;
        };

        return $documents;
    }

    protected function addDocumentForOwner($custodian, Document $document)
    {
        if (!$custodian->getCustodianDocuments()->contains($document)) {
            $custodian->addCustodianDocument($document);
        }
    }

    public function success()
    {
        $custodian = $this->custodian;
        $data = $this->form->getData();

        foreach ($data as $key => $file) {
            if ($file instanceof UploadedFile) {
                $document = new Document();
                $document->setFile($file);
                $document->setType($key);
                $document->upload();
                $this->addDocumentForOwner($custodian, $document);
                $this->em->persist($document);
            }
        }

        $this->em->persist($this->custodian);

        $this->em->flush();
    }


    protected function sendEmailMessages(User $owner, $documentType)
    {
        $custodianRepo = $this->em->getRepository('App\Entity\User');

        $clients = [];
        if ($owner->hasRole('ROLE_RIA')) {
            $clients = $custodianRepo->findClientsByRiaId($owner->getId());
        }

        foreach ($clients as $client) {
            foreach ($client->getSlaveClients() as $slaveClient) {
                $this->mailer->sendClientUpdatedDocumentsEmail($slaveClient, $documentType);
            }
            $this->mailer->sendClientUpdatedDocumentsEmail($client, $documentType);
        }
    }
}
