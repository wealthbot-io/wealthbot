<?php

namespace App\Form\Handler;

use App\Entity\Security;
use App\Entity\SecurityAssignment;

class ModelSecurityFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        /** @var $securityAssignment SecurityAssignment */
        $securityAssignment = $this->getOption('security_assignment');

        /** @var $security Security */
        $security = $this->em->getRepository('App\Entity\Security')->find($securityAssignment->getSecurityId());
        $securityAssignment->setSecurity($security);

        $this->em->persist($securityAssignment);
        $this->em->flush();
    }
}
