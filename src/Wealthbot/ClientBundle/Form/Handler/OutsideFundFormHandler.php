<?php

namespace Wealthbot\ClientBundle\Form\Handler;

use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\AdminBundle\Exception\DataAlreadyExistsException;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\UserBundle\Entity\User;

class OutsideFundFormHandler extends AbstractOutsideFundFormHandler
{
    /**
     * @Deprecated
     * Create new securityAssignment
     *
     * @param \Wealthbot\UserBundle\Entity\User      $ria
     * @param \Wealthbot\AdminBundle\Entity\Security $security
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
     * @throws \Wealthbot\AdminBundle\Exception\DataAlreadyExistsException
     */
    protected function checkAccountAssociation(ClientAccount $account, SecurityAssignment $securityAssignment, $isPreferred)
    {
        if ($this->existAccountAssociation($account->getId(), $securityAssignment->getId())) {
            throw new DataAlreadyExistsException(sprintf(
                'SecurityAssignment with id: %s already exist for client account with id: %s',
                $securityAssignment->getId(),
                $account->getId()
            ));
        }

        $this->createAccountAssociation($account, $securityAssignment, $isPreferred);
    }
}
