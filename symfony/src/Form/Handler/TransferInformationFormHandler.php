<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 11.02.14
 * Time: 17:20.
 */

namespace App\Form\Handler;

use App\Form\Handler\AbstractFormHandler;
use App\Entity\TransferInformation;
use App\Entity\Document;
use App\Entity\User;

class TransferInformationFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $client = $this->getOption('client');
        if (!($client instanceof User) || !$client->hasRole('ROLE_CLIENT')) {
            throw new \InvalidArgumentException(sprintf(
                'Option client must be instance of %s and have role ROLE_CLIENT',
                get_class(new User())
            ));
        }

        /** @var TransferInformation $data */
        $data = $this->form->getData();

        $statement = $data->getStatementDocument();
        if ($statement) {
            $statement->setType(Document::TYPE_ACCOUNT_TRANSFER_STATEMENT);

            if (!$statement->getId()) {
                $statement->addUser($client);
                $client->addUserDocument($statement);
            }

            if (!file_exists($statement->getAbsolutePath())) {
                $statement->upload();
            }
        }

        $this->em->persist($data);
        $this->em->flush();
    }
}
