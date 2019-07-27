<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.05.13
 * Time: 15:06
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Entity\User;

class UserAccountOwnerAdapter implements AccountOwnerInterface, \ArrayAccess
{
    private $user;
    private $region;
    private $street2;
    private $mailingStreetTwo;
    private $mailingRegion;
    private $email;
    private $first_name;
    private $last_name;
    private $middle_name;
    private $employment_type;

    public function __construct(User $user)
    {
        $this->user = $user;

        if (!$this->user->getClientPersonalInformation()) {
            $personalInformation = new \App\Entity\PersonalInformation();
            $personalInformation->setClient($this->user);

            $this->user->setClientPersonalInformation($personalInformation);
        }
    }

    public function getId()
    {
        return $this->user->getId();
    }

    public function getFirstName()
    {
        return $this->user->getFirstName();
    }

    public function setFirstName($firstName)
    {
        $this->user->getProfile()->setFirstName($firstName);

        return $this;
    }

    public function getMiddleName()
    {
        return $this->user->getMiddleName();
    }

    public function setMiddleName($middleName)
    {
        $this->user->getProfile()->setMiddleName($middleName);

        return $this;
    }

    public function getLastName()
    {
        return $this->user->getLastName();
    }

    public function setLastName($lastName)
    {
        $this->user->getProfile()->setLastName($lastName);

        return $this;
    }

    public function getFullName()
    {
        return $this->getFirstName().' '.$this->getMiddleName().' '.$this->getLastName();
    }

    public function getEmail()
    {
        return $this->user->getEmail();
    }

    public function setEmail($email)
    {
        $this->user->setEmail($email);

        return $this;
    }

    public function getType()
    {
        return 'self';
    }

    public function setType($type)
    {
        return $this;
    }

    public function getStreet()
    {
        return $this->user->getProfile()->getStreet();
    }

    public function setStreet($street)
    {
        $this->user->getProfile()->setStreet($street);

        return $this;
    }

    public function getCity()
    {
        return $this->user->getProfile()->getCity();
    }

    public function setCity($city)
    {
        $this->user->getProfile()->setCity($city);

        return $this;
    }

    public function getState()
    {
        return $this->user->getProfile()->getState();
    }

    public function setState(\App\Entity\State $state)
    {
        $this->user->getProfile()->setState($state);

        return $this;
    }

    public function getZip()
    {
        return $this->user->getProfile()->getZip();
    }

    public function setZip($zip)
    {
        $this->user->getProfile()->setZip($zip);

        return $this;
    }

    public function getIsDifferentAddress()
    {
        return $this->user->getProfile()->getIsDifferentAddress();
    }

    public function setIsDifferentAddress($isDifferentAddress)
    {
        $this->user->getProfile()->setIsDifferentAddress($isDifferentAddress);

        return $this;
    }

    public function getMailingStreet()
    {
        return $this->user->getProfile()->getMailingStreet();
    }

    public function setMailingStreet($mailingStreet)
    {
        $this->user->getProfile()->setMailingStreet($mailingStreet);

        return $this;
    }

    public function getMailingCity()
    {
        return $this->user->getProfile()->getMailingCity();
    }

    public function setMailingCity($mailingCity)
    {
        $this->user->getProfile()->setMailingCity($mailingCity);

        return $this;
    }

    public function getMailingState()
    {
        return $this->user->getProfile()->getMailingState();
    }

    public function setMailingState(\App\Entity\State $mailingState = null)
    {
        $this->user->getProfile()->setMailingState($mailingState);
    }

    public function getMailingZip()
    {
        return $this->user->getProfile()->getMailingZip();
    }

    public function setMailingZip($mailingZip)
    {
        $this->user->getProfile()->setMailingZip($mailingZip);
    }

    public function getBirthDate()
    {
        return $this->user->getProfile()->getBirthDate();
    }

    public function setBirthDate($birthDate)
    {
        $this->user->getProfile()->setBirthDate($birthDate);

        return $this;
    }

    public function getPhoneNumber()
    {
        return $this->user->getProfile()->getPhoneNumber();
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->user->getProfile()->setPhoneNumber($phoneNumber);

        return $this;
    }

    /**
     * Set dependents.
     *
     * @param int $dependents
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setDependents($dependents)
    {
        $this->user->getClientPersonalInformation()->setDependents($dependents);

        return $this;
    }

    /**
     * Get dependents.
     *
     * @return int
     */
    public function getDependents()
    {
        return $this->user->getClientPersonalInformation()->getDependents();
    }

    /**
     * Set ssn_tin.
     *
     * @param string $ssnTin
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setSsnTin($ssnTin)
    {
        $this->user->getClientPersonalInformation()->setSsnTin($ssnTin);

        return $this;
    }

    /**
     * Get ssn_tin.
     *
     * @return string
     */
    public function getSsnTin()
    {
        return $this->user->getClientPersonalInformation()->getSsnTin();
    }

    /**
     * Set income_source.
     *
     * @param string $incomeSource
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setIncomeSource($incomeSource)
    {
        $this->user->getClientPersonalInformation()->setIncomeSource($incomeSource);

        return $this;
    }

    /**
     * Get income_source.
     *
     * @return string
     */
    public function getIncomeSource()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getIncomeSource() : null;
    }

    /**
     * Set employer_name.
     *
     * @param string $employerName
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setEmployerName($employerName)
    {
        $this->user->getClientPersonalInformation()->setEmployerName($employerName);

        return $this;
    }

    /**
     * Get employer_name.
     *
     * @return string
     */
    public function getEmployerName()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getEmployerName() : null;
    }

    /**
     * Set industry.
     *
     * @param string $industry
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setIndustry($industry)
    {
        $this->user->getClientPersonalInformation()->setIndustry($industry);

        return $this;
    }

    /**
     * Get industry.
     *
     * @return string
     */
    public function getIndustry()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getIndustry() : null;
    }

    /**
     * Set occupation.
     *
     * @param string $occupation
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setOccupation($occupation)
    {
        $this->user->getClientPersonalInformation()->setOccupation($occupation);

        return $this;
    }

    /**
     * Get occupation.
     *
     * @return string
     */
    public function getOccupation()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getOccupation() : null;
    }

    /**
     * Set business_type.
     *
     * @param string $businessType
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setBusinessType($businessType)
    {
        $this->user->getClientPersonalInformation()->setBusinessType($businessType);

        return $this;
    }

    /**
     * Get business_type.
     *
     * @return string
     */
    public function getBusinessType()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getBusinessType() : null;
    }

    /**
     * Set employer_address.
     *
     * @param string $employerAddress
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setEmployerAddress($employerAddress)
    {
        $this->user->getClientPersonalInformation()->setEmployerAddress($employerAddress);

        return $this;
    }

    /**
     * Get employer_address.
     *
     * @return string
     */
    public function getEmployerAddress()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getEmployerAddress() : null;
    }

    /**
     * Set employment_city.
     *
     * @param string $employmentCity
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setEmploymentCity($employmentCity)
    {
        $this->user->getClientPersonalInformation()->setCity($employmentCity);

        return $this;
    }

    /**
     * Get employment_city.
     *
     * @return string
     */
    public function getEmploymentCity()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getCity() : null;
    }

    /**
     * Set employment_zip.
     *
     * @param string $employmentZip
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function setEmploymentZip($employmentZip)
    {
        $this->user->getClientPersonalInformation()->setZipcode($employmentZip);

        return $this;
    }

    /**
     * Get employment_zip.
     *
     * @return string
     */
    public function getEmploymentZip()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getZipcode() : null;
    }

    public function getEmploymentState()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getState() : null;
    }

    public function setEmploymentState(\App\Entity\State $state = null)
    {
        $this->user->getClientPersonalInformation()->setState($state);

        return $this;
    }

    /**
     * Set is_senior_political_figure.
     *
     * @param bool $isSeniorPoliticalFigure
     *
     * @return AccountOwnerInterface
     */
    public function setIsSeniorPoliticalFigure($isSeniorPoliticalFigure)
    {
        $this->user->getClientPersonalInformation()->setIsSeniorPoliticalFigure($isSeniorPoliticalFigure);

        return $this;
    }

    /**
     * Get is_senior_political_figure.
     *
     * @return bool
     */
    public function getIsSeniorPoliticalFigure()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getIsSeniorPoliticalFigure() : null;
    }

    /**
     * Set senior_spf_name.
     *
     * @param string $seniorSpfName
     *
     * @return AccountOwnerInterface
     */
    public function setSeniorSpfName($seniorSpfName)
    {
        $this->user->getClientPersonalInformation()->setSeniorSpfName($seniorSpfName);

        return $this;
    }

    /**
     * Get senior_spf_name.
     *
     * @return string
     */
    public function getSeniorSpfName()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getSeniorSpfName() : null;
    }

    /**
     * Set senior_political_title.
     *
     * @param string $seniorPoliticalTitle
     *
     * @return AccountOwnerInterface
     */
    public function setSeniorPoliticalTitle($seniorPoliticalTitle)
    {
        $this->user->getClientPersonalInformation()->setSeniorPoliticalTitle($seniorPoliticalTitle);

        return $this;
    }

    /**
     * Get senior_political_title.
     *
     * @return string
     */
    public function getSeniorPoliticalTitle()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getSeniorPoliticalTitle() : null;
    }

    /**
     * Set senior_account_owner_relationship.
     *
     * @param string $seniorAccountOwnerRelationship
     *
     * @return AccountOwnerInterface
     */
    public function setSeniorAccountOwnerRelationship($seniorAccountOwnerRelationship)
    {
        $this->user->getClientPersonalInformation()->setSeniorAccountOwnerRelationship($seniorAccountOwnerRelationship);

        return $this;
    }

    /**
     * Get senior_account_owner_relationship.
     *
     * @return string
     */
    public function getSeniorAccountOwnerRelationship()
    {
        return  $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getSeniorAccountOwnerRelationship() : null;
    }

    /**
     * Set senior_country_office.
     *
     * @param string $seniorCountryOffice
     *
     * @return AccountOwnerInterface
     */
    public function setSeniorCountryOffice($seniorCountryOffice)
    {
        $this->user->getClientPersonalInformation()->setSeniorCountryOffice($seniorCountryOffice);

        return $this;
    }

    /**
     * Get senior_country_office.
     *
     * @return string
     */
    public function getSeniorCountryOffice()
    {
        return  $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getSeniorCountryOffice() : null;
    }

    /**
     * Set is_publicly_traded_company.
     *
     * @param bool $isPubliclyTradedCompany
     *
     * @return AccountOwnerInterface
     */
    public function setIsPubliclyTradedCompany($isPubliclyTradedCompany)
    {
        $this->user->getClientPersonalInformation()->setIsPubliclyTradedCompany($isPubliclyTradedCompany);

        return $this;
    }

    /**
     * Get is_publicly_traded_company.
     *
     * @return bool
     */
    public function getIsPubliclyTradedCompany()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getIsPubliclyTradedCompany() : null;
    }

    /**
     * Set publicle_company_name.
     *
     * @param string $publicleCompanyName
     *
     * @return AccountOwnerInterface
     */
    public function setPublicleCompanyName($publicleCompanyName)
    {
        $this->user->getClientPersonalInformation()->setPublicleCompanyName($publicleCompanyName);

        return $this;
    }

    /**
     * Get publicle_company_name.
     *
     * @return string
     */
    public function getPublicleCompanyName()
    {
        return  $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getPublicleCompanyName() : null;
    }

    /**
     * Set publicle_address.
     *
     * @param string $publicleAddress
     *
     * @return AccountOwnerInterface
     */
    public function setPublicleAddress($publicleAddress)
    {
        $this->user->getClientPersonalInformation()->setPublicleAddress($publicleAddress);

        return $this;
    }

    /**
     * Get publicle_address.
     *
     * @return string
     */
    public function getPublicleAddress()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getPublicleAddress() : null;
    }

    /**
     * Set publicle_city.
     *
     * @param string $publicleCity
     *
     * @return AccountOwnerInterface
     */
    public function setPublicleCity($publicleCity)
    {
        $this->user->getClientPersonalInformation()->setPublicleCity($publicleCity);

        return $this;
    }

    /**
     * Get publicle_city.
     *
     * @return string
     */
    public function getPublicleCity()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getPublicleCity() : null;
    }

    /**
     * Set publicleState.
     *
     * @param \App\Entity\State $publicleState
     *
     * @return AccountOwnerInterface
     */
    public function setPublicleState(\App\Entity\State $publicleState = null)
    {
        $this->user->getClientPersonalInformation()->setPublicleState($publicleState);

        return $this;
    }

    /**
     * Get publicleState.
     *
     * @return \App\Entity\State
     */
    public function getPublicleState()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getPublicleState() : null;
    }

    /**
     * Set is_broker_security_exchange_person.
     *
     * @param bool $isBrokerSecurityExchangePerson
     *
     * @return AccountOwnerInterface
     */
    public function setIsBrokerSecurityExchangePerson($isBrokerSecurityExchangePerson)
    {
        $this->user->getClientPersonalInformation()->setIsBrokerSecurityExchangePerson($isBrokerSecurityExchangePerson);

        return $this;
    }

    /**
     * Get is_broker_security_exchange_person.
     *
     * @return bool
     */
    public function getIsBrokerSecurityExchangePerson()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getIsBrokerSecurityExchangePerson() : null;
    }

    /**
     * Set broker_security_exchange_company_name.
     *
     * @param string $brokerSecurityExchangeCompanyName
     *
     * @return AccountOwnerInterface
     */
    public function setBrokerSecurityExchangeCompanyName($brokerSecurityExchangeCompanyName)
    {
        $this->user->getClientPersonalInformation()->setBrokerSecurityExchangeCompanyName($brokerSecurityExchangeCompanyName);

        return $this;
    }

    /**
     * Get broker_security_exchange_company_name.
     *
     * @return string
     */
    public function getBrokerSecurityExchangeCompanyName()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getBrokerSecurityExchangeCompanyName() : null;
    }

    /**
     * Set broker_security_exchange_compliance_letter.
     *
     * @param string $brokerSecurityExchangeComplianceLetter
     *
     * @return AccountOwnerInterface
     */
    public function setBrokerSecurityExchangeComplianceLetter($brokerSecurityExchangeComplianceLetter)
    {
        $this->user->getClientPersonalInformation()->setBrokerSecurityExchangeComplianceLetter($brokerSecurityExchangeComplianceLetter);

        return $this;
    }

    /**
     * Get broker_security_exchange_compliance_letter.
     *
     * @return string
     */
    public function getBrokerSecurityExchangeComplianceLetter()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getBrokerSecurityExchangeComplianceLetter() : null;
    }

    /**
     * Set compliance_letter_file.
     *
     * @param $complianceLetterFile
     *
     * @return AccountOwnerInterface
     */
    public function setComplianceLetterFile($complianceLetterFile)
    {
        $this->user->getClientPersonalInformation()->setComplianceLetterFile($complianceLetterFile);

        return $this;
    }

    /**
     * Get compliance_letter_file.
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getComplianceLetterFile()
    {
        return $this->user->getClientPersonalInformation() ? $this->user->getClientPersonalInformation()->getComplianceLetterFile() : null;
    }

    /**
     * Set employment_type.
     *
     * @param string $employmentType
     *
     * @return AccountOwnerInterface
     */
    public function setEmploymentType($employmentType)
    {
        $this->user->getProfile()->setEmploymentType($employmentType);

        return $this;
    }

    /**
     * Get employment_type.
     *
     * @return string
     */
    public function getEmploymentType()
    {
        return $this->user->getProfile()->getEmploymentType();
    }

    /**
     * Set marital status.
     *
     * @param string $maritalStatus
     *
     * @return AccountOwnerInterface
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->user->getProfile()->setMaritalStatus($maritalStatus);

        return $this;
    }

    /**
     * Get marital status.
     *
     * @return string
     */
    public function getMaritalStatus()
    {
        return $this->user->getProfile()->getMaritalStatus();
    }

    public function getObjectToSave()
    {
        return $this->user;
    }

    public function getSpouse()
    {
        return $this->user->getSpouse();
    }

    public function setSpouse(\App\Entity\ClientAdditionalContact $spouse)
    {
        $spouse->setClient($this->user);
        $this->user->setSpouse($spouse);
    }

    public function getAnnualIncome()
    {
        return $this->user->getProfile()->getAnnualIncome();
    }

    public function setAnnualIncome($annualIncome)
    {
        $this->user->getProfile()->setAnnualIncome($annualIncome);
    }

    public function getEstimatedIncomeTax()
    {
        return $this->user->getProfile()->getEstimatedIncomeTax();
    }

    public function setEstimatedIncomeTax($estimatedIncomeTax)
    {
        $this->user->getProfile()->setEstimatedIncomeTax($estimatedIncomeTax);
    }

    public function getLiquidNetWorth()
    {
        return $this->user->getProfile()->getLiquidNetWorth();
    }

    public function setLiquidNetWorth($liquidNetWorth)
    {
        $this->user->getProfile()->setLiquidNetWorth($liquidNetWorth);
    }

    /**
     * Set first name of spouse.
     *
     * @param string $spouseFirstName
     *
     * @return self
     */
    public function setSpouseFirstName($spouseFirstName)
    {
        if (!$this->getSpouse()) {
            $this->setSpouse($this->createSpouseObject());
        }

        $this->getSpouse()->setFirstName($spouseFirstName);
    }

    /**
     * Get first name of spouse.
     *
     * @return string
     */
    public function getSpouseFirstName()
    {
        return $this->getSpouse() ? $this->getSpouse()->getFirstName() : null;
    }

    /**
     * Set middle name of spouse.
     *
     * @param string $spouseMiddleName
     *
     * @return self
     */
    public function setSpouseMiddleName($spouseMiddleName)
    {
        if (!$this->getSpouse()) {
            $this->setSpouse($this->createSpouseObject());
        }

        $this->getSpouse()->setMiddleName($spouseMiddleName);
    }

    /**
     * Get middle name of spouse.
     *
     * @return string
     */
    public function getSpouseMiddleName()
    {
        return $this->getSpouse() ? $this->getSpouse()->getMiddleName() : null;
    }

    /**
     * Set last name of spouse.
     *
     * @param string $spouseLastName
     *
     * @return self
     */
    public function setSpouseLastName($spouseLastName)
    {
        if (!$this->getSpouse()) {
            $this->setSpouse($this->createSpouseObject());
        }

        $this->getSpouse()->setLastName($spouseLastName);
    }

    /**
     * Get last name of spouse.
     *
     * @return string
     */
    public function getSpouseLastName()
    {
        return $this->getSpouse() ? $this->getSpouse()->getLastName() : null;
    }

    /**
     * Set spouse_birth_date.
     *
     * @param \DateTime $spouseBirthDate
     *
     * @return self
     */
    public function setSpouseBirthDate($spouseBirthDate)
    {
        if (!$this->getSpouse()) {
            $this->setSpouse($this->createSpouseObject());
        }

        $this->getSpouse()->setBirthDate($spouseBirthDate);
    }

    /**
     * Get spouse_birth_date.
     *
     * @return \DateTime|null
     */
    public function getSpouseBirthDate()
    {
        return $this->getSpouse() ? $this->getSpouse()->getBirthDate() : null;
    }

    /**
     * Create new spouse object for user.
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    private function createSpouseObject()
    {
        return $this->user->createSpouseObject();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     */
    public function setRegion($region): void
    {
        $this->region = $region;
    }

    /**
     * @return mixed
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * @param mixed $street2
     */
    public function setStreet2($street2): void
    {
        $this->street2 = $street2;
    }

    /**
     * @return mixed
     */
    public function getMailingStreetTwo()
    {
        return $this->mailingStreetTwo;
    }

    /**
     * @param mixed $mailingStreetTwo
     */
    public function setMailingStreetTwo($mailingStreetTwo): void
    {
        $this->mailingStreetTwo = $mailingStreetTwo;
    }

    /**
     * @return mixed
     */
    public function getMailingRegion()
    {
        return $this->mailingRegion;
    }

    /**
     * @param mixed $mailingRegion
     */
    public function setMailingRegion($mailingRegion): void
    {
        $this->mailingRegion = $mailingRegion;
    }

    public function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    public function offsetGet($offset)
    {
        // TODO: Implement offsetGet() method.
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }
}
