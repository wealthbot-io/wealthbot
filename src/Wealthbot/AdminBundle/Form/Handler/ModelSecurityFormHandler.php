<?php

namespace Wealthbot\AdminBundle\Form\Handler;

use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;

class ModelSecurityFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        /** @var $securityAssignment SecurityAssignment */
        $securityAssignment = $this->getOption('security_assignment');

        /** @var $security Security */
        $security = $this->em->getRepository('WealthbotAdminBundle:Security')->find($securityAssignment->getSecurityId());
        $securityAssignment->setSecurity($security);

        $this->em->persist($securityAssignment);
        $this->em->flush();
    }
}
