<?php

namespace App\Entity;

/**
 * Class RetirementPlanInformation
 * @package App\Entity
 */
class RetirementPlanInformation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $account_id;

    /**
     * @var string
     */
    private $financial_institution;

    /**
     * @var string
     */
    private $account_number;

    /**
     * @var string
     */
    private $account_description;

    /**
     * @var string
     */
    private $web_address_login;

    /**
     * @var string
     */
    private $username;

    /**
     * @param \App\Entity\ClientAccount
     */
    private $account;

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
     * Set account_id.
     *
     * @param int $accountId
     *
     * @return RetirementPlanInformation
     */
    public function setAccountId($accountId)
    {
        $this->account_id = $accountId;

        return $this;
    }

    /**
     * Get account_id.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Set account_number.
     *
     * @param string $accountNumber
     *
     * @return RetirementPlanInformation
     */
    public function setAccountNumber($accountNumber)
    {
        $this->account_number = $accountNumber;

        return $this;
    }

    /**
     * Get account_number.
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->account_number;
    }

    /**
     * Set account_description.
     *
     * @param string $accountDescription
     *
     * @return RetirementPlanInformation
     */
    public function setAccountDescription($accountDescription)
    {
        $this->account_description = $accountDescription;

        return $this;
    }

    /**
     * Get account_description.
     *
     * @return string
     */
    public function getAccountDescription()
    {
        return $this->account_description;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return RetirementPlanInformation
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set account.
     *
     * @param \App\Entity\ClientAccount $account
     *
     * @return RetirementPlanInformation
     */
    public function setAccount(ClientAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \App\Entity\ClientAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set financial_institution.
     *
     * @param string $financialInstitution
     *
     * @return RetirementPlanInformation
     */
    public function setFinancialInstitution($financialInstitution)
    {
        $this->financial_institution = $financialInstitution;

        return $this;
    }

    /**
     * Get financial_institution.
     *
     * @return string
     */
    public function getFinancialInstitution()
    {
        return $this->financial_institution;
    }

    /**
     * Set web_address_login.
     *
     * @param string $webAddressLogin
     *
     * @return RetirementPlanInformation
     */
    public function setWebAddressLogin($webAddressLogin)
    {
        $this->web_address_login = $webAddressLogin;

        return $this;
    }

    /**
     * Get web_address_login.
     *
     * @return string
     */
    public function getWebAddressLogin()
    {
        return $this->web_address_login;
    }

    /**
     * @var string
     */
    private $password;

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return RetirementPlanInformation
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}
