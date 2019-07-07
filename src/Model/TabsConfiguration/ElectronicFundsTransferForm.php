<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 28.10.13
 * Time: 19:41.
 */

namespace App\Model\TabsConfiguration;

use App\Entity\BankInformation;
use App\Entity\Distribution;
use App\Model\BaseContribution;
use App\Model\SignableInterface;
use App\Model\Tab\RadioGroupTab;
use App\Model\Tab\TextTab;
use App\Model\TabCollection;

class ElectronicFundsTransferForm extends AbstractTabsConfiguration
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
     * @throws \InvalidArgumentException
     */
    public function generate()
    {
        $account = $this->signableObject->getClientAccount();
        $transferInformation = $this->signableObject;

        if ($transferInformation instanceof Distribution) {
            $transferDirection = 'distribution';
        } elseif ($transferInformation instanceof BaseContribution) {
            $transferDirection = 'contribution';
        } else {
            throw new \InvalidArgumentException('Signable object must be instance of Distribution or BaseContribution');
        }

        $client = $account->getClient();
        $companyInformation = $client->getRiaCompanyInformation();

        $tabs = [];

        $advisorCode = new TextTab();
        $advisorCode->setTabLabel('advisor#')->setValue($this->getAdvisorCode($companyInformation));
        $tabs[] = $advisorCode;

        $accountNumber = new TextTab();
        $accountNumber->setTabLabel('account#')->setValue($account->getAccountNumber());
        $tabs[] = $accountNumber;

        if ($account->hasSystemAccount()) {
            $transferInstructionsValue = 'update';
        } else {
            $transferInstructionsValue = 'new';
        }

        $transferInstructionsTab = new RadioGroupTab();
        $transferInstructionsTab->setGroupName('transfer_instructions')
            ->setValue($transferInstructionsValue)
            ->setSelected(true);
        $tabs[] = $transferInstructionsTab;

        $direction = new RadioGroupTab();
        $direction->setGroupName('transfer_direction')->setValue($transferDirection)->setSelected(true);
        $tabs[] = $direction;

        $bankInformation = $transferInformation->getBankInformation();
        if ($bankInformation) {
            $accountOwnerName = new TextTab();
            $accountOwnerName->setTabLabel('account_owner_full_name')->setValue($bankInformation->getAccountOwnerFullName());
            $tabs[] = $accountOwnerName;

            if ('' !== trim($bankInformation->getJointAccountOwnerFullName())) {
                $jointAccountOwner = new TextTab();
                $jointAccountOwner->setTabLabel('joint_account_owner_full_name')
                    ->setValue($bankInformation->getJointAccountOwnerFullName());
                $tabs[] = $jointAccountOwner;
            }

            $bankAccountType = new RadioGroupTab();
            $bankAccountTypeValue = BankInformation::ACCOUNT_TYPE_CHECK === $bankInformation->getAccountType() ? 'checking' : 'savings';
            $bankAccountType->setGroupName('bank_account_type')->setValue($bankAccountTypeValue)->setSelected(true);
            $tabs[] = $bankAccountType;

            $bankAccountNumber = new TextTab();
            $bankAccountNumber->setTabLabel('bank_account_number')->setValue($bankInformation->getAccountNumber());
            $tabs[] = $bankAccountNumber;

            $bankRoutingNumber = new TextTab();
            $bankRoutingNumber->setTabLabel('bank_routing_number')->setValue($bankInformation->getRoutingNumber());
            $tabs[] = $bankRoutingNumber;

            $bankName = new TextTab();
            $bankName->setTabLabel('bank_name')->setValue($bankInformation->getName());
            $tabs[] = $bankName;

            $bankAccountTitle = new TextTab();
            $bankAccountTitle->setTabLabel('bank_account_title')->setValue($bankInformation->getAccountTitle());
            $tabs[] = $bankAccountTitle;

            $bankPhoneNumber = new TextTab();
            $bankPhoneNumber->setTabLabel('bank_phone_number')->setValue($bankInformation->getPhoneNumber());
            $tabs[] = $bankPhoneNumber;
        }

        if ($transferInformation instanceof BaseContribution) {
            $startDateValue = $transferInformation->getStartTransferDate();

            $frequency = $transferInformation->getTransactionFrequency();
            switch ($frequency) {
                case BaseContribution::TRANSACTION_FREQUENCY_ONE_TIME:
                    $frequencyValue = 'one_time';
                    break;
                case BaseContribution::TRANSACTION_FREQUENCY_EVERY_OTHER_WEEK:
                    $frequencyValue = 'every_other_week';
                    break;
                case BaseContribution::TRANSACTION_FREQUENCY_MONTHLY:
                    $frequencyValue = 'monthly';
                    break;
                case BaseContribution::TRANSACTION_FREQUENCY_QUARTERLY:
                    $frequencyValue = 'quarterly';
                    break;
                default:
                    $frequencyValue = null;
                    break;
            }
        } elseif ($transferInformation instanceof Distribution) {
            $startDateValue = $transferInformation->getTransferDate();

            $frequency = $transferInformation->getFrequency();
            if ($transferInformation->isOneTime()) {
                $frequencyValue = 'one_time';
            } else {
                switch ($frequency) {
                    case Distribution::FREQUENCY_EVERY_OTHER_WEEK:
                        $frequencyValue = 'every_other_week';
                        break;
                    case Distribution::FREQUENCY_MONTHLY:
                        $frequencyValue = 'monthly';
                        break;
                    case Distribution::FREQUENCY_QUARTERLY:
                        $frequencyValue = 'quarterly';
                        break;
                    default:
                        $frequencyValue = null;
                        break;
                }
            }
        } else {
            $frequencyValue = null;
            $startDateValue = null;
        }

        if ($frequencyValue) {
            $frequencyTab = new RadioGroupTab();
            $frequencyTab->setGroupName('transaction_frequency')->setValue($frequencyValue)->setSelected(true);
            $tabs[] = $frequencyTab;
        }

        $amountTab = new TextTab();
        $amountTab->setTabLabel('transaction_amount')->setValue($transferInformation->getAmount());
        $tabs[] = $amountTab;

        if ($startDateValue) {
            $startDateTab = new TextTab();
            $startDateTab->setTabLabel('transfer_start_date')
                ->setValue($startDateValue->format('m/d'));
            $tabs[] = $startDateTab;
        }

        return new TabCollection($tabs);
    }
}
