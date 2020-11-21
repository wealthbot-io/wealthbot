<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Model\PersonalInformation as BasePersonalInformation;

/**
 * Class PersonalInformation
 * @package App\Entity
 */
class PersonalInformation extends BasePersonalInformation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @param \App\Entity\User
     */
    private $client;

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
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return PersonalInformation
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
     * @var int
     */
    private $client_id;

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
    protected $income_source;

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
    private $city;

    /**
     * @var int
     */
    private $state_id;

    /**
     * @var string
     */
    private $zipcode;

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
    protected $broker_security_exchange_compliance_letter;

    /**
     * @var UploadedFile
     */
    private $compliance_letter_file;

    public function __construct()
    {
        $this->is_senior_political_figure = false;
        $this->is_publicly_traded_company = false;
        $this->is_broker_security_exchange_person = false;
    }

    /**
     * Set client_id.
     *
     * @param int $clientId
     *
     * @return PersonalInformation
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
     * Set dependents.
     *
     * @param int $dependents
     *
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
     */
    public function setIncomeSource($incomeSource)
    {
        return parent::setIncomeSource($incomeSource);
    }

    /**
     * Get income_source.
     *
     * @return string
     */
    public function getIncomeSource()
    {
        return parent::getIncomeSource();
    }

    /**
     * Set employer_name.
     *
     * @param string $employerName
     *
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * Set city.
     *
     * @param string $city
     *
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * Set zipcode.
     *
     * @param string $zipcode
     *
     * @return PersonalInformation
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode.
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set is_senior_political_figure.
     *
     * @param bool $isSeniorPoliticalFigure
     *
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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
     * @return PersonalInformation
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

    /**
     * @param \App\Entity\State
     */
    private $state;

    /**
     * Set state.
     *
     * @param \App\Entity\State $state
     *
     * @return PersonalInformation
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
     * @param \App\Entity\State
     */
    private $publicleState;

    /**
     * Set publicleState.
     *
     * @param \App\Entity\State $publicleState
     *
     * @return PersonalInformation
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
            $oldFile = $this->getAbsoluteComplianceLetter();
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }

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
}
