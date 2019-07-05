<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.10.13
 * Time: 18:53
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model\TabsConfiguration;

use App\Entity\Beneficiary;
use App\Model\AccountOwnerInterface;
use App\Model\ClientAccount;
use App\Model\Tab\CheckboxTab;
use App\Model\Tab\RadioGroupTab;
use App\Model\Tab\TextTab;
use App\Model\TabCollection;
use App\Entity\Profile;

class IraAccountApplication extends AbstractTabsConfiguration
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
        $advisorCode->setTabLabel('Advisor#')->setValue($this->getAdvisorCode($companyInfo));
        $tabs[] = $advisorCode;

        $accountNumber = new TextTab();
        $accountNumber->setTabLabel('Account#')->setValue($this->account->getAccountNumber());
        $tabs[] = $accountNumber;

        $firmName = new TextTab();
        $firmName->setTabLabel('FirmName')->setValue($companyInfo->getName());
        $tabs[] = $firmName;

        $primaryContact = new TextTab();
        $primaryContact->setTabLabel('PrimaryContact')
            ->setValue($companyInfo->getPrimaryFirstName().' '.$companyInfo->getPrimaryLastName());
        $tabs[] = $primaryContact;

        $ownerTabs = $this->getOwnerInformationTabs($primaryApplicant);

        $type = new RadioGroupTab();
        $type->setGroupName('IraType')->setSelected(true);
        if ($this->account->isTraditionalIraType()) {
            $type->setValue('Traditional');
        } elseif ($this->account->isRothIraType()) {
            $type->setValue('Roth');
        }
        $tabs[] = $type;

        $tabs = array_merge($tabs, $ownerTabs);

        if ($this->account->isJointType()) {
            $jointOwnerTabs = $this->getOwnerInformationTabs($this->account->getSecondaryApplicant(), true);
            $tabs = array_merge($tabs, $jointOwnerTabs);
        }

        // Check employment type
        $employmentType = $primaryApplicant->getEmploymentType();
        if (Profile::CLIENT_EMPLOYMENT_TYPE_RETIRED === $employmentType ||
            Profile::CLIENT_EMPLOYMENT_TYPE_UNEMPLOYED === $employmentType) {
            $employmentStatus = new RadioGroupTab();
            $employmentStatus->setGroupName('EmploymentStatus')->setValue($employmentType)->setSelected(true);
            $tabs[] = $employmentStatus;

            $incomeSource = new TextTab();
            $incomeSource->setTabLabel('SourceOfIncome')->setValue($primaryApplicant->getIncomeSource());
            $tabs[] = $incomeSource;
        } elseif (Profile::CLIENT_EMPLOYMENT_TYPE_EMPLOYED === $employmentType ||
            Profile::CLIENT_EMPLOYMENT_TYPE_SELF_EMPLOYED === $employmentType) {
            $employerName = new TextTab();
            $employerName->setTabLabel('EmployerName')->setValue($primaryApplicant->getEmployerName());
            $tabs[] = $employerName;

            $occupation = new TextTab();
            $occupation->setTabLabel('Occupation')->setValue($primaryApplicant->getOccupation());
            $tabs[] = $occupation;

            $businessType = new TextTab();
            $businessType->setTabLabel('TypeOfBusiness')->setValue($primaryApplicant->getBusinessType());
            $tabs[] = $businessType;

            $employerStreet = new TextTab();
            $employerStreet->setTabLabel('EmployerAddressStreet')->setValue($primaryApplicant->getEmployerAddress());
            $tabs[] = $employerStreet;

            $employerCity = new TextTab();
            $employerCity->setTabLabel('EmployerAddressCity')->setValue($primaryApplicant->getEmploymentCity());
            $tabs[] = $employerCity;

            $employerState = new TextTab();
            $employerState->setTabLabel('EmployerAddressState')
                ->setValue($primaryApplicant->getEmploymentState() ? $primaryApplicant->getEmploymentState()->getName() : '');
            $tabs[] = $employerState;

            $employerZip = new TextTab();
            $employerZip->setTabLabel('EmployerAddressZip')->setValue($primaryApplicant->getEmploymentZip());
            $tabs[] = $employerZip;
        }

        $beneficiariesTabs = $this->getBeneficiariesTabs();
        $tabs = array_merge($tabs, $beneficiariesTabs);

        $isUsCitizen = new RadioGroupTab();
        $isUsCitizen->setGroupName('IsUSCitizen')->setValue('yes')->setSelected(true);
        $tabs[] = $isUsCitizen;

        if ($primaryApplicant->getIsSeniorPoliticalFigure()) {
            $isSeniorPoliticalFigure = new CheckboxTab();
            $isSeniorPoliticalFigure->setTabLabel('IsSeniorPoliticalFigure')->setSelected(true);

            $fields = $primaryApplicant->getSeniorSpfName().', '.$primaryApplicant->getSeniorPoliticalTitle().
                ', '.$primaryApplicant->getSeniorAccountOwnerRelationship().
                ', '.$primaryApplicant->getSeniorCountryOffice();

            $spfFields = new TextTab();
            $spfFields->setTabLabel('SPFFields')->setValue($fields);

            $tabs[] = $isSeniorPoliticalFigure;
            $tabs[] = $spfFields;
        }

        return new TabCollection($tabs);
    }

    private function getOwnerInformationTabs(AccountOwnerInterface $owner, $isJoint = false)
    {
        $prefix = '';
        if ($isJoint) {
            $prefix = 'Joint';
        }

        $tabs = [];

        $fullName = new TextTab();
        $fullName->setTabLabel(''.$prefix.'FullName')->setValue($owner->getFullName());
        $tabs[] = $fullName;

        $ssn = new TextTab();
        $ssn->setTabLabel(''.$prefix.'SSN')->setValue($owner->getSsnTin());
        $tabs[] = $ssn;

        $dob = new TextTab();
        $dob->setTabLabel(''.$prefix.'DOB')->setValue($owner->getBirthDate()->format('m-d-Y'));
        $tabs[] = $dob;

        $homeStreet = new TextTab();
        $homeStreet->setTabLabel(''.$prefix.'HomeAddressStreet')->setValue($owner->getStreet());
        $tabs[] = $homeStreet;

        $homeCity = new TextTab();
        $homeCity->setTabLabel(''.$prefix.'HomeAddressCity')->setValue($owner->getCity());
        $tabs[] = $homeCity;

        $homeState = new TextTab();
        $homeState->setTabLabel(''.$prefix.'HomeAddressState')
            ->setValue($owner->getState() ? $owner->getState()->getName() : '');
        $tabs[] = $homeState;

        $homeZip = new TextTab();
        $homeZip->setTabLabel(''.$prefix.'HomeAddressZip')->setValue($owner->getZip());
        $tabs[] = $homeZip;

        $primaryPhone = new TextTab();
        $primaryPhone->setTabLabel(''.$prefix.'PrimaryPhoneNumber')->setValue($owner->getPhoneNumber());
        $tabs[] = $primaryPhone;

        $emailAddress = new TextTab();
        $emailAddress->setTabLabel(''.$prefix.'EmailAddress')->setValue($owner->getEmail());
        $tabs[] = $emailAddress;

        // If is different mailing address
        if ($owner->getIsDifferentAddress()) {
            $mailingStreet = new TextTab();
            $mailingStreet->setTabLabel(''.$prefix.'MailingAddressStreet')->setValue($owner->getMailingStreet());
            $tabs[] = $mailingStreet;

            $mailingCity = new TextTab();
            $mailingCity->setTabLabel(''.$prefix.'MailingAddressCity')->setValue($owner->getMailingCity());
            $tabs[] = $mailingCity;

            $mailingState = new TextTab();
            $mailingState->setTabLabel(''.$prefix.'MailingAddressState')
                ->setValue($owner->getMailingState() ? $owner->getMailingState()->getName() : '');
            $tabs[] = $mailingState;

            $mailingZip = new TextTab();
            $mailingZip->setTabLabel(''.$prefix.'MailingAddressZip')->setValue($owner->getMailingZip());
            $tabs[] = $mailingZip;
        }

        return $tabs;
    }

    private function getBeneficiariesTabs()
    {
        $tabs = [];
        $index = 1;

        /** @var Beneficiary $beneficiary */
        foreach ($this->account->getBeneficiaries() as $beneficiary) {
            $prefix = 'Beneficiary';

            $name = new TextTab();
            $name->setTabLabel($prefix.$index.'Name')->setValue($beneficiary->getFullName());
            $tabs[] = $name;

            $birthday = new TextTab();
            $birthday->setTabLabel($prefix.$index.'DOB')->setValue($beneficiary->getBirthDate()->format('m-d-Y'));
            $tabs[] = $birthday;

            $ssn = new TextTab();
            $ssn->setTabLabel($prefix.$index.'SSN')->setValue($beneficiary->getSsn());
            $tabs[] = $ssn;

            $street = new TextTab();
            $street->setTabLabel($prefix.$index.'HomeAddressStreet')->setValue($beneficiary->getStreet());
            $tabs[] = $street;

            $city = new TextTab();
            $city->setTabLabel($prefix.$index.'HomeAddressCity')->setValue($beneficiary->getCity());
            $tabs[] = $city;

            $state = new TextTab();
            $state->setTabLabel($prefix.$index.'HomeAddressState')
                ->setValue($beneficiary->getState() ? $beneficiary->getState()->getName() : '');
            $tabs[] = $state;

            $zip = new TextTab();
            $zip->setTabLabel($prefix.$index.'HomeAddressZip')->setValue($beneficiary->getZip());
            $tabs[] = $zip;

            $share = new TextTab();
            $share->setTabLabel($prefix.$index.'Share')->setValue(round($beneficiary->getShare(), 2));
            $tabs[] = $share;

            $type = new RadioGroupTab();
            $type->setGroupName($prefix.$index.'Type')
                ->setValue(strtolower($beneficiary->getType()))
                ->setSelected(true);
            $tabs[] = $type;

            $relationship = new TextTab();
            $relationship->setTabLabel($prefix.$index.'Relationship')->setValue($beneficiary->getRelationship());
            $tabs[] = $relationship;

            ++$index;
        }

        return $tabs;
    }
}
