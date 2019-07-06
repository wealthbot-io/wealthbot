<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 30.08.13
 * Time: 14:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Mailer\TwigSwiftMailer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Handler\AbstractFormHandler;
use App\Entity\Document;
use App\Entity\User;

class DocumentFormHandler extends AbstractFormHandler
{
    protected $mailer;

    public function __construct(Form $form, Request $request, EntityManager $em, TwigSwiftMailer $mailer, $options = [])
    {
        parent::__construct($form, $request, $em, $options);

        $this->mailer = $mailer;
    }

    protected function success()
    {
        $user = $this->getOption('user');
        if (!($user instanceof User)) {
            throw new \InvalidArgumentException(sprintf('Option user must be instance of %s', get_class(new User())));
        }

        /** @var Document $data */
        $data = $this->form->getData();
        $existDocuments = $this->getExistDocuments($user);

        if (!isset($existDocuments[$data->getType()])) {
            $document = $data;
        } else {
            /** @var Document $document */
            $document = $existDocuments[$data->getType()];

            $document->setFile($data->getFile());
            $document->setOriginalName($data->getOriginalName());
        }

        $document->setOwner($user);
        $document->upload();

        if (!$user->getUserDocuments()->contains($document)) {
            $user->addUserDocument($document);
        }

        $this->em->persist($user);
        $this->em->flush();

        if (Document::TYPE_ADV === $document->getType() || Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT === $document->getType()) {
            $this->sendEmailMessages($document->getType());
        }
    }

    private function getExistDocuments(User $user)
    {
        $documents = [];
        foreach ($user->getUserDocuments() as $document) {
            $documents[$document->getType()] = $document;
        }

        return $documents;
    }

    private function sendEmailMessages($documentType)
    {
        $userRepo = $this->em->getRepository('App\Entity\User');

        $clients = $userRepo->findAllClients();

        foreach ($clients as $client) {
            foreach ($client->getSlaveClients() as $slaveClient) {
                $this->mailer->sendClientUpdatedDocumentsEmail($slaveClient, $documentType);
            }

            $this->mailer->sendClientUpdatedDocumentsEmail($client, $documentType);
        }
    }
}
