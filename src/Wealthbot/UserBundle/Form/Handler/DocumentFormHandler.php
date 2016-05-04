<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 30.08.13
 * Time: 14:05
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\UserBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\AdminBundle\Form\Handler\AbstractFormHandler;
use Wealthbot\MailerBundle\Mailer\MailerInterface;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\User;

class DocumentFormHandler extends AbstractFormHandler
{
    protected $mailer;

    public function __construct(Form $form, Request $request, EntityManager $em, MailerInterface $mailer, $options = [])
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

        if ($document->getType() === Document::TYPE_ADV || $document->getType() === Document::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT) {
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
        $userRepo = $this->em->getRepository('WealthbotUserBundle:User');

        $clients = $userRepo->findAllClients();

        foreach ($clients as $client) {
            foreach ($client->getSlaveClients() as $slaveClient) {
                $this->mailer->sendClientUpdatedDocumentsEmail($slaveClient, $documentType);
            }

            $this->mailer->sendClientUpdatedDocumentsEmail($client, $documentType);
        }
    }
}
