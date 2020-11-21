<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 28.11.13
 * Time: 16:36.
 */

namespace App\Model\TabsConfiguration;

use App\Entity\BankInformation;
use App\Entity\Distribution;
use App\Model\ClientAccount;
use App\Model\SystemAccount;
use App\Model\SignableInterface;
use App\Model\Tab\RadioGroupTab;
use App\Model\Tab\TextTab;
use App\Model\TabCollection;

class IraDistributionForm extends AbstractTabsConfiguration
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
        if (!($this->signableObject instanceof Distribution)) {
            throw new \InvalidArgumentException('Signable object must be instance of Distribution.');
        }

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

        $tabs += $this->accountInformationSection();
        $tabs += $this->distributionTypeSection();
        $tabs += $this->paymentDetailsSection();
        $tabs += $this->taxWithholdingSection();
        $tabs += $this->paymentMethodSection();

        return new TabCollection($tabs);
    }

    /**
     * Generate account information section tabs.
     *
     * @return array
     */
    protected function accountInformationSection()
    {
        $clientAccount = $this->signableObject->getClientAccount();
        $owner = $clientAccount->getPrimaryApplicant();

        $tabs = [];

        $fullNameTab = new TextTab();
        $fullNameTab->setTabLabel('full_name')->setValue($owner->getFullName());
        $tabs[] = $fullNameTab;

        $ssnTab = new TextTab();
        $ssnTab->setTabLabel('ssn')->setValue($owner->getSsnTin());
        $tabs[] = $ssnTab;

        $phoneNumberTab = new TextTab();
        $phoneNumberTab->setTabLabel('phone_number')->setValue($owner->getPhoneNumber());
        $tabs[] = $phoneNumberTab;

        $birthDateTab = new TextTab();
        $birthDateTab->setTabLabel('birth_date')->setValue($owner->getBirthDate()->format('m-d-Y'));
        $tabs[] = $birthDateTab;

        $accountTypeTab = new RadioGroupTab();
        $accountTypeTab->setGroupName('account_type')
            ->setValue($this->getAccountTypeTabValue($clientAccount))
            ->setSelected(true);
        $tabs[] = $accountTypeTab;

        return $tabs;
    }

    /**
     * Generate distribution type section tabs.
     *
     * @return array
     */
    protected function distributionTypeSection()
    {
        $tabs = [];

        $distributionMethod = $this->signableObject->getDistributionMethod();
        if (Distribution::DISTRIBUTION_METHOD_NORMAL === $distributionMethod) {
            $distributionReason = 'normal_distribution';
        } else {
            $distributionReason = 'normal_distribution_roth_ira';
        }

        $distributionReasonTab = new RadioGroupTab();
        $distributionReasonTab->setGroupName('distribution_reason')->setValue($distributionReason)->setSelected(true);
        $tabs[] = $distributionReasonTab;

        return $tabs;
    }

    /**
     * Generate payment details section tabs.
     *
     * @return array
     */
    protected function paymentDetailsSection()
    {
        $tabs = [];

        $distributionTypeTab = new RadioGroupTab();
        $distributionTypeTab->setGroupName('distribution_type')->setValue('partial_cash_distribution')->setSelected(
            true
        );
        $tabs[] = $distributionTypeTab;

        $amountTab = new TextTab();
        $amountTab->setTabLabel('distribution_amount')->setValue($this->signableObject->getAmount());
        $tabs[] = $amountTab;

        $startTransferDate = $this->signableObject->getTransferDate();
        if ($startTransferDate) {
            $startDateTab = new TextTab();
            $startDateTab->setTabLabel('distribution_start_date')->setValue($startTransferDate->format('m-d-Y'));
            $tabs[] = $startDateTab;

            return $tabs;
        }

        if ($this->signableObject->isOneTime()) {
            $frequency = 'one_time';
        } else {
            switch ($this->signableObject->getFrequency()) {
                case Distribution::FREQUENCY_MONTHLY:
                    $frequency = 'monthly';
                    break;
                case Distribution::FREQUENCY_QUARTERLY:
                    $frequency = 'quarterly';
                    break;
                default:
                    $frequency = '';
                    break;
            }
        }

        $frequencyTab = new RadioGroupTab();
        $frequencyTab->setGroupName('distribution_frequency')->setValue($frequency)->setSelected(true);
        $tabs[] = $frequencyTab;

        return $tabs;
    }

    /**
     * Generate tax withholding section tabs.
     *
     * @return array
     */
    protected function taxWithholdingSection()
    {
        $tabs = [];

        $useFederalWithhold = (Distribution::FEDERAL_WITHHOLDING_TAXES === $this->signableObject->getFederalWithholding());
        $federalWithholdTab = new RadioGroupTab();
        $federalWithholdTab
            ->setGroupName('use_federal_withholding')
            ->setValue($useFederalWithhold ? 'yes' : 'no')
            ->setSelected(true);
        $tabs[] = $federalWithholdTab;

        if ($useFederalWithhold) {
            $federalRate = round($this->signableObject->getFederalWithholdMoney(), 2);
            if ($federalRate) {
                $federalWithholdRateTab = new TextTab();
                $federalWithholdRateTab->setTabLabel('\\*federal_withholding_rate')->setValue($federalRate);
                $tabs[] = $federalWithholdRateTab;
            }

            $federalRatePercent = round($this->signableObject->getFederalWithholdPercent(), 2);
            if ($federalRatePercent) {
                $federalWithholdRatePercentTab = new TextTab();
                $federalWithholdRatePercentTab->setTabLabel('\\*federal_withholding_rate_percent')
                    ->setValue($federalRatePercent);
                $tabs[] = $federalWithholdRatePercentTab;
            }
        }

        $useStateWithhold = (Distribution::STATE_WITHHOLDING_TAXES === $this->signableObject->getStateWithholding());
        $stateWithholdTab = new RadioGroupTab();
        $stateWithholdTab
            ->setGroupName('use_state_withholding')
            ->setValue($useStateWithhold ? 'yes' : 'no')
            ->setSelected(true);
        $tabs[] = $stateWithholdTab;

        if ($useStateWithhold) {
            $stateRate = round($this->signableObject->getStateWithholdMoney(), 2);
            if ($stateRate) {
                $stateWithholdRateTab = new TextTab();
                $stateWithholdRateTab->setTabLabel('\\*state_withholding_rate')->setValue($stateRate);
                $tabs[] = $stateWithholdRateTab;
            }

            $stateRatePercent = round($this->signableObject->getStateWithholdPercent(), 2);
            if ($stateRatePercent) {
                $stateWithholdRatePercentTab = new TextTab();
                $stateWithholdRatePercentTab->setTabLabel('\\*state_withholding_rate_percent')
                    ->setValue($stateRatePercent);
                $tabs[] = $stateWithholdRatePercentTab;
            }
        }

        $residenceState = $this->signableObject->getResidenceState();
        if ($residenceState) {
            $residentStateTab = new TextTab();
            $residentStateTab->setTabLabel('residence_state')->setValue($residenceState->getName());
            $tabs[] = $residentStateTab;

            return $tabs;
        }

        return $tabs;
    }

    /**
     * Generate payment method section tabs.
     *
     * @return array
     */
    protected function paymentMethodSection()
    {
        $clientAccount = $this->signableObject->getClientAccount();
        $owner = $clientAccount->getPrimaryApplicant();

        $paymentMethod = '';
        $transferMethod = $this->signableObject->getTransferMethod();
        if (Distribution::TRANSFER_METHOD_RECEIVE_CHECK === $transferMethod) {
            $paymentMethod = 'send_check';

            $sendCheckMethodTab = new RadioGroupTab();
            $sendCheckMethodTab->setGroupName('send_check_method')->setValue('us_first_class_mail')->setSelected(true);
            $tabs[] = $sendCheckMethodTab;

            $sendCheckTypeTab = new RadioGroupTab();
            $sendCheckTypeTab->setGroupName('send_check_type')->setValue('address_of_record')->setSelected(true);
            $tabs[] = $sendCheckTypeTab;

            $nameTab = new TextTab();
            $nameTab->setTabLabel('send_check_payee_name')->setValue($owner->getFullName());
            $tabs[] = $nameTab;

            $addressTab = new TextTab();
            $addressTab->setTabLabel('send_check_address')->setValue($owner->getStreet());
            $tabs[] = $addressTab;

            $cityTab = new TextTab();
            $cityTab->setTabLabel('send_check_city')->setValue($owner->getCity());
            $tabs[] = $cityTab;

            $stateTab = new TextTab();
            $stateTab->setTabLabel('send_check_state')->setValue($owner->getState()->getName());
            $tabs[] = $stateTab;

            $zipTab = new TextTab();
            $zipTab->setTabLabel('send_check_zip')->setValue($owner->getZip());
            $tabs[] = $zipTab;
        } elseif (Distribution::TRANSFER_METHOD_NOT_FUNDING !== $transferMethod) {
            $paymentMethod = 'electronic';

            $electronicType = (Distribution::TRANSFER_METHOD_WIRE_TRANSFER === $transferMethod) ? 'wire_transfer' : 'bank_transfer';
            $electronicTypeTab = new RadioGroupTab();
            $electronicTypeTab->setGroupName('electronic_type')->setValue($electronicType)->setSelected(true);
            $tabs[] = $electronicTypeTab;

            /** @var BankInformation $bankInformation */
            $bankInformation = $this->signableObject->getBankInformation();
            if ($bankInformation) {
                $isCheckingAccountType = (BankInformation::ACCOUNT_TYPE_CHECK === $bankInformation->getAccountType());

                $bankAccountTypeTab = new RadioGroupTab();
                $bankAccountTypeTab->setGroupName('electronic_account_type')
                    ->setValue($isCheckingAccountType ? 'check' : 'saving')
                    ->setSelected(true);
                $tabs[] = $bankAccountTypeTab;

                $bankAccountTitleTab = new TextTab();
                $bankAccountTitleTab
                    ->setTabLabel('electronic_bank_account_title')
                    ->setValue($bankInformation->getAccountTitle());
                $tabs[] = $bankAccountTitleTab;

                $bankAccountNumberTab = new TextTab();
                $bankAccountNumberTab
                    ->setTabLabel('electronic_bank_account_number')
                    ->setValue($bankInformation->getAccountNumber());
                $tabs[] = $bankAccountNumberTab;

                $bankRoutingNumberTab = new TextTab();
                $bankRoutingNumberTab
                    ->setTabLabel('electronic_bank_routing_number')
                    ->setValue($bankInformation->getRoutingNumber());
                $tabs[] = $bankRoutingNumberTab;

                $bankNameTab = new TextTab();
                $bankNameTab->setTabLabel('electronic_bank_name')->setValue($bankInformation->getName());
                $tabs[] = $bankNameTab;
            }
        }

        $paymentMethodTab = new RadioGroupTab();
        $paymentMethodTab->setGroupName('payment_method')->setValue($paymentMethod)->setSelected(true);
        $tabs[] = $paymentMethodTab;

        return $tabs;
    }

    /**
     * Get type of account tab value.
     *
     * @param ClientAccount $account
     *
     * @return string
     */
    private function getAccountTypeTabValue(ClientAccount $account)
    {
        $systemType = $account->getSystemType();
        $type = $account->getTypeName();

        switch ($systemType) {
            case SystemAccount::TYPE_ROTH_IRA:
                $result = 'roth_ira';
                break;
            case SystemAccount::TYPE_TRADITIONAL_IRA:
                if ('SEP IRA' === $type) {
                    $result = 'sep_ira';
                } elseif ('SIMPLE IRA' === $type) {
                    $result = 'simple_ira';
                } else {
                    $result = 'traditional_ira';
                }
                break;
            default:
                $result = '';
                break;
        }

        return $result;
    }
}
