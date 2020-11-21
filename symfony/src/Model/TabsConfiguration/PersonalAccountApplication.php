<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.09.13
 * Time: 13:25
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model\TabsConfiguration;

use App\Model\AccountOwnerInterface;
use App\Model\ClientAccount;
use App\Model\Tab\CheckboxTab;
use App\Model\Tab\RadioGroupTab;
use App\Model\Tab\TextTab;
use App\Model\TabCollection;
use App\Entity\Profile;

class PersonalAccountApplication extends AbstractTabsConfiguration
{
    /**
     * @var ClientAccount
     */
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
        $ria = $client->getRia();
        $companyInfo = $ria->getRiaCompanyInformation();

        $primaryApplicant = $this->account->getPrimaryApplicant();

        $tabs = [];

        $advisorCode = new TextTab();
        $advisorCode->setTabLabel('advisor#')->setValue($this->getAdvisorCode($companyInfo));
        $tabs[] = $advisorCode;

        $accountNumber = new TextTab();
        $accountNumber->setTabLabel('account#')->setValue($this->account->getAccountNumber());
        $tabs[] = $accountNumber;

        $firmName = new TextTab();
        $firmName->setTabLabel('firm_name')->setValue($companyInfo->getName());
        $tabs[] = $firmName;

        $primaryContact = new TextTab();
        $primaryContact->setTabLabel('primary_contact')
            ->setValue($companyInfo->getPrimaryFirstName().' '.$companyInfo->getPrimaryLastName());
        $tabs[] = $primaryContact;

        $accountTypeTab = new RadioGroupTab();
        $accountTypeTab->setGroupName('account_type')->setValue('cash')->setSelected(true);
        $tabs[] = $accountTypeTab;

        $ownerTabs = $this->getOwnerInformationTabs($primaryApplicant);

        $type = new RadioGroupTab();
        $type->setGroupName('type')->setSelected(true);
        if ($this->account->isJointType()) {
            $type->setValue('joint');
        } else {
            $type->setValue('individual');
        }
        $tabs[] = $type;

        $tabs = array_merge($tabs, $ownerTabs);

        if ($this->account->isJointType()) {
            $jointOwnerTabs = $this->getOwnerInformationTabs($this->account->getSecondaryApplicant(), true);
            $tabs = array_merge($tabs, $jointOwnerTabs);
        }

        $cashSweepTab = new RadioGroupTab();
        $cashSweepTab->setGroupName('cash_sweep_vehicle')->setValue('td_ameritrade_fdic')->setSelected(true);
        $tabs[] = $cashSweepTab;

        return new TabCollection($tabs);
    }

    private function getOwnerInformationTabs(AccountOwnerInterface $owner, $isJoint = false)
    {
        $prefix = '';
        if ($isJoint) {
            $prefix = 'joint_';
        }

        $tabs = [];

        $fullName = new TextTab();
        $fullName->setTabLabel($prefix.'full_name')->setValue($owner->getFullName());
        $tabs[] = $fullName;

        $ssn = new TextTab();
        $ssn->setTabLabel($prefix.'ssn')->setValue($owner->getSsnTin());
        $tabs[] = $ssn;

        $dob = new TextTab();
        $dob->setTabLabel($prefix.'dob')->setValue($owner->getBirthDate()->format('m-d-Y'));
        $tabs[] = $dob;

        $homeStreet = new TextTab();
        $homeStreet->setTabLabel($prefix.'home_address_street')->setValue($owner->getStreet());
        $tabs[] = $homeStreet;

        $homeCity = new TextTab();
        $homeCity->setTabLabel($prefix.'home_address_city')->setValue($owner->getCity());
        $tabs[] = $homeCity;

        $homeState = new TextTab();
        $homeState->setTabLabel($prefix.'home_address_state')
            ->setValue($owner->getState() ? $owner->getState()->getName() : '');
        $tabs[] = $homeState;

        $homeZip = new TextTab();
        $homeZip->setTabLabel($prefix.'home_address_zip')->setValue($owner->getZip());
        $tabs[] = $homeZip;

        $primaryPhone = new TextTab();
        $primaryPhone->setTabLabel($prefix.'primary_phone_number')->setValue($owner->getPhoneNumber());
        $tabs[] = $primaryPhone;

        $emailAddress = new TextTab();
        $emailAddress->setTabLabel($prefix.'email_address')->setValue($owner->getEmail());
        $tabs[] = $emailAddress;

        // If is different mailing address
        if ($owner->getIsDifferentAddress()) {
            $mailingStreet = new TextTab();
            $mailingStreet->setTabLabel($prefix.'mailing_address_street')->setValue($owner->getMailingStreet());
            $tabs[] = $mailingStreet;

            $mailingCity = new TextTab();
            $mailingCity->setTabLabel($prefix.'mailing_address_city')->setValue($owner->getMailingCity());
            $tabs[] = $mailingCity;

            $mailingState = new TextTab();
            $mailingState->setTabLabel($prefix.'mailing_address_state')
                ->setValue($owner->getMailingState() ? $owner->getMailingState()->getName() : '');
            $tabs[] = $mailingState;

            $mailingZip = new TextTab();
            $mailingZip->setTabLabel($prefix.'mailing_address_zip')->setValue($owner->getMailingZip());
            $tabs[] = $mailingZip;
        }

        // Check employment type
        $employmentType = $owner->getEmploymentType();
        if (Profile::CLIENT_EMPLOYMENT_TYPE_RETIRED === $employmentType ||
            Profile::CLIENT_EMPLOYMENT_TYPE_UNEMPLOYED === $employmentType) {
            $employmentStatus = new RadioGroupTab();
            $employmentStatus->setGroupName($prefix.'employment_status')->setValue(strtolower($employmentType))->setSelected(true);
            $tabs[] = $employmentStatus;

            $incomeSource = new TextTab();
            $incomeSource->setTabLabel($prefix.'source_of_income')->setValue($owner->getIncomeSource());
            $tabs[] = $incomeSource;
        } elseif (Profile::CLIENT_EMPLOYMENT_TYPE_EMPLOYED === $employmentType ||
            Profile::CLIENT_EMPLOYMENT_TYPE_SELF_EMPLOYED === $employmentType) {
            $employerName = new TextTab();
            $employerName->setTabLabel($prefix.'employer_name')->setValue($owner->getEmployerName());
            $tabs[] = $employerName;

            $occupation = new TextTab();
            $occupation->setTabLabel($prefix.'occupation')->setValue($owner->getOccupation());
            $tabs[] = $occupation;

            $businessType = new TextTab();
            $businessType->setTabLabel($prefix.'type_of_business')->setValue($owner->getBusinessType());
            $tabs[] = $businessType;

            $employerStreet = new TextTab();
            $employerStreet->setTabLabel($prefix.'employer_address_street')->setValue($owner->getEmployerAddress());
            $tabs[] = $employerStreet;

            $employerCity = new TextTab();
            $employerCity->setTabLabel($prefix.'employer_address_city')->setValue($owner->getEmploymentCity());
            $tabs[] = $employerCity;

            $employerState = new TextTab();
            $employerState->setTabLabel($prefix.'employer_address_state')
                ->setValue($owner->getEmploymentState() ? $owner->getEmploymentState()->getName() : '');
            $tabs[] = $employerState;

            $employerZip = new TextTab();
            $employerZip->setTabLabel($prefix.'employer_address_zip')->setValue($owner->getEmploymentZip());
            $tabs[] = $employerZip;
        }

        $isUsCitizen = new RadioGroupTab();
        $isUsCitizen->setGroupName($prefix.'is_us_citizen')->setValue('yes')->setSelected(true);
        $tabs[] = $isUsCitizen;

        if ($owner->getIsSeniorPoliticalFigure()) {
            $isSeniorPoliticalFigure = new CheckboxTab();
            $isSeniorPoliticalFigure->setTabLabel($prefix.'is_senior_political_figure')->setSelected(true);

            $fields = $owner->getSeniorSpfName().', '.$owner->getSeniorPoliticalTitle().
                ', '.$owner->getSeniorAccountOwnerRelationship().
                ', '.$owner->getSeniorCountryOffice();

            $spfFields = new TextTab();
            $spfFields->setTabLabel($prefix.'spf_fields')->setValue($fields);

            $tabs[] = $isSeniorPoliticalFigure;
            $tabs[] = $spfFields;
        }

        return $tabs;
    }
}
