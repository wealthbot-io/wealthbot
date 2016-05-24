<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.08.13
 * Time: 15:34
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\UserBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\AdminBundle\Form\Handler\AbstractFormHandler;
use Wealthbot\MailerBundle\Mailer\MailerInterface;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\User;

class DocumentsFormHandler extends AbstractFormHandler
{
    protected $mailer;

    public function __construct(Form $form, Request $request, EntityManager $em, MailerInterface $mailer, $options = [])
    {
        parent::__construct($form, $request, $em, $options);

        $this->mailer = $mailer;
    }

    protected function success()
    {
        /** @var User $owner */
        $owner = $this->getDocumentsOwner();
        $documents = $this->getExistDocuments($owner);
        $data = $this->form->getData();

        foreach ($data as $key => $file) {
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

                $this->addDocumentForOwner($owner, $document);

                if ($key === Document::TYPE_ADV || $key === Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT) {
                    $this->sendEmailMessages($owner, $key);
                }
            }
        }

        $this->em->persist($owner);
        $this->em->flush();
    }

    /**
     * Get documents owner.
     *
     * @return object
     *
     * @throws \InvalidArgumentException
     */
    protected function getDocumentsOwner()
    {
        $owner = $this->getOption('documents_owner');
        if (!($owner instanceof User)) {
            throw new \InvalidArgumentException(sprintf('Option documents_owner must be instance of %s', get_class(new User())));
        }

        return $owner;
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

    protected function sendEmailMessages(User $owner, $documentType)
    {
        $userRepo = $this->em->getRepository('WealthbotUserBundle:User');

        $clients = [];
        if ($owner->hasRole('ROLE_RIA')) {
            $clients = $userRepo->findClientsByRiaId($owner->getId());
        }

        foreach ($clients as $client) {
            foreach ($client->getSlaveClients() as $slaveClient) {
                $this->mailer->sendClientUpdatedDocumentsEmail($slaveClient, $documentType);
            }
            $this->mailer->sendClientUpdatedDocumentsEmail($client, $documentType);
        }
    }
}
