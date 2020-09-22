<?php

namespace App\Model;

class TransferInformation
{
    /**
     * @var string
     */
    protected $account_type;

    // ENUM values account_type column
    const ACCOUNT_TYPE_PERSONAL_INVESTMENT_ACCOUNT = 'Personal Investment Account';
    const ACCOUNT_TYPE_JOINT_ACCOUNT = 'Joint Account';
    const ACCOUNT_TYPE_ROTH_IRA = 'Roth IRA';
    const ACCOUNT_TYPE_TRADITIONAL_ROLLOVER_IRA = 'Traditional/Rollover IRA';

    private static $_accountTypeValues = null;

    /**
     * @var int
     */
    protected $transfer_from;

    const TRANSFER_FROM_BROKERAGE_FIRM = 1;
    const TRANSFER_FROM_MUTUAL_FUND_COMPANY = 2;
    const TRANSFER_FROM_BANK = 3;
    const TRANSFER_FROM_DEPOSIT_CERTIFICATES = 4;

    private static $_transferFromValues = [
        self::TRANSFER_FROM_BROKERAGE_FIRM => 'Transfer from a brokerage firm',
        self::TRANSFER_FROM_MUTUAL_FUND_COMPANY => 'Transfer from a Mutual Fund Company',
        self::TRANSFER_FROM_BANK => 'Transfer from a Bank, Insurance/Annuity Co, Trust Co, or Transfer Agent',
        self::TRANSFER_FROM_DEPOSIT_CERTIFICATES => 'Certificates of Deposit (CD’s)',
    ];

    /**
     * @var int
     */
    protected $insurance_policy_type;

    const INSURANCE_POLICY_TYPE_TERMINATE_CONTACT_POLICY = 1;
    const INSURANCE_POLICY_TYPE_TRANSFER_PENALTY_FREE_AMOUNT = 2;
    const INSURANCE_POLICY_TYPE_TRANSFER_PENALTY_FREE = 3;

    private static $_insurancePolicyTypeValues = [
        self::INSURANCE_POLICY_TYPE_TERMINATE_CONTACT_POLICY => 'I agree to redeem and terminate the entire contract of policy on my behalf. I understand that penalties may apply.',
        self::INSURANCE_POLICY_TYPE_TRANSFER_PENALTY_FREE_AMOUNT => 'Please transfer penalty-free amount only, which is',
        self::INSURANCE_POLICY_TYPE_TRANSFER_PENALTY_FREE => 'Check here if the entire account is penalty free.',
    ];

    /**
     * Get array ENUM values account_type column.
     *
     * @static
     *
     * @return array
     */
    public static function getAccountTypeChoices()
    {
        // Build $_statusValues if this is the first call
        if (null === self::$_accountTypeValues) {
            self::$_accountTypeValues = [];
            $oClass = new \ReflectionClass('\App\Model\TransferInformation');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'ACCOUNT_TYPE_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_accountTypeValues[$val] = $val;
                }
            }
        }

        return self::$_accountTypeValues;
    }

    /**
     * Set account_type.
     *
     * @param $accountType
     *
     * @return TransferInformation
     *
     * @throws \InvalidArgumentException
     */
    public function setAccountType($accountType)
    {
        if (!in_array($accountType, self::getAccountTypeChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for transfer_information.account_type : %s.', $accountType)
            );
        }

        $this->account_type = $accountType;

        return $this;
    }

    /**
     * Get account_type.
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->account_type;
    }

    /**
     * Get choices for transfer_from column.
     *
     * @return array
     */
    public static function getTransferFromChoices()
    {
        return self::$_transferFromValues;
    }

    /**
     * Set transfer_from.
     *
     * @param int|null $transferFrom
     *
     * @return TransferInformation
     *
     * @throws \InvalidArgumentException
     */
    public function setTransferFrom($transferFrom)
    {
        if (!is_null($transferFrom) && !array_key_exists($transferFrom, self::getTransferFromChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for transfer_information.transfer_from : %s.', $transferFrom)
            );
        }

        $this->transfer_from = $transferFrom;

        return $this;
    }

    /**
     * Get transfer_from.
     *
     * @return int
     */
    public function getTransferFrom()
    {
        return $this->transfer_from;
    }

    /**
     * Get choices for insurance_policy_type column.
     *
     * @return array
     */
    public static function getInsurancePolicyTypeChoices()
    {
        return self::$_insurancePolicyTypeValues;
    }

    /**
     * Set insurance_policy_type.
     *
     * @param int|null $insurancePolicyType
     *
     * @return TransferInformation
     *
     * @throws \InvalidArgumentException
     */
    public function setInsurancePolicyType($insurancePolicyType)
    {
        if (!is_null($insurancePolicyType) && !array_key_exists($insurancePolicyType, self::getInsurancePolicyTypeChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for transfer_information.insurance_policy_type : %s.', $insurancePolicyType)
            );
        }

        $this->insurance_policy_type = $insurancePolicyType;

        return $this;
    }

    /**
     * Get insurance_policy_type.
     *
     * @return int
     */
    public function getInsurancePolicyType()
    {
        return $this->insurance_policy_type;
    }
}
