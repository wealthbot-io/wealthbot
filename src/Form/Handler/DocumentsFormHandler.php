<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.08.13
 * Time: 15:34
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Mailer\TwigSwiftMailer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Handler\AbstractFormHandler;
use App\Entity\Document;
use App\Entity\User;

class DocumentsFormHandler extends AbstractFormHandler
{
    protected $mailer;

    public function __construct(Form $form, Request $request, EntityManager $em, TwigSwiftMailer $mailer, $options = [])
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
                $this->em->persist($document);

                if (Document::TYPE_ADV === $key || Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT === $key) {
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
     * Add document for owner
     * @param $owner
     * @param Document $document
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function addDocumentForOwner($owner, Document $document)
    {
        if (!$owner->getUserDocuments()->contains($document)) {
            $owner->addUserDocument($document);
        }
    }

    protected function sendEmailMessages(User $owner, $documentType)
    {
        $userRepo = $this->em->getRepository('App\Entity\User');

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
