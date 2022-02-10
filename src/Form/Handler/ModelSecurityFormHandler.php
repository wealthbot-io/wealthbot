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
            ->setSubclass($subclass)
            ->setSubclassId($data['subclass_id'])
            // added the model to security assignment to fix edit and delete functionality
            ->setModel($this->getOption('selected_model'))
            ->setRiaUserId($this->getOption('ria'));

        if(isset($data['muniSubstitution'])) {
            $securityAssignment->setMuniSubstitution($data['muniSubstitution']);
        }

        $this->em->persist($securityAssignment);
        $this->em->flush();
    }
}
