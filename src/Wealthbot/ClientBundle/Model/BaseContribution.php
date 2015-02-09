<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 02.04.13
 * Time: 16:27
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Model;


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

    static private $_typeValues = null;

    // ENUM values transaction_frequency column
    const TRANSACTION_FREQUENCY_ONE_TIME = 1;
    const TRANSACTION_FREQUENCY_EVERY_OTHER_WEEK = 2;
    const TRANSACTION_FREQUENCY_MONTHLY = 3;
    const TRANSACTION_FREQUENCY_QUARTERLY = 4;

    static private $_transactionFrequencies = array(
        self::TRANSACTION_FREQUENCY_ONE_TIME => 'One-time',
        self::TRANSACTION_FREQUENCY_EVERY_OTHER_WEEK => 'Every other week',
        self::TRANSACTION_FREQUENCY_MONTHLY => 'Monthly',
        self::TRANSACTION_FREQUENCY_QUARTERLY => 'Quarterly'
    );

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
     * @var integer
     */
    protected $bank_information_id;

    /**
     * @var \Wealthbot\ClientBundle\Entity\BankInformation
     */
    protected $bankInformation;

    /**
     * @var string
     */
    protected $contribution_year;

    /**
     * Get array ENUM values type column
     *
     * @static
     * @return array
     */
    static public function getTypeChoices()
    {
        // Build $_typeValues if this is the first call
        if (self::$_typeValues == null) {
            self::$_typeValues = array();
            $oClass = new \ReflectionClass('\Wealthbot\ClientBundle\Model\BaseContribution');
            $classConstants = $oClass->getConstants();
            $constantPrefix = "TYPE_";
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_typeValues[$val] = $val;
                }
            }
        }

        return self::$_typeValues;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return BaseContribution
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get transaction_frequency choices
     *
     * @return array
     */
    public static function getTransactionFrequencyChoices()
    {
        return self::$_transactionFrequencies;
    }

    /**
     * @param $transactionFrequency
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
     * Set amount
     *
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set start_transfer_date
     *
     * @param \DateTime $startTransferDate
     * @return AccountContribution
     */
    public function setStartTransferDate($startTransferDate)
    {
        $this->start_transfer_date = $startTransferDate;

        return $this;
    }

    /**
     * Get start_transfer_date
     *
     * @return \DateTime
     */
    public function getStartTransferDate()
    {
        return $this->start_transfer_date;
    }


    /**
     * Get workflow message code
     *
     * @return string
     */
    public function getWorkflowMessageCode()
    {
        return Workflow::MESSAGE_CODE_PAPERWORK_UPDATE_CONTRIBUTIONS;
    }

    /**
     * Get workflow amount
     *
     * @return float
     */
    public function getWorkflowAmount()
    {
        return $this->getAmount();
    }

    /**
     * Set bank_information_id
     *
     * @param integer $bankInformationId
     * @return AccountContribution
     */
    public function setBankInformationId($bankInformationId)
    {
        $this->bank_information_id = $bankInformationId;

        return $this;
    }

    /**
     * Get bank_information_id
     *
     * @return integer
     */
    public function getBankInformationId()
    {
        return $this->bank_information_id;
    }

    /**
     * Set bankInformation
     *
     * @param \Wealthbot\ClientBundle\Entity\BankInformation $bankInformation
     * @return AccountContribution
     */
    public function setBankInformation(\Wealthbot\ClientBundle\Entity\BankInformation $bankInformation = null)
    {
        $this->bankInformation = $bankInformation;

        return $this;
    }

    /**
     * Get bankInformation
     *
     * @return \Wealthbot\ClientBundle\Entity\BankInformation
     */
    public function getBankInformation()
    {
        return $this->bankInformation;
    }

    /**
     * Set contribution_year
     *
     * @param string $contributionYear
     * @return $this
     */
    public function setContributionYear($contributionYear)
    {
        $this->contribution_year = $contributionYear;

        return $this;
    }

    /**
     * Get contribution_year
     *
     * @return string
     */
    public function getContributionYear()
    {
        return $this->contribution_year;
    }
}