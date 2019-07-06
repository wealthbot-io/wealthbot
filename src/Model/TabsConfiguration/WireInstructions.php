<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 28.11.13
 * Time: 16:31.
 */

namespace App\Model\TabsConfiguration;

use App\Entity\Distribution;
use App\Model\SignableInterface;
use App\Model\Tab\TextTab;
use App\Model\TabCollection;

class WireInstructions extends AbstractTabsConfiguration
{
    /** @var \App\Model\SignableInterface */
    private $signableObject;

    public function __construct(SignableInterface $object)
    {
        $this->signableObject = $object;
    }

    /**
     * Generate collection of tabs.
     *
     * @return TabCollection
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function generate()
    {
        if (!($this->signableObject instanceof Distribution) || !$this->signableObject->isOneTime()) {
            throw new \InvalidArgumentException('Signable object must be one-time distribution.');
        }

        if (Distribution::TRANSFER_METHOD_WIRE_TRANSFER !== $this->signableObject->getTransferMethod()) {
            throw new \RuntimeException('Invalid transfer method for one-time distribution.');
        }

        $bankInformation = $this->signableObject->getBankInformation();
        $clientAccount = $this->signableObject->getClientAccount();
        $client = $clientAccount ? $clientAccount->getClient() : null;
        $companyInformation = $client ? $client->getRiaCompanyInformation() : null;

        $tabs = [];

        $advisorCode = new TextTab();
        $advisorCode->setTabLabel('advisor#')->setValue($this->getAdvisorCode($companyInformation));
        $tabs[] = $advisorCode;

        $accountNumber = new TextTab();
        $accountNumber->setTabLabel('account#')->setValue($clientAccount ? $clientAccount->getAccountNumber() : '');
        $tabs[] = $accountNumber;

        $bankNameTab = new TextTab();
        $bankNameTab->setTabLabel('bank_name')->setValue($bankInformation->getName());
        $tabs[] = $bankNameTab;

        $bankPhoneTab = new TextTab();
        $bankPhoneTab->setTabLabel('bank_phone_number')->setValue($bankInformation->getPhoneNumber());
        $tabs[] = $bankPhoneTab;

        return new TabCollection($tabs);
    }
}
