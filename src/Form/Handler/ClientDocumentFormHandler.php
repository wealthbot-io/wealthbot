<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 30.08.13
 * Time: 14:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Entity\ClientActivitySummary;
use App\Entity\Document;
use App\Entity\User;

class ClientDocumentFormHandler extends DocumentFormHandler
{
    protected function success()
    {
        $client = $this->getOption('user');

        if (!($client instanceof User)) {
            throw new \InvalidArgumentException(sprintf('Option user must be instance of %s', get_class(new User())));
        }

        /** @var Document $data */
        $data = $this->form->getData();

        $isRiaClientView = $this->getOption('is_ria_client_view');

        $isClientNotified = $isRiaClientView && $this->form->has('is_client_notified') && $this->form->get('is_client_notified')->getData();
        $isForAllClients = $isRiaClientView && $this->form->has('is_for_all_clients') && $this->form->get('is_for_all_clients')->getData();

        $data->setType(Document::TYPE_ANY);

        if ($isRiaClientView) {
            $data->setOwner($client->getRia());
        } else {
            $data->setOwner($client);
        }

        $data->upload();

        if ($isForAllClients) {
            $riaClients = $this->em->getRepository('App\Entity\User')->findClientsByRiaId($client->getRia()->getId());
            foreach ($riaClients as $riaClient) {
                $this->setClientDocument($riaClient, $data, $isClientNotified);
            }
        } else {
            $this->setClientDocument($client, $data, $isClientNotified);
        }

        $this->em->flush();
    }

    private function setClientDocument(User $client, Document $document, $isClientNotified)
    {
        if (!$client->getUserDocuments()->contains($document)) {
            $client->addUserDocument($document);

            if ($isClientNotified) {
                $this->mailer->sendClientRiaUploadedDocument($client);
            }

            $clientActivitySummary = new ClientActivitySummary();
            $clientActivitySummary->setClient($client);
            $clientActivitySummary->setDescription('Document Uploaded');
            $clientActivitySummary->setDocument($document);

            $client->addClientActivitySummary($clientActivitySummary);

            $this->em->persist($client);
            $this->em->flush();
        }
    }
}
