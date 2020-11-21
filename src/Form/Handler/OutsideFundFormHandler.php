<?php

namespace App\Form\Handler;

use App\Entity\Security;
use App\Entity\SecurityAssignment;
use Exception\DataAlreadyExistsException;
use App\Entity\ClientAccount;
use App\Entity\User;

class OutsideFundFormHandler extends AbstractOutsideFundFormHandler
{
    /**
     * @Deprecated
     * Create new securityAssignment
     *
     * @param \App\Entity\User      $ria
     * @param \App\Entity\Security $security
     *
     * @return SecurityAssignment
     */
    protected function createSecurity(User $ria, Security $security)
    {
        $securityAssignment = new SecurityAssignment();

//        $securityAssignment->setRia($ria); Deprecated
        $securityAssignment->setSecurity($security);
        $securityAssignment->setIsPreferred(false);

        $this->em->persist($securityAssignment);
        $this->em->flush();

        return $securityAssignment;
    }

    /**
     * Update securityAssignment object. Return updated object.
     *
     * @param SecurityAssignment $securityAssignment
     * @param Security           $security
     *
     * @return SecurityAssignment
     */
    protected function updateSecurity(SecurityAssignment $securityAssignment, Security $security = null)
    {
        return $securityAssignment;
    }

    /**
     * Check account outside fund and create new if does not exist.
     *
     * @param ClientAccount      $account
     * @param SecurityAssignment $securityAssignment
     * @param bool               $isPreferred
     *
     * @return mixed|void
     *
     * @throws \Exception
     */
    protected function checkAccountAssociation(ClientAccount $account, SecurityAssignment $securityAssignment, $isPreferred)
    {
        if ($this->existAccountAssociation($account->getId(), $securityAssignment->getId())) {
            throw new \Exception(sprintf(
                'SecurityAssignment with id: %s already exist for client account with id: %s',
                $securityAssignment->getId(),
                $account->getId()
            ));
        }

        $this->createAccountAssociation($account, $securityAssignment, $isPreferred);
    }
}
