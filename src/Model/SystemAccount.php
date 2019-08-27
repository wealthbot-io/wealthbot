<?php

namespace App\Model;

class SystemAccount implements WorkflowableInterface
{
    /**
     * @var int
     */
    protected $type;

    // Constants for type column
    const TYPE_PERSONAL_INVESTMENT = 1;
    const TYPE_JOINT_INVESTMENT = 2;
    const TYPE_ROTH_IRA = 3;
    const TYPE_TRADITIONAL_IRA = 4;
    const TYPE_RETIREMENT = 5;

    const CREATION_TYPE_NEW_ACCOUNT = 1;
    const CREATION_TYPE_TRANSFER_ACCOUNT = 2;
    const CREATION_TYPE_ROLLOVER_ACCOUNT = 3;

    // Time to wait accepting from custodian
    const DAYS_WAIT_TRANSFER_OR_ROLLOVER_ACCOUNT = 10;
    const DAYS_WAIT_NEW_ACCOUNT = 3;

    /**
     * String values for type column.
     *
     * @var array
     */
    private static $_types = [
        self::TYPE_PERSONAL_INVESTMENT => 'Personal Investment Account',
        self::TYPE_JOINT_INVESTMENT => 'Joint Investment Account',
        self::TYPE_ROTH_IRA => 'Roth IRA',
        self::TYPE_TRADITIONAL_IRA => 'Traditional IRA',
        self::TYPE_RETIREMENT => 'Retirement Account',
    ];

    /**
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $account_number;

    // Constants for status column
    const STATUS_REGISTERED = 'registered';
    const STATUS_ACTIVE = 'active';
    const STATUS_INIT_REBALANCE = 'init rebalance';
    const STATUS_INIT_REBALANCE_COMPLETE = 'init rebalance complete';
    const STATUS_REBALANCED = 'rebalanced';
    const STATUS_ANALYZED = 'account analyzed';
    const STATUS_CLOSED = 'account closed';
    const STATUS_WAITING_ACTIVATION = 'waiting activation';

    private static $_statusValues = null;

    /**
     * @var \DateTime
     */
    protected $activated_on;

    /**
     * @var \DateTime
     */
    protected $closed;


    protected $creationType;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->status = self::STATUS_REGISTERED;
        $this->creationType = self::CREATION_TYPE_NEW_ACCOUNT;
    }

    /**
     * Get choices for type column.
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        return self::$_types;
    }

    /**
     * Get type string name.
     *
     * @param $type
     *
     * @return string
     */
    public static function getTypeName($type)
    {
        $types = self::$_types;

        return isset($types[$type]) ? $types[$type] : '';
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return SystemAccount
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get type as string.
     *
     * @return string
     */
    public function getTypeAsString()
    {
        if (null === $this->type) {
            return '';
        }

        return array_values(self::$_types)[$this->type];
    }

    /**
     * Get choices for status column.
     *
     * @return array|null
     */
    public static function getStatusChoices()
    {
        // Build $_statusValues if this is the first call
        if (null === self::$_statusValues) {
            self::$_statusValues = [];
            $oClass = new \ReflectionClass('\App\Model\SystemAccount');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'STATUS_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_statusValues[$val] = $val;
                }
            }
        }

        return self::$_statusValues;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return SystemAccount
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function setStatusOpen()
    {
        $this->status = self::STATUS_REGISTERED;

        return $this;
    }

    public function isOpen()
    {
        return self::STATUS_REGISTERED === $this->status;
    }

    public function setStatusActive()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->activated_on = new \DateTime();

        return $this;
    }

    public function isActive()
    {
        return self::STATUS_ACTIVE === $this->status;
    }

    public function setStatusInitRebalance()
    {
        $this->status = self::STATUS_INIT_REBALANCE;

        return $this;
    }

    public function isInitRebalance()
    {
        return self::STATUS_INIT_REBALANCE === $this->status;
    }

    public function setStatusInitRebalanceComplete()
    {
        $this->status = self::STATUS_INIT_REBALANCE_COMPLETE;

        return $this;
    }

    public function isInitRebalanceComplete()
    {
        return self::STATUS_INIT_REBALANCE_COMPLETE === $this->status;
    }

    public function setStatusRebalanced()
    {
        $this->status = self::STATUS_REBALANCED;

        return $this;
    }

    public function isRebalanced()
    {
        return self::STATUS_REBALANCED === $this->status;
    }

    public function setStatusAnalyzed()
    {
        $this->status = self::STATUS_ANALYZED;

        return $this;
    }

    public function isAnalyzed()
    {
        return self::STATUS_ANALYZED === $this->status;
    }

    public function setStatusClosed()
    {
        $this->status = self::STATUS_CLOSED;
        $this->closed = new \DateTime();

        return $this;
    }

    public function isClosed()
    {
        return self::STATUS_CLOSED === $this->status;
    }

    /**
     * Set account_number.
     *
     * @param string $accountNumber
     *
     * @return SystemAccount
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
     * Returns true if account has Personal Investment type and false otherwise.
     *
     * @return bool
     */
    public function isPersonalType()
    {
        return (self::TYPE_PERSONAL_INVESTMENT === $this->getType()) ? true : false;
    }

    /**
     * Returns true if account has Joint Investment type and false otherwise.
     *
     * @return bool
     */
    public function isJointType()
    {
        return (self::TYPE_JOINT_INVESTMENT === $this->getType()) ? true : false;
    }

    /**
     * Returns true if account has Roth IRA type and false otherwise.
     *
     * @return bool
     */
    public function isRothIraType()
    {
        return (self::TYPE_ROTH_IRA === $this->getType()) ? true : false;
    }

    /**
     * Returns true if account has Traditional IRA type and false otherwise.
     *
     * @return bool
     */
    public function isTraditionalIraType()
    {
        return (self::TYPE_TRADITIONAL_IRA === $this->getType()) ? true : false;
    }

    /**
     * Returns true if account has Retirement type and false otherwise.
     *
     * @return bool
     */
    public function isRetirementType()
    {
        return (self::TYPE_RETIREMENT === $this->getType()) ? true : false;
    }

    public function getLastFourDigitsOfAccountNumber()
    {
        return substr($this->getAccountNumber(), -4);
    }

    /**
     * Set activated_on.
     *
     * @param \DateTime $activatedOn
     *
     * @return SystemAccount
     */
    public function setActivatedOn($activatedOn)
    {
        $this->activated_on = $activatedOn;

        return $this;
    }

    /**
     * Get activated_on.
     *
     * @return \DateTime
     */
    public function getActivatedOn()
    {
        return $this->activated_on;
    }

    /**
     * Set closed.
     *
     * @param \DateTime $closed
     *
     * @return SystemAccount
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed.
     *
     * @return \DateTime
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Get workflow message code.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    public function getWorkflowMessageCode()
    {
        return Workflow::MESSAGE_CODE_PAPERWORK_INITIAL_REBALANCE;
    }
}
