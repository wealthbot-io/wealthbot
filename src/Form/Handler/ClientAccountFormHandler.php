<?php

namespace App\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use App\Docusign\TransferInformationCustodianCondition;
use App\Entity\AccountGroup;
use App\Entity\ClientAccount;
use App\Entity\ClientAccountOwner;
use App\Repository\ClientAccountRepository;
use App\Manager\AccountDocusignManager;

class ClientAccountFormHandler
{
    protected $form;
    protected $request;
    protected $adm;
    protected $em;
    private $owners;
    private $consolidate;
    private $user;
    private $groupType;

    public function __construct(Form $form, Request $request, AccountDocusignManager $adm, array $owners = [], $consolidate = true, $user, $groupType)
    {
        $this->form = $form;
        $this->request = $request;
        $this->adm = $adm;
        $this->em = $adm->getObjectManager();
        $this->owners = $owners;
        $this->consolidate = $consolidate;
        $this->user = $user;
        $this->groupType = $groupType;
    }

    public function process()
    {
        if ('POST' === $this->request->getMethod()) {
            $this->form->handleRequest($this->request);

            if ($this->form->isSubmitted()  && $this->form->isValid()) {
                /** @var ClientAccount $data */
                $data = $this->form->getData();

                // If account is transfer account then save transfer information
                // and check ability to use electronically signing
                if ($this->form->has('transferInformation')) {
                    $transferInformation = $this->saveTransferInformation($data);

                    $isAllowed = $this->adm->isDocusignAllowed($transferInformation, [
                        new TransferInformationCustodianCondition(),
                    ]);

                    $this->adm->setIsUsedDocusign($data, $isAllowed);
                } else {
                    $this->adm->setIsUsedDocusign($data, true);
                };

                $data->setClient($this->user);
                $data->setClientId($this->user->getId());
                if ($this->groupType) {
                    $data->setGroupType($this->groupType);
                };

                $this->em->persist($data);


                $this->saveAccountOwners($data);
                $this->consolidateAccount($data);

                $this->em->persist($data);
                $this->em->flush();

                return true;
            }
        }

        return false;
    }

    /**
     * Set account_id for owners in $this->owners array.
     *
     * @param ClientAccount $account
     */
    protected function saveAccountOwners(ClientAccount $account)
    {
        if (empty($this->owners) && $account->getAccountOwners()->isEmpty()) {
            $owner = new ClientAccountOwner();
            $owner->setOwnerType(ClientAccountOwner::OWNER_TYPE_SELF);
            $owner->setClient($account->getClient());
            $owner->setOwnerClientId($account->getClientId());
            $owner->setAccount($account);

            $account->addAccountOwner($owner);

            $this->em->persist($owner);
        } else {
            $repo = $this->em->getRepository('App\Entity\ClientAccountOwner');

            foreach ($this->owners as $ownerItem) {
                $owner = new ClientAccountOwner();
                $owner->setOwnerType($ownerItem['owner_type']);
                $owner->setAccount($account);

                if (ClientAccountOwner::OWNER_TYPE_SELF === $ownerItem['owner_type']) {
                    $client = $this->em->getRepository('App\Entity\User')->find($ownerItem['owner_client_id']);
                    if ($client) {
                        $exist = $repo->findOneBy([
                            'owner_type' => $ownerItem['owner_type'],
                            'owner_client_id' => $client->getId(),
                            'account_id' => $account->getId(),
                        ]);
                        if (!$exist) {
                            $owner->setClient($client);
                            $this->em->persist($owner);
                        }
                    }
                } else {
                    $contact = $this->em->getRepository('App\Entity\ClientAdditionalContact')->find($ownerItem['owner_contact_id']);
                    if ($contact) {
                        $exist = $repo->findOneBy([
                            'owner_type' => $ownerItem['owner_type'],
                            'owner_contact_id' => $contact->getId(),
                            'account_id' => $account->getId(),
                        ]);
                        if (!$exist) {
                            $owner->setContact($contact);
                            $this->em->persist($owner);
                        }
                    }
                }

                $account->addAccountOwner($owner);
            }
        }

        $this->em->persist($account);
    }

    /**
     * Save transfer information of account.
     *
     * @param ClientAccount $account
     *
     * @return \App\Entity\TransferInformation
     */
    protected function saveTransferInformation(ClientAccount $account)
    {
        $transferInformation = $account->getTransferInformation();
        $transferCustodianId = $this->form->get('transferInformation')->get('transfer_custodian_id')->getData();
        $isFirmNotAppear = $this->form->get('transferInformation')->get('is_firm_not_appear')->getData();

        $transferInformation->setClientAccount($account);

        if (!$isFirmNotAppear && $transferCustodianId) {
            $transferCustodian = $this->em->getRepository('App\Entity\TransferCustodian')->find($transferCustodianId);

            $transferInformation->setTransferCustodian($transferCustodian);

            $questionnaire = $transferInformation->getQuestionnaireAnswers();
            foreach ($questionnaire as $answer) {
                $answer->setTransferInformation($transferInformation);
            }
        }

        return $transferInformation;
    }

    /**
     * Need to consolidate account?
     *
     * @return bool
     */
    protected function needConsolidate()
    {
        $data = $this->form->getData();

        return $this->consolidate && AccountGroup::GROUP_EMPLOYER_RETIREMENT !== $data->getGroupName();
    }

    /**
     * Consolidation account process.
     */
    protected function consolidateAccount(ClientAccount $account)
    {
        if ($this->needConsolidate()) {
            /** @var ClientAccountRepository $repo */
            $repo = $this->em->getRepository('App\Entity\ClientAccount');
            $consolidator = $repo->findConsolidatorForAccount($account);

            if ($consolidator) {
                $account->setConsolidator($consolidator);
            }
        }
    }
}
