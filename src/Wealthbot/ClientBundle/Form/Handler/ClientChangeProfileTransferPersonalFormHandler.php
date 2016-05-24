<?php

namespace Wealthbot\ClientBundle\Form\Handler;

use Wealthbot\AdminBundle\Form\Handler\AbstractFormHandler;

class ClientChangeProfileTransferPersonalFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $data = $this->form->getData();

        $this->em->persist($data->getObjectToSave());
        $this->em->flush();
    }
}
