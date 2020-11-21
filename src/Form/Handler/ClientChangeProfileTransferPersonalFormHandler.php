<?php

namespace App\Form\Handler;

use App\Form\Handler\AbstractFormHandler;

class ClientChangeProfileTransferPersonalFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $data = $this->form->getData();

        $this->em->persist($data->getObjectToSave());
        $this->em->flush();
    }
}
