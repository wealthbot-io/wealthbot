<?php

namespace Wealthbot\AdminBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="demographics")
 */
class Portfolio
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String(name="company_name")
     */
    protected $companyName;

    /**
     * @MongoDB\String(name="last_name")
     */
    protected $lastName;

    /**
     * @MongoDB\String(name="first_name")
     */
    protected $firstName;

    /**
     * @MongoDB\String(name="street")
     */
    protected $street;

    /**
     * @MongoDB\String(name="address_2")
     */
    protected $address2;

    /**
     * @MongoDB\String(name="address_3")
     */
    protected $address3;

    /**
     * @MongoDB\String(name="address_4")
     */
    protected $address4;

    /**
     * @MongoDB\String(name="address_5")
     */
    protected $address5;

    /**
     * @MongoDB\String(name="address_6")
     */
    protected $address6;

    /**
     * @MongoDB\String(name="city")
     */
    protected $city;

    /**
     * @MongoDB\String(name="state")
     */
    protected $state;

    /**
     * @MongoDB\String(name="zipcode")
     */
    protected $zipcode;

    /**
     * @MongoDB\String(name="ssn")
     */
    protected $ssn;

    /**
     * @MongoDB\String(name="account_number")
     */
    protected $accountNumber;

    /**
     * @MongoDB\String(name="advisor_id")
     */
    protected $advisorId;

    /**
     * @MongoDB\String(name="taxable")
     */
    protected $taxable;

    /**
     * @MongoDB\String(name="phone_number")
     */
    protected $phoneNumber;

    /**
     * @MongoDB\String(name="fax_number")
     */
    protected $faxNumber;

    /**
     * @MongoDB\String(name="account_type")
     */
    protected $accountType;

    /**
     * @MongoDB\String(name="objective")
     */
    protected $objective;

    /**
     * @MongoDB\String(name="billing_account_number")
     */
    protected $billingAccountNumber;

    /**
     * @MongoDB\String(name="default_account")
     */
    protected $defaultAccount;

    /**
     * @MongoDB\String(name="state_of_primary_residence")
     */
    protected $stateOfPrimaryResidence;

    /**
     * @MongoDB\String(name="performance_inception_date")
     */
    protected $performanceInceptionDate;

    /**
     * @MongoDB\String(name="billing_inception_date")
     */
    protected $billingInceptionDate;

    /**
     * @MongoDB\String(name="federal_tax_rate")
     */
    protected $federalTaxRate;

    /**
     * @MongoDB\String(name="state_tax_rate")
     */
    protected $stateTaxRate;

    /**
     * @MongoDB\String(name="months_in_short_term_holding_period")
     */
    protected $monthsInShortTermHoldingPeriod;

    /**
     * @MongoDB\String(name="fiscal_year_end")
     */
    protected $fiscalYearEnd;

    /**
     * @MongoDB\String(name="use_average_cost_accounting")
     */
    protected $useAverageCostAccounting;

    /**
     * @MongoDB\String(name="display_accrued_interest")
     */
    protected $displayAccruedInterest;

    /**
     * @MongoDB\String(name="display_accrued_dividends")
     */
    protected $displayAccruedDividends;

    /**
     * @MongoDB\String(name="display_accrued_gains")
     */
    protected $displayAccruedGains;

    /**
     * @MongoDB\String(name="birth_date")
     */
    protected $birthDate;

    /**
     * @MongoDB\String(name="discount_rate")
     */
    protected $discountRate;

    /**
     * @MongoDB\String(name="payout_rate")
     */
    protected $payoutRate;

    /**
     * @MongoDB\String(name="created")
     */
    protected $created;

    /**
     * @MongoDB\String(name="import_date")
     */
    protected $importDate;

    /**
     * @MongoDB\String(name="source")
     */
    protected $source;

    /**
     * @param mixed $accountNumber
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return mixed
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param mixed $accountType
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
    }

    /**
     * @return mixed
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param mixed $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return mixed
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param mixed $address3
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    /**
     * @return mixed
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param mixed $address4
     */
    public function setAddress4($address4)
    {
        $this->address4 = $address4;
    }

    /**
     * @return mixed
     */
    public function getAddress4()
    {
        return $this->address4;
    }

    /**
     * @param mixed $address5
     */
    public function setAddress5($address5)
    {
        $this->address5 = $address5;
    }

    /**
     * @return mixed
     */
    public function getAddress5()
    {
        return $this->address5;
    }

    /**
     * @param mixed $address6
     */
    public function setAddress6($address6)
    {
        $this->address6 = $address6;
    }

    /**
     * @return mixed
     */
    public function getAddress6()
    {
        return $this->address6;
    }

    /**
     * @param mixed $advisorId
     */
    public function setAdvisorId($advisorId)
    {
        $this->advisorId = $advisorId;
    }

    /**
     * @return mixed
     */
    public function getAdvisorId()
    {
        return $this->advisorId;
    }

    /**
     * @param mixed $billingAccountNumber
     */
    public function setBillingAccountNumber($billingAccountNumber)
    {
        $this->billingAccountNumber = $billingAccountNumber;
    }

    /**
     * @return mixed
     */
    public function getBillingAccountNumber()
    {
        return $this->billingAccountNumber;
    }

    /**
     * @param mixed $billingInceptionDate
     */
    public function setBillingInceptionDate($billingInceptionDate)
    {
        $this->billingInceptionDate = $billingInceptionDate;
    }

    /**
     * @return mixed
     */
    public function getBillingInceptionDate()
    {
        return $this->billingInceptionDate;
    }

    /**
     * @param mixed $birthDate
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $companyName
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;
    }

    /**
     * @return mixed
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $defaultAccount
     */
    public function setDefaultAccount($defaultAccount)
    {
        $this->defaultAccount = $defaultAccount;
    }

    /**
     * @return mixed
     */
    public function getDefaultAccount()
    {
        return $this->defaultAccount;
    }

    /**
     * @param mixed $discountRate
     */
    public function setDiscountRate($discountRate)
    {
        $this->discountRate = $discountRate;
    }

    /**
     * @return mixed
     */
    public function getDiscountRate()
    {
        return $this->discountRate;
    }

    /**
     * @param mixed $displayAccruedDividends
     */
    public function setDisplayAccruedDividends($displayAccruedDividends)
    {
        $this->displayAccruedDividends = $displayAccruedDividends;
    }

    /**
     * @return mixed
     */
    public function getDisplayAccruedDividends()
    {
        return $this->displayAccruedDividends;
    }

    /**
     * @param mixed $displayAccruedGains
     */
    public function setDisplayAccruedGains($displayAccruedGains)
    {
        $this->displayAccruedGains = $displayAccruedGains;
    }

    /**
     * @return mixed
     */
    public function getDisplayAccruedGains()
    {
        return $this->displayAccruedGains;
    }

    /**
     * @param mixed $displayAccruedInterest
     */
    public function setDisplayAccruedInterest($displayAccruedInterest)
    {
        $this->displayAccruedInterest = $displayAccruedInterest;
    }

    /**
     * @return mixed
     */
    public function getDisplayAccruedInterest()
    {
        return $this->displayAccruedInterest;
    }

    /**
     * @param mixed $faxNumber
     */
    public function setFaxNumber($faxNumber)
    {
        $this->faxNumber = $faxNumber;
    }

    /**
     * @return mixed
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    /**
     * @param mixed $federalTaxRate
     */
    public function setFederalTaxRate($federalTaxRate)
    {
        $this->federalTaxRate = $federalTaxRate;
    }

    /**
     * @return mixed
     */
    public function getFederalTaxRate()
    {
        return $this->federalTaxRate;
    }

    /**
     * @param mixed $fiscalYearEnd
     */
    public function setFiscalYearEnd($fiscalYearEnd)
    {
        $this->fiscalYearEnd = $fiscalYearEnd;
    }

    /**
     * @return mixed
     */
    public function getFiscalYearEnd()
    {
        return $this->fiscalYearEnd;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $importDate
     */
    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
    }

    /**
     * @return mixed
     */
    public function getImportDate()
    {
        return $this->importDate;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $monthsInShortTermHoldingPeriod
     */
    public function setMonthsInShortTermHoldingPeriod($monthsInShortTermHoldingPeriod)
    {
        $this->monthsInShortTermHoldingPeriod = $monthsInShortTermHoldingPeriod;
    }

    /**
     * @return mixed
     */
    public function getMonthsInShortTermHoldingPeriod()
    {
        return $this->monthsInShortTermHoldingPeriod;
    }

    /**
     * @param mixed $objective
     */
    public function setObjective($objective)
    {
        $this->objective = $objective;
    }

    /**
     * @return mixed
     */
    public function getObjective()
    {
        return $this->objective;
    }

    /**
     * @param mixed $payoutRate
     */
    public function setPayoutRate($payoutRate)
    {
        $this->payoutRate = $payoutRate;
    }

    /**
     * @return mixed
     */
    public function getPayoutRate()
    {
        return $this->payoutRate;
    }

    /**
     * @param mixed $performanceInceptionDate
     */
    public function setPerformanceInceptionDate($performanceInceptionDate)
    {
        $this->performanceInceptionDate = $performanceInceptionDate;
    }

    /**
     * @return mixed
     */
    public function getPerformanceInceptionDate()
    {
        return $this->performanceInceptionDate;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $ssn
     */
    public function setSsn($ssn)
    {
        $this->ssn = $ssn;
    }

    /**
     * @return mixed
     */
    public function getSsn()
    {
        return $this->ssn;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $stateOfPrimaryResidence
     */
    public function setStateOfPrimaryResidence($stateOfPrimaryResidence)
    {
        $this->stateOfPrimaryResidence = $stateOfPrimaryResidence;
    }

    /**
     * @return mixed
     */
    public function getStateOfPrimaryResidence()
    {
        return $this->stateOfPrimaryResidence;
    }

    /**
     * @param mixed $stateTaxRate
     */
    public function setStateTaxRate($stateTaxRate)
    {
        $this->stateTaxRate = $stateTaxRate;
    }

    /**
     * @return mixed
     */
    public function getStateTaxRate()
    {
        return $this->stateTaxRate;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $taxable
     */
    public function setTaxable($taxable)
    {
        $this->taxable = $taxable;
    }

    /**
     * @return mixed
     */
    public function getTaxable()
    {
        return $this->taxable;
    }

    /**
     * @param mixed $useAverageCostAccounting
     */
    public function setUseAverageCostAccounting($useAverageCostAccounting)
    {
        $this->useAverageCostAccounting = $useAverageCostAccounting;
    }

    /**
     * @return mixed
     */
    public function getUseAverageCostAccounting()
    {
        return $this->useAverageCostAccounting;
    }

    /**
     * @param mixed $zipcode
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }

    /**
     * @return mixed
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }
}
