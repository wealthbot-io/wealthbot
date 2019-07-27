<?php

namespace App\Form\Handler;

use App\Entity\Security;
use App\Entity\SecurityAssignment;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

class ModelSecurityFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $securityAssignment = $this->form->getData();
        /** @var $security Security */
        $security = $this->em->getRepository('App\Entity\Security')->find($securityAssignment->getSecurityId());
        $securityAssignment->setSecurity($security);
        $data = $this->request->get('model_security_form');

        $subclass = $this->em->getRepository('App\Entity\Subclass')->find($data['subclass_id']);

        $securityAssignment
            ->setModelId($this->getOption('selected_model')->getId())
            ->setMuniSubstitution($data['muniSubstitution'])
            ->setSubclass($subclass)
            ->setSubclassId($data['subclass_id'])
            ->setRiaUserId($this->getOption('ria'));

        $this->em->persist($securityAssignment);
        $this->em->flush();
    }
}
