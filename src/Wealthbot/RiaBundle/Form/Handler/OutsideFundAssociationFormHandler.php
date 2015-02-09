<?php
namespace Wealthbot\RiaBundle\Form\Handler;

use Wealthbot\ClientBundle\Entity\AccountOutsideFund;
use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\ClientBundle\Form\Handler\AbstractOutsideFundFormHandler;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Wealthbot\ClientBundle\Entity\ClientAccount;

use Wealthbot\UserBundle\Entity\User;
use Wealthbot\AdminBundle\Entity\SecurityAssignment;

class OutsideFundAssociationFormHandler extends AbstractOutsideFundFormHandler
{
    /**
     * Check if account outside fund exist and create new if it does not exist
     *
     * @param ClientAccount $account
     * @param SecurityAssignment $securityAssignment
     * @param $isPreferred
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