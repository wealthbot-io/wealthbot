<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Model\AccountOwnerInterface;
use App\Model\ClientAdditionalContact as BaseClientAdditionalContact;

/**
 * Class ClientAdditionalContact
 * @package App\Entity
 */
class ClientAdditionalContact extends BaseClientAdditionalContact implements AccountOwnerInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client_id;

    /**
     * @var string
     */
    private $first_name;

    /**
     * @var string
     */
    private $last_name;

    /**
     * @var string
     */
    private $middle_name;

    /**
     * @var string
     */
    private $street;

    /**
     * @var string
     */
    private $city;

    /**
     * @var int
     */
    private $state_id;

    /**
     * @var string
     */
    private $zip;

    /**
     * @var bool
     */
    private $is_different_address;

    /**
     * @var string
     */
    private $mailing_street;

    /**
     * @var string
     */
    private $mailing_city;

    /**
     * @var int
     */
    private $mailing_state_id;

    /**
     * @var string
     */
    private $mailing_zip;

    /**
     * @var \DateTime
     */
    private $birth_date;

    /**
     * @var string
     */
    private $phone_number;

    /**
     * @var int
     */
    private $dependents;

    /**
     * @var string
     */
    private $ssn_tin;

    /**
     * @var string
     */
    private $income_source;

    /**
     * @var string
     */
    private $employer_name;

    /**
     * @var string
     */
    private $industry;

    /**
     * @var string
     */
    private $occupation;

    /**
     * @var string
     */
    private $business_type;

    /**
     * @var string
     */
    private $employer_address;

    /**
     * @var string
     */
    private $employment_city;

    /**
     * @var int
     */
    private $employment_state_id;

    /**
     * @var string
     */
    private $employment_zip;

    /**
     * @var bool
     */
    private $is_senior_political_figure;

    /**
     * @var string
     */
    private $senior_spf_name;

    /**
     * @var string
     */
    private $senior_political_title;

    /**
     * @var string
     */
    private $senior_account_owner_relationship;

    /**
     * @var string
     */
    private $senior_country_office;

    /**
     * @var bool
     */
    private $is_publicly_traded_company;

    /**
     * @var string
     */
    private $publicle_company_name;

    /**
     * @var string
     */
    private $publicle_address;

    /**
     * @var string
     */
    private $publicle_city;

    /**
     * @var int
     */
    private $publicle_state_id;

    /**
     * @var bool
     */
    private $is_broker_security_exchange_person;

    /**
     * @var string
     */
    private $broker_security_exchange_company_name;

    /**
     * @var string
     */
    private $broker_security_exchange_compliance_letter;

    /**
     * @param \App\Entity\User
     */
    private $client;

    /**
     * @param \App\Entity\State
     */
    private $state;

    /**
     * @param \App\Entity\State
     */
    private $mailingState;

    /**
     * @param \App\Entity\State
     */
    private $employmentState;

    /**
     * @param \App\Entity\State
     */
    private $publicleState;

    /**
     * @var UploadedFile
     */
    private $compliance_letter_file;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    private $relationship;

    /**
     * @var string
     */
    private $employment_type;

    /**
     * @var string
     */
    private $marital_status;

    /**
     * @var string
     */
    private $spouse_first_name;

    /**
     * @var string
     */
    private $spouse_middle_name;

    /**
     * @var string
     */
    private $spouse_last_name;

    /**
     * @var \DateTime
     */
    private $spouse_birth_date;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set client_id.
     *
     * @param int $clientId
     *
     * @return ClientAdditionalContact
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set first_name.
     *
     * @param string $firstName
     *
     * @return ClientAdditionalContact
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;

        return $this;
    }

    /**
     * Get first_name.
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set last_name.
     *
     * @param string $lastName
     *
     * @return ClientAdditionalContact
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;

        return $this;
    }

    /**
     * Get last_name.
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set middle_name.
     *
     * @param string $middleName
     *
     * @return ClientAdditionalContact
     */
    public function setMiddleName($middleName)
    {
        $this->middle_name = $middleName;

        return $this;
    }

    /**
     * Get middle_name.
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    public function getFullName()
    {
        return $this->getFirstName().' '.$this->getMiddleName().' '.$this->getLastName();
    }

    /**
     * Set street.
     *
     * @param string $street
     *
     * @return ClientAdditionalContact
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street.
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return ClientAdditionalContact
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state_id.
     *
     * @param int $stateId
     *
     * @return ClientAdditionalContact
     */
    public function setStateId($stateId)
    {
        $this->state_id = $stateId;

        return $this;
    }

    /**
     * Get state_id.
     *
     * @return int
     */
    public function getStateId()
    {
        return $this->state_id;
    }

    /**
     * Set zip.
     *
     * @param string $zip
     *
     * @return ClientAdditionalContact
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * Get zip.
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Set is_different_address.
     *
     * @param bool $isDifferentAddress
     *
     * @return ClientAdditionalContact
     */
    public function setIsDifferentAddress($isDifferentAddress)
    {
        $this->is_different_address = $isDifferentAddress;

        return $this;
    }

    /**
     * Get is_different_address.
     *
     * @return bool
     */
    public function getIsDifferentAddress()
    {
        return $this->is_different_address;
    }

    /**
     * Set mailing_street.
     *
     * @param string $mailingStreet
     *
     * @return ClientAdditionalContact
     */
    public function setMailingStreet($mailingStreet)
    {
        $this->mailing_street = $mailingStreet;

        return $this;
    }

    /**
     * Get mailing_street.
     *
     * @return string
     */
    public function getMailingStreet()
    {
        return $this->mailing_street;
    }

    /**
     * Set mailing_city.
     *
     * @param string $mailingCity
     *
     * @return ClientAdditionalContact
     */
    public function setMailingCity($mailingCity)
    {
        $this->mailing_city = $mailingCity;

        return $this;
    }

    /**
     * Get mailing_city.
     *
     * @return string
     */
    public function getMailingCity()
    {
        return $this->mailing_city;
    }

    /**
     * Set mailing_state_id.
     *
     * @param int $mailingStateId
     *
     * @return ClientAdditionalContact
     */
    public function setMailingStateId($mailingStateId)
    {
        $this->mailing_state_id = $mailingStateId;

        return $this;
    }

    /**
     * Get mailing_state_id.
     *
     * @return int
     */
    public function getMailingStateId()
    {
        return $this->mailing_state_id;
    }

    /**
     * Set mailing_zip.
     *
     * @param string $mailingZip
     *
     * @return ClientAdditionalContact
     */
    public function setMailingZip($mailingZip)
    {
        $this->mailing_zip = $mailingZip;

        return $this;
    }

    /**
     * Get mailing_zip.
     *
     * @return string
     */
    public function getMailingZip()
    {
        return $this->mailing_zip;
    }

    /**
     * Set birth_date.
     *
     * @param \DateTime $birthDate
     *
     * @return ClientAdditionalContact
     */
    public function setBirthDate($birthDate)
    {
        $this->birth_date = $birthDate;

        return $this;
    }

    /**
     * Get birth_date.
     *
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birth_date;
    }

    /**
     * Set phone_number.
     *
     * @param string $phoneNumber
     *
     * @return ClientAdditionalContact
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phone_number = $phoneNumber;

        return $this;
    }

    /**
     * Get phone_number.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * Set dependents.
     *
     * @param int $dependents
     *
     * @return ClientAdditionalContact
     */
    public function setDependents($dependents)
    {
        $this->dependents = $dependents;

        return $this;
    }

    /**
     * Get dependents.
     *
     * @return int
     */
    public function getDependents()
    {
        return $this->dependents;
    }

    /**
     * Set ssn_tin.
     *
     * @param string $ssnTin
     *
     * @return ClientAdditionalContact
     */
    public function setSsnTin($ssnTin)
    {
        $this->ssn_tin = $ssnTin;

        return $this;
    }

    /**
     * Get ssn_tin.
     *
     * @return string
     */
    public function getSsnTin()
    {
        return $this->ssn_tin;
    }

    /**
     * Set income_source.
     *
     * @param string $incomeSource
     *
     * @return ClientAdditionalContact
     */
    public function setIncomeSource($incomeSource)
    {
        $this->income_source = $incomeSource;

        return $this;
    }

    /**
     * Get income_source.
     *
     * @return string
     */
    public function getIncomeSource()
    {
        return $this->income_source;
    }

    /**
     * Set employer_name.
     *
     * @param string $employerName
     *
     * @return ClientAdditionalContact
     */
    public function setEmployerName($employerName)
    {
        $this->employer_name = $employerName;

        return $this;
    }

    /**
     * Get employer_name.
     *
     * @return string
     */
    public function getEmployerName()
    {
        return $this->employer_name;
    }

    /**
     * Set industry.
     *
     * @param string $industry
     *
     * @return ClientAdditionalContact
     */
    public function setIndustry($industry)
    {
        $this->industry = $industry;

        return $this;
    }

    /**
     * Get industry.
     *
     * @return string
     */
    public function getIndustry()
    {
        return $this->industry;
    }

    /**
     * Set occupation.
     *
     * @param string $occupation
     *
     * @return ClientAdditionalContact
     */
    public function setOccupation($occupation)
    {
        $this->occupation = $occupation;

        return $this;
    }

    /**
     * Get occupation.
     *
     * @return string
     */
    public function getOccupation()
    {
        return $this->occupation;
    }

    /**
     * Set business_type.
     *
     * @param string $businessType
     *
     * @return ClientAdditionalContact
     */
    public function setBusinessType($businessType)
    {
        $this->business_type = $businessType;

        return $this;
    }

    /**
     * Get business_type.
     *
     * @return string
     */
    public function getBusinessType()
    {
        return $this->business_type;
    }

    /**
     * Set employer_address.
     *
     * @param string $employerAddress
     *
     * @return ClientAdditionalContact
     */
    public function setEmployerAddress($employerAddress)
    {
        $this->employer_address = $employerAddress;

        return $this;
    }

    /**
     * Get employer_address.
     *
     * @return string
     */
    public function getEmployerAddress()
    {
        return $this->employer_address;
    }

    /**
     * Set employment_city.
     *
     * @param string $employmentCity
     *
     * @return ClientAdditionalContact
     */
    public function setEmploymentCity($employmentCity)
    {
        $this->employment_city = $employmentCity;

        return $this;
    }

    /**
     * Get employment_city.
     *
     * @return string
     */
    public function getEmploymentCity()
    {
        return $this->employment_city;
    }

    /**
     * Set employment_state_id.
     *
     * @param int $employmentStateId
     *
     * @return ClientAdditionalContact
     */
    public function setEmploymentStateId($employmentStateId)
    {
        $this->employment_state_id = $employmentStateId;

        return $this;
    }

    /**
     * Get employment_state_id.
     *
     * @return int
     */
    public function getEmploymentStateId()
    {
        return $this->employment_state_id;
    }

    /**
     * Set employment_zip.
     *
     * @param string $employmentZip
     *
     * @return ClientAdditionalContact
     */
    public function setEmploymentZip($employmentZip)
    {
        $this->employment_zip = $employmentZip;

        return $this;
    }

    /**
     * Get employment_zip.
     *
     * @return string
     */
    public function getEmploymentZip()
    {
        return $this->employment_zip;
    }

    /**
     * Set is_senior_political_figure.
     *
     * @param bool $isSeniorPoliticalFigure
     *
     * @return ClientAdditionalContact
     */
    public function setIsSeniorPoliticalFigure($isSeniorPoliticalFigure)
    {
        $this->is_senior_political_figure = $isSeniorPoliticalFigure;

        return $this;
    }

    /**
     * Get is_senior_political_figure.
     *
     * @return bool
     */
    public function getIsSeniorPoliticalFigure()
    {
        return $this->is_senior_political_figure;
    }

    /**
     * Set senior_spf_name.
     *
     * @param string $seniorSpfName
     *
     * @return ClientAdditionalContact
     */
    public function setSeniorSpfName($seniorSpfName)
    {
        $this->senior_spf_name = $seniorSpfName;

        return $this;
    }

    /**
     * Get senior_spf_name.
     *
     * @return string
     */
    public function getSeniorSpfName()
    {
        return $this->senior_spf_name;
    }

    /**
     * Set senior_political_title.
     *
     * @param string $seniorPoliticalTitle
     *
     * @return ClientAdditionalContact
     */
    public function setSeniorPoliticalTitle($seniorPoliticalTitle)
    {
        $this->senior_political_title = $seniorPoliticalTitle;

        return $this;
    }

    /**
     * Get senior_political_title.
     *
     * @return string
     */
    public function getSeniorPoliticalTitle()
    {
        return $this->senior_political_title;
    }

    /**
     * Set senior_account_owner_relationship.
     *
     * @param string $seniorAccountOwnerRelationship
     *
     * @return ClientAdditionalContact
     */
    public function setSeniorAccountOwnerRelationship($seniorAccountOwnerRelationship)
    {
        $this->senior_account_owner_relationship = $seniorAccountOwnerRelationship;

        return $this;
    }

    /**
     * Get senior_account_owner_relationship.
     *
     * @return string
     */
    public function getSeniorAccountOwnerRelationship()
    {
        return $this->senior_account_owner_relationship;
    }

    /**
     * Set senior_country_office.
     *
     * @param string $seniorCountryOffice
     *
     * @return ClientAdditionalContact
     */
    public function setSeniorCountryOffice($seniorCountryOffice)
    {
        $this->senior_country_office = $seniorCountryOffice;

        return $this;
    }

    /**
     * Get senior_country_office.
     *
     * @return string
     */
    public function getSeniorCountryOffice()
    {
        return $this->senior_country_office;
    }

    /**
     * Set is_publicly_traded_company.
     *
     * @param bool $isPubliclyTradedCompany
     *
     * @return ClientAdditionalContact
     */
    public function setIsPubliclyTradedCompany($isPubliclyTradedCompany)
    {
        $this->is_publicly_traded_company = $isPubliclyTradedCompany;

        return $this;
    }

    /**
     * Get is_publicly_traded_company.
     *
     * @return bool
     */
    public function getIsPubliclyTradedCompany()
    {
        return $this->is_publicly_traded_company;
    }

    /**
     * Set publicle_company_name.
     *
     * @param string $publicleCompanyName
     *
     * @return ClientAdditionalContact
     */
    public function setPublicleCompanyName($publicleCompanyName)
    {
        $this->publicle_company_name = $publicleCompanyName;

        return $this;
    }

    /**
     * Get publicle_company_name.
     *
     * @return string
     */
    public function getPublicleCompanyName()
    {
        return $this->publicle_company_name;
    }

    /**
     * Set publicle_address.
     *
     * @param string $publicleAddress
     *
     * @return ClientAdditionalContact
     */
    public function setPublicleAddress($publicleAddress)
    {
        $this->publicle_address = $publicleAddress;

        return $this;
    }

    /**
     * Get publicle_address.
     *
     * @return string
     */
    public function getPublicleAddress()
    {
        return $this->publicle_address;
    }

    /**
     * Set publicle_city.
     *
     * @param string $publicleCity
     *
     * @return ClientAdditionalContact
     */
    public function setPublicleCity($publicleCity)
    {
        $this->publicle_city = $publicleCity;

        return $this;
    }

    /**
     * Get publicle_city.
     *
     * @return string
     */
    public function getPublicleCity()
    {
        return $this->publicle_city;
    }

    /**
     * Set publicle_state_id.
     *
     * @param int $publicleStateId
     *
     * @return ClientAdditionalContact
     */
    public function setPublicleStateId($publicleStateId)
    {
        $this->publicle_state_id = $publicleStateId;

        return $this;
    }

    /**
     * Get publicle_state_id.
     *
     * @return int
     */
    public function getPublicleStateId()
    {
        return $this->publicle_state_id;
    }

    /**
     * Set is_broker_security_exchange_person.
     *
     * @param bool $isBrokerSecurityExchangePerson
     *
     * @return ClientAdditionalContact
     */
    public function setIsBrokerSecurityExchangePerson($isBrokerSecurityExchangePerson)
    {
        $this->is_broker_security_exchange_person = $isBrokerSecurityExchangePerson;

        return $this;
    }

    /**
     * Get is_broker_security_exchange_person.
     *
     * @return bool
     */
    public function getIsBrokerSecurityExchangePerson()
    {
        return $this->is_broker_security_exchange_person;
    }

    /**
     * Set broker_security_exchange_company_name.
     *
     * @param string $brokerSecurityExchangeCompanyName
     *
     * @return ClientAdditionalContact
     */
    public function setBrokerSecurityExchangeCompanyName($brokerSecurityExchangeCompanyName)
    {
        $this->broker_security_exchange_company_name = $brokerSecurityExchangeCompanyName;

        return $this;
    }

    /**
     * Get broker_security_exchange_company_name.
     *
     * @return string
     */
    public function getBrokerSecurityExchangeCompanyName()
    {
        return $this->broker_security_exchange_company_name;
    }

    /**
     * Set broker_security_exchange_compliance_letter.
     *
     * @param string $brokerSecurityExchangeComplianceLetter
     *
     * @return ClientAdditionalContact
     */
    public function setBrokerSecurityExchangeComplianceLetter($brokerSecurityExchangeComplianceLetter)
    {
        $this->broker_security_exchange_compliance_letter = $brokerSecurityExchangeComplianceLetter;

        return $this;
    }

    /**
     * Get broker_security_exchange_compliance_letter.
     *
     * @return string
     */
    public function getBrokerSecurityExchangeComplianceLetter()
    {
        return $this->broker_security_exchange_compliance_letter;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return ClientAdditionalContact
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set state.
     *
     * @param \App\Entity\State $state
     *
     * @return ClientAdditionalContact
     */
    public function setState(State $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return \App\Entity\State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set mailingState.
     *
     * @param \App\Entity\State $mailingState
     *
     * @return ClientAdditionalContact
     */
    public function setMailingState(State $mailingState = null)
    {
        $this->mailingState = $mailingState;

        return $this;
    }

    /**
     * Get mailingState.
     *
     * @return \App\Entity\State
     */
    public function getMailingState()
    {
        return $this->mailingState;
    }

    /**
     * Set employmentState.
     *
     * @param \App\Entity\State $employmentState
     *
     * @return ClientAdditionalContact
     */
    public function setEmploymentState(State $employmentState = null)
    {
        $this->employmentState = $employmentState;

        return $this;
    }

    /**
     * Get employmentState.
     *
     * @return \App\Entity\State
     */
    public function getEmploymentState()
    {
        return $this->employmentState;
    }

    /**
     * Set publicleState.
     *
     * @param \App\Entity\State $publicleState
     *
     * @return ClientAdditionalContact
     */
    public function setPublicleState(State $publicleState = null)
    {
        $this->publicleState = $publicleState;

        return $this;
    }

    /**
     * Get publicleState.
     *
     * @return \App\Entity\State
     */
    public function getPublicleState()
    {
        return $this->publicleState;
    }

    /**
     * Set compliance_letter_file.
     *
     * @param $complianceLetterFile
     *
     * @return PersonalInformation
     */
    public function setComplianceLetterFile($complianceLetterFile)
    {
        $this->compliance_letter_file = $complianceLetterFile;

        return $this;
    }

    /**
     * Get compliance_letter_file.
     *
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getComplianceLetterFile()
    {
        return $this->compliance_letter_file;
    }

    public function getAbsoluteComplianceLetter()
    {
        return null === $this->getBrokerSecurityExchangeComplianceLetter() ? null : $this->getUploadRootDir().'/'.$this->getBrokerSecurityExchangeComplianceLetter();
    }

    public function getWebComplianceLetter()
    {
        return null === $this->getBrokerSecurityExchangeComplianceLetter() ? null : $this->getUploadDir().'/'.$this->getBrokerSecurityExchangeComplianceLetter();
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return getcwd().'/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/client_compliance_letter_files';
    }

    public function preUpload()
    {
        if (null !== $this->compliance_letter_file) {
            // do whatever you want to generate a unique name
            $this->broker_security_exchange_compliance_letter = sha1(uniqid(mt_rand(), true)).'.'.$this->compliance_letter_file->guessExtension();
        }
    }

    public function upload()
    {
        if (null === $this->compliance_letter_file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->compliance_letter_file->move($this->getUploadRootDir(), $this->broker_security_exchange_compliance_letter);

        unset($this->compliance_letter_file);
    }

    public function removeUpload()
    {
        if ($file = $this->getAbsoluteComplianceLetter()) {
            unlink($file);
        }
    }

    /**
     * @var string
     */
    private $email;

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return ClientAdditionalContact
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return ClientAdditionalContact
     */
    public function setType($type)
    {
        parent::setType($type);

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return parent::getType();
    }

    /**
     * Set relationship.
     *
     * @param string $relationship
     *
     * @return ClientAdditionalContact
     */
    public function setRelationship($relationship)
    {
        $this->relationship = $relationship;

        return $this;
    }

    /**
     * Get relationship.
     *
     * @return string
     */
    public function getRelationship()
    {
        return $this->relationship;
    }

    /**
     * Set employment_type.
     *
     * @param string $employmentType
     *
     * @return ClientAdditionalContact
     */
    public function setEmploymentType($employmentType)
    {
        $this->employment_type = $employmentType;

        return $this;
    }

    /**
     * Get employment_type.
     *
     * @return string
     */
    public function getEmploymentType()
    {
        return $this->employment_type;
    }

    /**
     * Set marital status.
     *
     * @param string $maritalStatus
     *
     * @return $this|AccountOwnerInterface
     */
    public function setMaritalStatus($maritalStatus)
    {
        $this->marital_status = $maritalStatus;

        return $this;
    }

    /**
     * Get marital status.
     *
     * @return string
     */
    public function getMaritalStatus()
    {
        return $this->marital_status;
    }

    /**
     * Set spouse_first_name.
     *
     * @param string $spouseFirstName
     *
     * @return ClientAdditionalContact
     */
    public function setSpouseFirstName($spouseFirstName)
    {
        $this->spouse_first_name = $spouseFirstName;

        return $this;
    }

    /**
     * Get spouse_first_name.
     *
     * @return string
     */
    public function getSpouseFirstName()
    {
        return $this->spouse_first_name;
    }

    /**
     * Set spouse_middle_name.
     *
     * @param string $spouseMiddleName
     *
     * @return ClientAdditionalContact
     */
    public function setSpouseMiddleName($spouseMiddleName)
    {
        $this->spouse_middle_name = $spouseMiddleName;

        return $this;
    }

    /**
     * Get spouse_middle_name.
     *
     * @return string
     */
    public function getSpouseMiddleName()
    {
        return $this->spouse_middle_name;
    }

    /**
     * Set spouse_last_name.
     *
     * @param string $spouseLastName
     *
     * @return ClientAdditionalContact
     */
    public function setSpouseLastName($spouseLastName)
    {
        $this->spouse_last_name = $spouseLastName;

        return $this;
    }

    /**
     * Get spouse_last_name.
     *
     * @return string
     */
    public function getSpouseLastName()
    {
        return $this->spouse_last_name;
    }

    /**
     * Set spouse_birth_date.
     *
     * @param \DateTime $spouseBirthDate
     *
     * @return ClientAdditionalContact
     */
    public function setSpouseBirthDate($spouseBirthDate)
    {
        $this->spouse_birth_date = $spouseBirthDate;

        return $this;
    }

    /**
     * Get spouse_birth_date.
     *
     * @return \DateTime
     */
    public function getSpouseBirthDate()
    {
        return $this->spouse_birth_date;
    }

    /**
     * Returns object to save for entity manager.
     *
     * @return $this
     */
    public function getObjectToSave()
    {
        return $this;
    }
}
