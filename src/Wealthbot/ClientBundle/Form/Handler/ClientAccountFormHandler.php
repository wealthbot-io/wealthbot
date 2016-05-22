<?php

namespace Wealthbot\ClientBundle\Form\Handler;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Wealthbot\ClientBundle\Docusign\TransferInformationCustodianCondition;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\ClientAccountOwner;
use Wealthbot\ClientBundle\Repository\ClientAccountRepository;
use Wealthbot\SignatureBundle\Manager\AccountDocusignManager;

class ClientAccountFormHandler
{
    protected $form;
    protected $request;
    protected $adm;
    protected $em;
    private $owners;
    private $consolidate;

    public function __construct(Form $form, Request $request, AccountDocusignManager $adm, array $owners = [], $consolidate = true)
    {
        $this->form = $form;
        $this->request = $request;
        $this->adm = $adm;
        $this->em = $adm->getObjectManager();
        $this->owners = $owners;
        $this->consolidate = $consolidate;
    }

    public function process()
    {
        if ('POST' === $this->request->getMethod()) {
            $this->form->handleRequest($this->request);

            if ($this->form->isValid()) {

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
                }

                $this->em->persist($data);
                $this->em->flush();

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
            $owner->setAccount($account);

            $account->addAccountOwner($owner);

            $this->em->persist($owner);
        } else {
            $repo = $this->em->getRepository('WealthbotClientBundle:ClientAccountOwner');

            foreach ($this->owners as $ownerItem) {
                $owner = new ClientAccountOwner();
                $owner->setOwnerType($ownerItem['owner_type']);
                $owner->setAccount($account);

                if ($ownerItem['owner_type'] === ClientAccountOwner::OWNER_TYPE_SELF) {
                    $client = $this->em->getRepository('WealthbotUserBundle:User')->find($ownerItem['owner_client_id']);
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
                    $contact = $this->em->getRepository('WealthbotClientBundle:ClientAdditionalContact')->find($ownerItem['owner_contact_id']);
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
        $this->em->flush();
    }

    /**
     * Save transfer information of account.
     *
     * @param ClientAccount $account
     *
     * @return \Wealthbot\ClientBundle\Entity\TransferInformation
     */
    protected function saveTransferInformation(ClientAccount $account)
    {
        $transferInformation = $account->getTransferInformation();
        $transferCustodianId = $this->form->get('transferInformation')->get('transfer_custodian_id')->getData();
        $isFirmNotAppear = $this->form->get('transferInformation')->get('is_firm_not_appear')->getData();

        $transferInformation->setClientAccount($account);

        if (!$isFirmNotAppear && $transferCustodianId) {
            $transferCustodian = $this->em->getRepository('WealthbotClientBundle:TransferCustodian')->find($transferCustodianId);

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

        return $this->consolidate && $data->getGroupName() !== AccountGroup::GROUP_EMPLOYER_RETIREMENT;
    }

    /**
     * Consolidation account process.
     */
    protected function consolidateAccount(ClientAccount $account)
    {
        if ($this->needConsolidate()) {
            /** @var ClientAccountRepository $repo */
            $repo = $this->em->getRepository('WealthbotClientBundle:ClientAccount');
            $consolidator = $repo->findConsolidatorForAccount($account);

            if ($consolidator) {
                $account->setConsolidator($consolidator);
            }
        }
    }
}
