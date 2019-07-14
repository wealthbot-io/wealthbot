<?php

namespace App\Model;

class BaseContribution implements PaymentWorkflowableInterface
{
    /**
     * @var string
     */
    protected $type;

    // ENUM values type column
    const TYPE_FUNDING_MAIL = 'funding_mail_check';
    const TYPE_FUNDING_BANK = 'funding_bank_transfer';
    const TYPE_FUNDING_WIRE = 'funding_wire_transfer';
    const TYPE_NOT_FUNDING = 'not_funding';
    const TYPE_DISTRIBUTING = 'distribution';

    private static $_typeValues = null;

    // ENUM values transaction_frequency column
    const TRANSACTION_FREQUENCY_ONE_TIME = 1;
    const TRANSACTION_FREQUENCY_EVERY_OTHER_WEEK = 2;
    const TRANSACTION_FREQUENCY_MONTHLY = 3;
    const TRANSACTION_FREQUENCY_QUARTERLY = 4;

    private static $_transactionFrequencies = [
        'One-time' => self::TRANSACTION_FREQUENCY_ONE_TIME,
        'Every other week' => self::TRANSACTION_FREQUENCY_EVERY_OTHER_WEEK,
        'Monthly' => self::TRANSACTION_FREQUENCY_MONTHLY,
        'Quarterly' => self::TRANSACTION_FREQUENCY_QUARTERLY,
    ];

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var \DateTime
     */
    protected $start_transfer_date;

    /** @var int */
    protected $transaction_frequency;

    /**
     * @var int
     */
    protected $bank_information_id;

    /**
     * @param \App\Entity\BankInformation
     */
    protected $bankInformation;

    /**
     * @var string
     */
    protected $contribution_year;

    /**
     * Get array ENUM values type column.
     *
     * @static
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        // Build $_typeValues if this is the first call
        if (null === self::$_typeValues) {
            self::$_typeValues = [];
            $oClass = new \ReflectionClass('\App\Model\BaseContribution');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'TYPE_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_typeValues[$val] = $val;
                }
            }
        }

        return self::$_typeValues;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return BaseContribution
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getTypeChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for type : %s.', $type)
            );
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get transaction_frequency choices.
     *
     * @return array
     */
    public static function getTransactionFrequencyChoices()
    {
        return self::$_transactionFrequencies;
    }

    /**
     * @param $transactionFrequency
     *
     * @return $this
     */
    public function setTransactionFrequency($transactionFrequency)
    {
        $this->transaction_frequency = $transactionFrequency;

        return $this;
    }

    /**
     * @return int
     */
    public function getTransactionFrequency()
    {
        return $this->transaction_frequency;
    }

    /**
     * Set amount.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set start_transfer_date.
     *
     * @param \DateTime $startTransferDate
     *
     * @return AccountContribution
     */
    public function setStartTransferDate($startTransferDate)
    {
        $this->start_transfer_date = $startTransferDate;

        return $this;
    }

    /**
     * Get start_transfer_date.
     *
     * @return \DateTime
     */
    public function getStartTransferDate()
    {
        return $this->start_transfer_date;
    }

    /**
     * Get workflow message code.
     *
     * @return string
     */
    public function getWorkflowMessageCode()
    {
        return Workflow::MESSAGE_CODE_PAPERWORK_UPDATE_CONTRIBUTIONS;
    }

    /**
     * Get workflow amount.
     *
     * @return float
     */
    public function getWorkflowAmount()
    {
        return $this->getAmount();
    }

    /**
     * Set bank_information_id.
     *
     * @param int $bankInformationId
     *
     * @return AccountContribution
     */
    public function setBankInformationId($bankInformationId)
    {
        $this->bank_information_id = $bankInformationId;

        return $this;
    }

    /**
     * Get bank_information_id.
     *
     * @return int
     */
    public function getBankInformationId()
    {
        return $this->bank_information_id;
    }

    /**
     * Set bankInformation.
     *
     * @param \App\Entity\BankInformation $bankInformation
     *
     * @return AccountContribution
     */
    public function setBankInformation(\App\Entity\BankInformation $bankInformation = null)
    {
        $this->bankInformation = $bankInformation;

        return $this;
    }

    /**
     * Get bankInformation.
     *
     * @return \App\Entity\BankInformation
     */
    public function getBankInformation()
    {
        return $this->bankInformation;
    }

    /**
     * Set contribution_year.
     *
     * @param string $contributionYear
     *
     * @return $this
     */
    public function setContributionYear($contributionYear)
    {
        $this->contribution_year = $contributionYear;

        return $this;
    }

    /**
     * Get contribution_year.
     *
     * @return string
     */
    public function getContributionYear()
    {
        return $this->contribution_year;
    }
}
