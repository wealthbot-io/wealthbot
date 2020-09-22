<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 08.05.14
 * Time: 16:14.
 */

namespace App\Form\Handler;

use App\Form\Handler\AbstractFormHandler;
use App\Entity\BankInformation;
use App\Entity\Document;
use App\Entity\User;

class BankInformationFormHandler extends AbstractFormHandler
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

        /** @var BankInformation $data */
        $data = $this->form->getData();
        $data->setClient($client);

        $pdf = $data->getPdfDocument();
        if ($pdf) {
            $pdf->setType(Document::TYPE_BANK_PDF_COPY);

            if (!$pdf->getId()) {
                $pdf->addUser($client);
                $client->addUserDocument($pdf);
            }

            if (!file_exists($pdf->getAbsolutePath())) {
                $pdf->upload();
            }
        }

        $this->em->persist($data);
        $this->em->flush();
    }
}
