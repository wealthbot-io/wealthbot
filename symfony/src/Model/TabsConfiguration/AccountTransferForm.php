<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 30.10.13
 * Time: 15:59.
 */

namespace App\Model\TabsConfiguration;

use App\Model\ClientAccount;
use App\Model\SystemAccount;
use App\Model\Tab\RadioGroupTab;
use App\Model\Tab\TextTab;
use App\Model\TabCollection;

class AccountTransferForm extends AbstractTabsConfiguration
{
    /** @var \App\Model\ClientAccount */
    private $account;

    public function __construct(ClientAccount $account)
    {
        $this->account = $account;
    }

    /**
     * Generate collection of tabs.
     *
     * @return TabCollection
     */
    public function generate()
    {
        $client = $this->account->getClient();
        $companyInformation = $client->getRiaCompanyInformation();

        $tabs = [];

        $advisorCode = new TextTab();
        $advisorCode->setTabLabel('advisor#')->setValue($this->getAdvisorCode($companyInformation));
        $tabs[] = $advisorCode;

        $accountNumber = new TextTab();
        $accountNumber->setTabLabel('account#')->setValue($this->account->getAccountNumber());
        $tabs[] = $accountNumber;

        $accountOwner = $this->account->getPrimaryApplicant();
        $transferInformation = $this->account->getTransferInformation();

        $accountTitleTab = new TextTab();
        $accountTitleTab->setTabLabel('account_title')->setValue($this->account->getTypeString());
        $tabs[] = $accountTitleTab;

        $ssnTab = new TextTab();
        $ssnTab->setTabLabel('ssn')->setValue($accountOwner->getSsnTin());
        $tabs[] = $ssnTab;

        $accountType = $this->getAccountTypeTabValue();
        $accountTypeTab = new RadioGroupTab();
        $accountTypeTab->setGroupName('account_type')->setValue($accountType)->setSelected(true);
        $tabs[] = $accountTypeTab;

        // Information on the account transferring from

        $accountTitle = $transferInformation->getAccountTitle().' '.$this->account->getTypeName();
        $transferFromAccountTitleTab = new TextTab();
        $transferFromAccountTitleTab->setTabLabel('transfer_from_account_title')->setValue($accountTitle);
        $tabs[] = $transferFromAccountTitleTab;

        $transferFromAccountNumberTab = new TextTab();
        $transferFromAccountNumberTab->setTabLabel('transfer_from_account_number')
            ->setValue($transferInformation->getAccountNumber());
        $tabs[] = $transferFromAccountNumberTab;

        $transferFromFirmTab = new TextTab();
        $transferFromFirmTab->setTabLabel('transfer_from_delivering_firm_name')
            ->setValue($transferInformation->getFinancialInstitution());
        $tabs[] = $transferFromFirmTab;

        $transferFromFirmAddressTab = new TextTab();
        $transferFromFirmAddressTab->setTabLabel('transfer_from_firm_address')
            ->setValue($transferInformation->getFirmAddress());
        $tabs[] = $transferFromFirmAddressTab;

        $transferFromFirmPhoneTab = new TextTab();
        $transferFromFirmPhoneTab->setTabLabel('transfer_from_phone_number')
            ->setValue($transferInformation->getPhoneNumber());
        $tabs[] = $transferFromFirmPhoneTab;

        $transferFromAccountTypeTab = new RadioGroupTab();
        $transferFromAccountTypeTab->setGroupName('transfer_from_account_type')->setValue($accountType)->setSelected(true);
        $tabs[] = $transferFromAccountTypeTab;

        // --------------------------------------------

        $brokerageFirmTransferTypeTab = new RadioGroupTab();
        $brokerageFirmTransferTypeTab->setGroupName('brokerage_firm_transfer_type')->setValue('full')->setSelected(true);
        $tabs[] = $brokerageFirmTransferTypeTab;

        $bankTransferTypeTab = new RadioGroupTab();
        $bankTransferTypeTab->setGroupName('bank_transfer_type')->setValue('full')->setSelected(true);
        $tabs[] = $bankTransferTypeTab;

        $depositCertificatesTab = new RadioGroupTab();
        $depositCertificatesTab->setGroupName('deposit_certificates')->setValue('redeem_cd_immediately')->setSelected(true);
        $tabs[] = $depositCertificatesTab;

        $deliveringAccountTitle = $transferInformation->getDeliveringAccountTitle();
        $ameritradeAccountTitle = $transferInformation->getAmeritradeAccountTitle();

        if (strlen($deliveringAccountTitle) > 0) {
            $deliveringAccountTitleTab = new TextTab();
            $deliveringAccountTitleTab->setTabLabel('delivering_account_title')->setValue($deliveringAccountTitle);
            $tabs[] = $deliveringAccountTitleTab;
        }

        if (strlen($ameritradeAccountTitle) > 0) {
            $ameritradeAccountTitleTab = new TextTab();
            $ameritradeAccountTitleTab->setTabLabel('td_ameritrade_account_title')->setValue($ameritradeAccountTitle);
            $tabs[] = $ameritradeAccountTitleTab;
        }

        return new TabCollection($tabs);
    }

    private function getAccountTypeTabValue()
    {
        $systemType = $this->account->getSystemType();
        $type = $this->account->getTypeName();

        $result = '';
        switch ($systemType) {
            case SystemAccount::TYPE_PERSONAL_INVESTMENT:
                $result = 'individual';
                break;
            case SystemAccount::TYPE_JOINT_INVESTMENT:
                $result = 'joint';
                break;
            case SystemAccount::TYPE_ROTH_IRA:
                $result = 'roth_ira';
                break;
            case SystemAccount::TYPE_TRADITIONAL_IRA:
                if ('SEP IRA' === $type) {
                    $result = 'sep_ira';
                } elseif ('SIMPLE IRA' === $type) {
                    $result = 'simple_ira';
                } else {
                    $result = 'traditional_or_rollover_ira';
                }
                break;
            case SystemAccount::TYPE_RETIREMENT:
                $result = 'qualified_retirement_plan';
                break;
        }

        return $result;
    }
}
