<?php

namespace Wealthbot\RiaBundle\Form\Handler;

use Wealthbot\AdminBundle\Entity\SecurityAssignment;
use Wealthbot\ClientBundle\Entity\AccountOutsideFund;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Form\Handler\AbstractOutsideFundFormHandler;

class OutsideFundAssociationFormHandler extends AbstractOutsideFundFormHandler
{
    /**
     * Check if account outside fund exist and create new if it does not exist.
     *
     * @param ClientAccount      $account
     * @param SecurityAssignment $securityAssignment
     * @param $isPreferred
     *
     * @return mixed|null|AccountOutsideFund
     */
    protected function checkAccountAssociation(ClientAccount $account, SecurityAssignment $securityAssignment, $isPreferred)
    {
        $association = $this->existAccountAssociation($account->getId(), $securityAssignment->getId());

        if (!$association) {
            $association = $this->createAccountAssociation($account, $securityAssignment, $isPreferred);
        } else {
            $this->updateAccountAssociation($association, $isPreferred);
        }

        return $association;
    }

    protected function updateAccountAssociation(AccountOutsideFund $association, $isPreferred)
    {
        $association->setIsPreferred($isPreferred);
        $this->em->persist($association);
        $this->em->flush();
    }
}
