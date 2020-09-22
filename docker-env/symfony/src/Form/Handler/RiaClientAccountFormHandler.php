<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.05.13
 * Time: 14:18
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\AccountGroup;
use App\Entity\ClientAccount;
use App\Entity\ClientAccountOwner;
use App\Entity\ClientAdditionalContact;
use App\Form\Handler\ClientAccountFormHandler as BaseClientAccountFormHandler;
use App\Manager\AccountDocusignManager;

class RiaClientAccountFormHandler extends BaseClientAccountFormHandler
{
    public function __construct(Form $form, Request $request, AccountDocusignManager $adm, $user)
    {
        parent::__construct($form, $request, $adm, [], true, $user, null);
    }

    /**
     * Set account_id for owners in $this->owners array.
     *
     * @param ClientAccount $account
     */
    protected function saveAccountOwners(ClientAccount $account)
    {
        $ownerTypes = $this->getOwnerTypes();

        if (empty($ownerTypes) && $account->getAccountOwners()->isEmpty()) {
            $owner = new ClientAccountOwner();
            $owner->setOwnerType(ClientAccountOwner::OWNER_TYPE_SELF);
            $owner->setClient($account->getClient());
            $owner->setAccount($account);

            $this->em->persist($owner);
            $this->em->flush();
        } else {
            foreach ($account->getAccountOwners() as $accountOwner) {
                $this->em->remove($accountOwner);
            }

            foreach ($ownerTypes as $type) {
                $this->createAccountOwnerByType($account, $type);
            }

            $this->em->persist($account);
            $this->em->flush();
        }
    }

    protected function needConsolidate()
    {
        $data = $this->form->getData();
        $consolidate = $this->form->get('consolidate')->getData();

        return $consolidate && AccountGroup::GROUP_EMPLOYER_RETIREMENT !== $data->getGroupName();
    }

    protected function consolidateAccount(ClientAccount $account)
    {
        if ($this->needConsolidate()) {
            parent::consolidateAccount($account);

            $account->setUnconsolidated(false);
        } else {
            $account->setUnconsolidated(true);
            $account->setConsolidator(null);
        }
    }

    /**
     * Create and save new client account owner.
     *
     * @param ClientAccount $account
     * @param $ownerType
     *
     * @return ClientAccountOwner
     */
    private function createAccountOwnerByType(ClientAccount $account, $ownerType)
    {
        $owner = new ClientAccountOwner();
        $owner->setOwnerType($ownerType);
        $owner->setAccount($account);

        switch ($ownerType) {
            case ClientAccountOwner::OWNER_TYPE_SELF:
                $owner->setClient($account->getClient());
                break;

            case ClientAccountOwner::OWNER_TYPE_SPOUSE:
                $owner->setContact($account->getClient()->getSpouse());
                break;

            case ClientAccountOwner::OWNER_TYPE_OTHER:
                $contactData = $this->getOtherContact();
                $existContact = $this->em->getRepository('App\Entity\ClientAdditionalContact')->findOneBy([
                    'client_id' => $account->getClientId(),
                    'first_name' => $contactData->getFirstName(),
                    'middle_name' => $contactData->getMiddleName(),
                    'last_name' => $contactData->getLastName(),
                    'relationship' => $contactData->getRelationship(),
                    'type' => ClientAccountOwner::OWNER_TYPE_OTHER,
                ]);

                if ($contactData->getId() && !$existContact) {
                    $contact = new ClientAdditionalContact();
                    $contact->setFirstName($contactData->getFirstName());
                    $contact->setMiddleName($contactData->getMiddleName());
                    $contact->setLastName($contactData->getLastName());
                    $contact->setRelationship($contactData->getRelationship());
                    $contact->setType(ClientAccountOwner::OWNER_TYPE_OTHER);
                } elseif (!$contactData->getId() && $existContact) {
                    $contact = $existContact;
                } else {
                    $contact = $contactData;
                }

                $contact->setClient($account->getClient());
                $owner->setContact($contact);

                $this->em->persist($contact);
                $this->em->persist($owner);

                break;
        }

        $account->addAccountOwner($owner);

        $this->em->persist($owner);
        $this->em->flush();

        return $owner;
    }

    /**
     * Get form owner_types data.
     *
     * @return array
     */
    private function getOwnerTypes()
    {
        $ownerTypes = [];

        if ($this->form->has('owners') && $this->form->get('owners')->has('owner_types')) {
            $data = $this->form->get('owners')->get('owner_types')->getData();

            if (is_array($data)) {
                $ownerTypes = $data;
            } else {
                $ownerTypes[] = $data;
            }
        }

        return $ownerTypes;
    }

    /**
     * Get form other_contact data.
     *
     * @return ClientAdditionalContact|null
     */
    private function getOtherContact()
    {
        $contact = null;

        if ($this->form->has('owners') && $this->form->get('owners')->has('other_contact')) {
            $contact = $this->form->get('owners')->get('other_contact')->getData();
        }

        return $contact;
    }
}
