<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.05.13
 * Time: 15:01
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Entity\State;

interface AccountOwnerInterface
{
    /**
     * Get owner id.
     *
     * @return int
     */
    public function getId();

    /**
     * Get owner first name.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set owner first name.
     *
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName($firstName);

    /**
     * Get owner middle name.
     *
     * @return string
     */
    public function getMiddleName();

    /**
     * Set owner middle name.
     *
     * @param string $middleName
     *
     * @return self
     */
    public function setMiddleName($middleName);

    /**
     * Get owner last name.
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set owner last name.
     *
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName($lastName);

    /**
     * Get owner full name.
     *
     * @return string
     */
    public function getFullName();

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail($email);

    public function getType();

    /**
     * Get street.
     *
     * @return string
     */
    public function getStreet();

    /**
     * Set street.
     *
     * @param string $street
     *
     * @return self
     */
    public function setStreet($street);

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity();

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return self
     */
    public function setCity($city);

    /**
     * Get state.
     *
     * @return State|null
     */
    public function getState();

    /**
     * Set state.
     *
     * @param State $state
     *
     * @return self
     */
    public function setState(\App\Entity\State $state);

    /**
     * Get zip code.
     *
     * @return string
     */
    public function getZip();

    /**
     * Set zip code.
     *
     * @param string $zip
     *
     * @return self
     */
    public function setZip($zip);

    /**
     * Returns true if mailing address is different and false otherwise.
     *
     * @return bool
     */
    public function getIsDifferentAddress();

    /**
     * Set is mailing address is different.
     *
     * @param bool $isDifferentAddress
     *
     * @return self
     */
    public function setIsDifferentAddress($isDifferentAddress);

    /**
     * Get mailing street.
     *
     * @return string
     */
    public function getMailingStreet();

    /**
     * Set mailing street.
     *
     * @param string $mailingStreet
     *
     * @return self
     */
    public function setMailingStreet($mailingStreet);

    /**
     * Get mailing city.
     *
     * @return string
     */
    public function getMailingCity();

    /**
     * Set mailing city.
     *
     * @param string $mailingCity
     *
     * @return self
     */
    public function setMailingCity($mailingCity);

    /**
     * Get mailing state.
     *
     * @return State
     */
    public function getMailingState();

    /**
     * Set mailing state.
     *
     * @param State $mailingState
     *
     * @return self
     */
    public function setMailingState(\App\Entity\State $mailingState = null);

    /**
     * Get mailing zip code.
     *
     * @return string
     */
    public function getMailingZip();

    /**
     * Set mailing zip code.
     *
     * @param string $mailingZip
     *
     * @return self
     */
    public function setMailingZip($mailingZip);

    /**
     * Get date of birth.
     *
     * @return \DateTime
     */
    public function getBirthDate();

    /**
     * Set date of birth.
     *
     * @param \DateTime $birthDate
     *
     * @return self
     */
    public function setBirthDate($birthDate);

    /**
     * Get phone number.
     *
     * @return string
     */
    public function getPhoneNumber();

    /**
     * Set phone number.
     *
     * @param string $phoneNumber
     *
     * @return self
     */
    public function setPhoneNumber($phoneNumber);

    public function setDependents($dependents);

    public function getDependents();

    /**
     * Set social security number.
     *
     * @param string $ssnTin
     *
     * @return self
     */
    public function setSsnTin($ssnTin);

    /**
     * Get social security number.
     *
     * @return string
     */
    public function getSsnTin();

    /**
     * Set source of income.
     *
     * @param string $incomeSource
     *
     * @return self
     */
    public function setIncomeSource($incomeSource);

    /**
     * Get source of income.
     *
     * @return string
     */
    public function getIncomeSource();

    /**
     * Set employer name.
     *
     * @param string $employerName
     *
     * @return self
     */
    public function setEmployerName($employerName);

    /**
     * Get employer name.
     *
     * @return string
     */
    public function getEmployerName();

    /**
     * Set industry.
     *
     * @param string $industry
     *
     * @return self
     */
    public function setIndustry($industry);

    /**
     * Get industry.
     *
     * @return string
     */
    public function getIndustry();

    /**
     * Set occupation.
     *
     * @param string $occupation
     *
     * @return self
     */
    public function setOccupation($occupation);

    /**
     * Get occupation.
     *
     * @return string
     */
    public function getOccupation();

    /**
     * Set type of business.
     *
     * @param string $businessType
     *
     * @return self
     */
    public function setBusinessType($businessType);

    /**
     * Get type of business.
     *
     * @return string
     */
    public function getBusinessType();

    /**
     * Set employer address.
     *
     * @param string $employerAddress
     *
     * @return self
     */
    public function setEmployerAddress($employerAddress);

    /**
     * Get employer address.
     *
     * @return string
     */
    public function getEmployerAddress();

    /**
     * Set employment city.
     *
     * @param string $employmentCity
     *
     * @return self
     */
    public function setEmploymentCity($employmentCity);

    /**
     * Get employment city.
     *
     * @return string
     */
    public function getEmploymentCity();

    /**
     * Set employment zip code.
     *
     * @param string $employmentZip
     *
     * @return self
     */
    public function setEmploymentZip($employmentZip);

    /**
     * Get employment zip code.
     *
     * @return string
     */
    public function getEmploymentZip();

    /**
     * Get employment state.
     *
     * @return State
     */
    public function getEmploymentState();

    /**
     * Set employment state.
     *
     * @param State $state
     *
     * @return self
     */
    public function setEmploymentState(\App\Entity\State $state = null);

    public function setIsSeniorPoliticalFigure($isSeniorPoliticalFigure);

    public function getIsSeniorPoliticalFigure();

    public function setSeniorSpfName($seniorSpfName);

    public function getSeniorSpfName();

    public function setSeniorPoliticalTitle($seniorPoliticalTitle);

    public function getSeniorPoliticalTitle();

    public function setSeniorAccountOwnerRelationship($seniorAccountOwnerRelationship);

    public function getSeniorAccountOwnerRelationship();

    public function setSeniorCountryOffice($seniorCountryOffice);

    public function getSeniorCountryOffice();

    public function setIsPubliclyTradedCompany($isPubliclyTradedCompany);

    public function getIsPubliclyTradedCompany();

    public function setPublicleCompanyName($publicleCompanyName);

    public function getPublicleCompanyName();

    public function setPublicleAddress($publicleAddress);

    public function getPublicleAddress();

    public function setPublicleCity($publicleCity);

    public function getPublicleCity();

    public function setPublicleState(\App\Entity\State $publicleState = null);

    public function getPublicleState();

    public function setIsBrokerSecurityExchangePerson($isBrokerSecurityExchangePerson);

    public function getIsBrokerSecurityExchangePerson();

    public function setBrokerSecurityExchangeCompanyName($brokerSecurityExchangeCompanyName);

    public function getBrokerSecurityExchangeCompanyName();

    public function setBrokerSecurityExchangeComplianceLetter($brokerSecurityExchangeComplianceLetter);

    public function getBrokerSecurityExchangeComplianceLetter();

    public function setComplianceLetterFile($complianceLetterFile);

    public function getComplianceLetterFile();

    public function setEmploymentType($employmentType);

    public function getEmploymentType();

    /**
     * Set marital status.
     *
     * @param string $maritalStatus
     *
     * @return self
     */
    public function setMaritalStatus($maritalStatus);

    /**
     * Get marital status.
     *
     * @return string
     */
    public function getMaritalStatus();

    /**
     * Set first name of spouse.
     *
     * @param string $spouseFirstName
     *
     * @return self
     */
    public function setSpouseFirstName($spouseFirstName);

    /**
     * Get first name of spouse.
     *
     * @return string
     */
    public function getSpouseFirstName();

    /**
     * Set middle name of spouse.
     *
     * @param string $spouseMiddleName
     *
     * @return self
     */
    public function setSpouseMiddleName($spouseMiddleName);

    /**
     * Get middle name of spouse.
     *
     * @return string
     */
    public function getSpouseMiddleName();

    /**
     * Set last name of spouse.
     *
     * @param string $spouseLastName
     *
     * @return self
     */
    public function setSpouseLastName($spouseLastName);

    /**
     * Get last name of spouse.
     *
     * @return string
     */
    public function getSpouseLastName();

    /**
     * Set spouse_birth_date.
     *
     * @param \DateTime $spouseBirthDate
     *
     * @return self
     */
    public function setSpouseBirthDate($spouseBirthDate);

    /**
     * Get spouse_birth_date.
     *
     * @return \DateTime
     */
    public function getSpouseBirthDate();

    /**
     * Returns object to save for entity manager.
     *
     * @return mixed
     */
    public function getObjectToSave();
}
