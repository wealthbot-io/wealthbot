<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 17.12.12
 * Time: 13:42
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Form\Handler\AbstractFormHandler;
use App\Mailer\MailerInterface;
use App\Entity\RiaCompanyInformation;
use App\Entity\Document;
use App\Entity\User;

class RiaProposalFormHandler extends AbstractFormHandler
{
    public function success()
    {
        $emailService = $this->getOption('email_service');

        if (!($emailService instanceof MailerInterface)) {
            throw new \InvalidArgumentException(sprintf('Option email_service must be instance of Mailer\MailerInterface'));
        }

        /** @var RiaCompanyInformation $riaCompanyInformation */
        $riaCompanyInformation = $this->form->getData();

        /** @var User $ria */
        $ria = $riaCompanyInformation->getRia();

        $documents = $this->getExistDocuments($ria);

        $documentFormData = $this->form->get('documents')->getData();

        if ($documentFormData) {
            foreach ($documentFormData as $key => $file) {
                if ($file instanceof UploadedFile) {
                    if (isset($documents[$key])) {
                        $document = $documents[$key];
                    } else {
                        $document = new Document();
                        $documents[$key] = $document;
                    }

                    $document->setFile($file);
                    $document->setType($key);
                    $document->upload();

                    $this->addDocumentForOwner($ria, $document);

                    if (Document::TYPE_ADV === $key || Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT === $key) {
                        $this->sendEmailMessages($ria, $key, $emailService);
                    }
                }
            }
        }

        $this->em->persist($ria);
        $this->em->persist($riaCompanyInformation);

        $this->em->flush();
    }

    /**
     * Get exist documents for $owner.
     *
     * @param object $owner
     *
     * @return array
     */
    protected function getExistDocuments($owner)
    {
        $documents = [];
        foreach ($owner->getUserDocuments() as $doc) {
            $documents[$doc->getType()] = $doc;
        }

        return $documents;
    }

    /**
     * Add document for owner.
     *
     * @param object   $owner
     * @param Document $document
     */
    protected function addDocumentForOwner($owner, Document $document)
    {
        if (!$owner->getUserDocuments()->contains($document)) {
            $owner->addUserDocument($document);
        }
    }

    protected function sendEmailMessages(User $owner, $documentType, MailerInterface $mailer)
    {
        $userRepo = $this->em->getRepository('App\Entity\User');

        $clients = [];
        if ($owner->hasRole('ROLE_RIA')) {
            $clients = $userRepo->findClientsByRiaId($owner->getId());
        }

        foreach ($clients as $client) {
            foreach ($client->getSlaveClients() as $slaveClient) {
                $mailer->sendClientUpdatedDocumentsEmail($slaveClient, $documentType);
            }
            $mailer->sendClientUpdatedDocumentsEmail($client, $documentType);
        }
    }
}
