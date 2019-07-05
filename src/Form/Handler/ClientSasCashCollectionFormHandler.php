<?php

namespace App\Form\Handler;

use App\Form\Handler\AbstractFormHandler;

class ClientSasCashCollectionFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $data = $this->form->getData();
        foreach ($data['sas_cash_collection'] as $id => $sasCash) {
            $clientAccount = $this->em->getRepository('App\Entity\ClientAccount')->find($id);
            $clientAccount->setSasCash($sasCash);
            $this->em->persist($clientAccount);
        }

        $this->em->flush();
    }
}
