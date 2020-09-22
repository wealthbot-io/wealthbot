<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.07.13
 * Time: 16:11
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Entity\User;

class Workflow implements PaymentActivityInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $type;

    const TYPE_PAPERWORK = 1;
    const TYPE_ALERT = 2;

    protected static $_types = [
        self::TYPE_PAPERWORK => 'Paperwork',
        self::TYPE_ALERT => 'Alert',
    ];

    /**
     * @var int
     */
    protected $object_id;

    /**
     * @var array,
     */
    protected $object_ids;

    /**
     * @var string
     */
    protected $object_type;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $message_code;

    // Message code constants for paperwork
    const MESSAGE_CODE_PAPERWORK_NEW_ACCOUNT = 'p1';
    const MESSAGE_CODE_PAPERWORK_TRANSFER = 'p2';
    const MESSAGE_CODE_PAPERWORK_ROLLOVER = 'p3';
    const MESSAGE_CODE_PAPERWORK_UPDATE_BENEFICIARY = 'p4';
    const MESSAGE_CODE_PAPERWORK_UPDATE_ADDRESS = 'p5';
    const MESSAGE_CODE_PAPERWORK_UPDATE_BANKING_INFORMATION = 'p6';
    const MESSAGE_CODE_PAPERWORK_UPDATE_CONTRIBUTIONS = 'p7';
    const MESSAGE_CODE_PAPERWORK_UPDATE_DISTRIBUTIONS = 'p8';
    const MESSAGE_CODE_PAPERWORK_PORTFOLIO_PROPOSED = 'p9';
    const MESSAGE_CODE_PAPERWORK_INITIAL_REBALANCE = 'p10';

    // Message code constants for alerts
    const MESSAGE_CODE_ALERT_NEW_RETIREMENT_ACCOUNT = 'a1';
    const MESSAGE_CODE_ALERT_CLOSED_ACCOUNT = 'a3';
    //const MESSAGE_CODE_ALERT_INITIAL_REBALANCE              = 'a4';
    //const MESSAGE_CODE_ALERT_PORTFOLIO_PROPOSED             = 'a2';

    private static $_messageCodeValues = null;

    /**
     * Messages for paperwork.
     *
     * @var array
     */
    private static $_paperworkMessages = [
        self::MESSAGE_CODE_PAPERWORK_NEW_ACCOUNT => 'New Account',
        self::MESSAGE_CODE_PAPERWORK_TRANSFER => 'Transfer',
        self::MESSAGE_CODE_PAPERWORK_ROLLOVER => 'Rollover',
        self::MESSAGE_CODE_PAPERWORK_UPDATE_BENEFICIARY => 'New/Update Beneficiary',
        self::MESSAGE_CODE_PAPERWORK_UPDATE_ADDRESS => 'Address Update',
        self::MESSAGE_CODE_PAPERWORK_UPDATE_BANKING_INFORMATION => 'Banking Information Update',
        self::MESSAGE_CODE_PAPERWORK_UPDATE_CONTRIBUTIONS => 'New/Update Contributions',
        self::MESSAGE_CODE_PAPERWORK_UPDATE_DISTRIBUTIONS => 'New/Update Distributions',
        self::MESSAGE_CODE_PAPERWORK_PORTFOLIO_PROPOSED => 'Portfolio Proposal',
        self::MESSAGE_CODE_PAPERWORK_INITIAL_REBALANCE => 'Initial Rebalance',
    ];

    /**
     * Messages for alerts.
     *
     * @var array
     */
    private static $_alertMessages = [
        self::MESSAGE_CODE_ALERT_NEW_RETIREMENT_ACCOUNT => 'New Retirement Account',
        self::MESSAGE_CODE_ALERT_CLOSED_ACCOUNT => 'Closed Account',
        //self::MESSAGE_CODE_ALERT_INITIAL_REBALANCE      => 'Initial Rebalance',
        //self::MESSAGE_CODE_ALERT_PORTFOLIO_PROPOSED    => 'Portfolio Proposal',
    ];

    /**
     * @var int
     */
    protected $status;

    const STATUS_NEW = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_PENDING = 2;
    const STATUS_COMPLETED = 3;

    protected static $_statuses = [
        self::STATUS_NEW => 'new',
        self::STATUS_IN_PROGRESS => 'in progress',
        self::STATUS_PENDING => 'pending',
        self::STATUS_COMPLETED => 'completed',
    ];

    /**
     * @var int
     */
    protected $client_status;

    const CLIENT_STATUS_DEFAULT = 0;
    const CLIENT_STATUS_ENVELOPE_CREATED = 1;
    const CLIENT_STATUS_ENVELOPE_OPENED = 2;
    const CLIENT_STATUS_ENVELOPE_COMPLETED = 3;
    const CLIENT_STATUS_PORTFOLIO_PROPOSED = 4;
    const CLIENT_STATUS_PORTFOLIO_CLIENT_ACCEPTED = 5;
    const CLIENT_STATUS_ACCOUNT_OPENED = 6;
    const CLIENT_STATUS_ACCOUNT_FUNDED = 7;

    protected static $_clientStatuses = [
        self::CLIENT_STATUS_DEFAULT => '',
        self::CLIENT_STATUS_ENVELOPE_CREATED => 'envelope created',
        self::CLIENT_STATUS_ENVELOPE_OPENED => 'envelope opened',
        self::CLIENT_STATUS_ENVELOPE_COMPLETED => 'envelope completed',
        self::CLIENT_STATUS_PORTFOLIO_PROPOSED => 'portfolio proposed',
        self::CLIENT_STATUS_PORTFOLIO_CLIENT_ACCEPTED => 'client accepted portfolio',
        self::CLIENT_STATUS_ACCOUNT_OPENED => 'account opened',
        self::CLIENT_STATUS_ACCOUNT_FUNDED => 'account funded',
    ];

    /**
     * @var bool
     */
    protected $is_archived;

    /**
     * @var \DateTime
     */
    protected $submitted;

    /**
     * @param \App\Entity\User
     */
    protected $client;

    /**
     * @var string
     */
    protected $note;

    /**
     * @var string
     */
    protected $amount;

    public function __construct()
    {
        $this->object_ids = [];
        $this->is_archived = false;
        $this->status = self::STATUS_NEW;
        $this->client_status = self::CLIENT_STATUS_DEFAULT;
    }

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
     * Get type choices.
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        return self::$_types;
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return Workflow
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if (!array_key_exists($type, self::getTypeChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid value: %s for type column.', $type));
        }

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
     * Set object_id.
     *
     * @param int $objectId
     *
     * @return Workflow
     */
    public function setObjectId($objectId)
    {
        $this->object_id = $objectId;

        return $this;
    }

    /**
     * Get object_id.
     *
     * @return int
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Set object_ids.
     *
     * @param array $objectIds
     *
     * @return Workflow
     */
    public function setObjectIds(array $objectIds)
    {
        $this->object_ids = $objectIds;

        return $this;
    }

    /**
     * Get object_ids.
     *
     * @return array
     */
    public function getObjectIds()
    {
        return $this->object_ids;
    }

    /**
     * Set object_type.
     *
     * @param string $objectType
     *
     * @return Workflow
     */
    public function setObjectType($objectType)
    {
        $this->object_type = $objectType;

        return $this;
    }

    /**
     * Get object_type.
     *
     * @return string
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * Get message code choices.
     *
     * @return array
     */
    public static function getMessageCodeChoices()
    {
        if (null === self::$_messageCodeValues) {
            self::$_messageCodeValues = [];

            $rClass = new \ReflectionClass('App\Model\Workflow');
            $prefix = 'MESSAGE_CODE_';

            foreach ($rClass->getConstants() as $key => $value) {
                if ($prefix === substr($key, 0, strlen($prefix))) {
                    self::$_messageCodeValues[$value] = $value;
                }
            }
        }

        return self::$_messageCodeValues;
    }

    /**
     * Set message_code.
     *
     * @param $messageCode
     *
     * @return Workflow
     *
     * @throws \InvalidArgumentException
     */
    public function setMessageCode($messageCode)
    {
        if (!in_array($messageCode, self::getMessageCodeChoices())) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value for workflow.message_code column: %s.',
                $messageCode
            ));
        }

        $this->message_code = $messageCode;

        return $this;
    }

    /**
     * Get paperwork message choices.
     *
     * @return array
     */
    public static function getPaperworkMessageChoices()
    {
        return self::$_paperworkMessages;
    }

    /**
     * Get alert message choices.
     *
     * @return array
     */
    public static function getAlertMessageChoices()
    {
        return self::$_alertMessages;
    }

    /**
     * Get message_code.
     *
     * @return string
     */
    public function getMessageCode()
    {
        return $this->message_code;
    }

    /**
     * TODO: remove
     * Set message.
     *
     * @param string $message
     *
     * @return Workflow
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        if (self::TYPE_PAPERWORK === $this->getType()) {
            return self::$_paperworkMessages[$this->getMessageCode()];
        }

        return self::$_alertMessages[$this->getMessageCode()];
    }

    /**
     * Get message from db.
     *
     * @return string
     */
    public function getDbMessage()
    {
        return $this->message;
    }

    /**
     * Get status choices.
     *
     * @return array
     */
    public static function getStatusChoices()
    {
        return self::$_statuses;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Workflow
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!array_key_exists($status, self::getStatusChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid value: %s for status column.', $status));
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get status as string.
     *
     * @return string
     */
    public function getStatusAsString()
    {
        return self::$_statuses[$this->status];
    }

    /**
     * Is status new.
     *
     * @return bool
     */
    public function isNew()
    {
        return self::STATUS_NEW === $this->status;
    }

    /**
     * Is status in progress.
     *
     * @return bool
     */
    public function isInProgress()
    {
        return self::STATUS_IN_PROGRESS === $this->status;
    }

    /**
     * Is status pending.
     *
     * @return bool
     */
    public function isPending()
    {
        return self::STATUS_PENDING === $this->status;
    }

    /**
     * Is status completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return self::STATUS_COMPLETED === $this->status;
    }

    /**
     * Get client status choices.
     *
     * @return array
     */
    public static function getClientStatusChoices()
    {
        return self::$_clientStatuses;
    }

    /**
     * Set client status.
     *
     * @param int $clientStatus
     *
     * @return Workflow
     *
     * @throws \InvalidArgumentException
     */
    public function setClientStatus($clientStatus)
    {
        if (!array_key_exists($clientStatus, self::getClientStatusChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid value: %s for client_status column.', $$clientStatus));
        }

        $this->client_status = $clientStatus;

        return $this;
    }

    /**
     * Get client status.
     *
     * @return int
     */
    public function getClientStatus()
    {
        return $this->client_status;
    }

    /**
     * Get client status as string.
     *
     * @return string
     */
    public function getClientStatusAsString()
    {
        return self::$_clientStatuses[$this->client_status];
    }

    /**
     * Is client status envelope created.
     *
     * @return bool
     */
    public function isClientStatusEnvelopeCreated()
    {
        return self::CLIENT_STATUS_ENVELOPE_CREATED === $this->client_status;
    }

    /**
     * Is client status envelope opened.
     *
     * @return bool
     */
    public function isClientStatusEnvelopeOpened()
    {
        return self::CLIENT_STATUS_ENVELOPE_OPENED === $this->client_status;
    }

    /**
     * Is client status envelope completed.
     *
     * @return bool
     */
    public function isClientStatusEnvelopeCompleted()
    {
        return self::CLIENT_STATUS_ENVELOPE_COMPLETED === $this->client_status;
    }

    /**
     * Is client status portfolio proposed.
     *
     * @return bool
     */
    public function isClientStatusPortfolioProposed()
    {
        return self::CLIENT_STATUS_PORTFOLIO_PROPOSED === $this->client_status;
    }

    /**
     * Is client status portfolio accepted.
     *
     * @return bool
     */
    public function isClientStatusPortfolioAccepted()
    {
        return self::CLIENT_STATUS_PORTFOLIO_CLIENT_ACCEPTED === $this->client_status;
    }

    /**
     * Is client status account opened.
     *
     * @return bool
     */
    public function isClientStatusAccountOpened()
    {
        return self::CLIENT_STATUS_ACCOUNT_OPENED === $this->client_status;
    }

    /**
     * Is client status account funded.
     *
     * @return bool
     */
    public function isClientStatusAccountFunded()
    {
        return self::CLIENT_STATUS_ACCOUNT_FUNDED === $this->client_status;
    }

    /**
     * Set is_archived.
     *
     * @param bool $isArchived
     *
     * @return Workflow
     */
    public function setIsArchived($isArchived)
    {
        $this->is_archived = $isArchived;

        return $this;
    }

    /**
     * Get is_archived.
     *
     * @return bool
     */
    public function getIsArchived()
    {
        return $this->is_archived;
    }

    /**
     * Set submitted.
     *
     * @param \DateTime $submitted
     *
     * @return Workflow
     */
    public function setSubmitted($submitted)
    {
        $this->submitted = $submitted;

        return $this;
    }

    /**
     * Get submitted.
     *
     * @return \DateTime
     */
    public function getSubmitted()
    {
        return $this->submitted;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return Workflow
     */
    public function setClient(\App\Entity\User $client = null)
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
     * Is paperwork workflow.
     *
     * @return bool
     */
    public function isPaperwork()
    {
        return self::TYPE_PAPERWORK === $this->getType();
    }

    /**
     * Is alert workflow.
     *
     * @return bool
     */
    public function isAlert()
    {
        return self::TYPE_ALERT === $this->getType();
    }

    /**
     * Set note.
     *
     * @param string $note
     *
     * @return Workflow
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set amount.
     *
     * @param string $amount
     *
     * @return Workflow
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Workflow can have documents.
     *
     * @return bool
     */
    public function canHaveDocuments()
    {
        $paperworkWithoutDocuments = [
            self::MESSAGE_CODE_PAPERWORK_PORTFOLIO_PROPOSED,
            self::MESSAGE_CODE_PAPERWORK_INITIAL_REBALANCE,
        ];

        return $this->isPaperwork() && !in_array($this->message_code, $paperworkWithoutDocuments);
    }

    /**
     * Is portfolio proposal workflow.
     *
     * @return bool
     */
    public function isPortfolioProposal()
    {
        return $this->isPaperwork() && self::MESSAGE_CODE_PAPERWORK_PORTFOLIO_PROPOSED === $this->message_code;
    }

    /**
     * Get activity message.
     *
     * @return string
     */
    public function getActivityMessage()
    {
        // TODO: add new distribution and new contribution messages

        $message = null;
        switch ($this->message_code) {
            case self::MESSAGE_CODE_PAPERWORK_NEW_ACCOUNT:
                $message = 'Opened Account';
                break;
            case self::MESSAGE_CODE_PAPERWORK_TRANSFER:
                $message = 'Transferred Account In';
                break;
            case self::MESSAGE_CODE_PAPERWORK_ROLLOVER:
                $message = 'Rolled Account In';
                break;
//            case self::MESSAGE_CODE_PAPERWORK_UPDATE_CONTRIBUTIONS:
//                $message = 'Updated contribution';
//                break;
//            case self::MESSAGE_CODE_PAPERWORK_UPDATE_DISTRIBUTIONS:
//                $message = 'Updated distribution';
//                break;
            case self::MESSAGE_CODE_ALERT_CLOSED_ACCOUNT:
                $message = 'Account #%s Closed';
                break;
            case self::MESSAGE_CODE_PAPERWORK_PORTFOLIO_PROPOSED:
                if (self::CLIENT_STATUS_PORTFOLIO_CLIENT_ACCEPTED === $this->client_status) {
                    $message = ucwords($this->getClientStatusAsString());
                }
                break;
        }

        return $message;
    }

    /**
     * Get activity client.
     *
     * @return User
     */
    public function getActivityClient()
    {
        return $this->getClient();
    }

    /**
     * Get activity amount.
     *
     * @return float
     */
    public function getActivityAmount()
    {
        return $this->getAmount();
    }

    /**
     * Is advisor status editable.
     *
     * @return bool
     */
    public function isAdvisorStatusEditable()
    {
        return $this->isPortfolioProposal() || $this->isInitialRebalance() || $this->isClientStatusEnvelopeCompleted();
    }

    /**
     * Returns true if new account or transfer or rollover paperwork
     * and false otherwise.
     *
     * @return bool
     */
    public function isAccountPaperwork()
    {
        if (!$this->isPaperwork()) {
            return false;
        }

        $code = $this->getMessageCode();

        return
            self::MESSAGE_CODE_PAPERWORK_NEW_ACCOUNT === $code ||
            self::MESSAGE_CODE_PAPERWORK_TRANSFER === $code ||
            self::MESSAGE_CODE_PAPERWORK_ROLLOVER === $code
        ;
    }

    public function isInitialRebalance()
    {
        return self::MESSAGE_CODE_PAPERWORK_INITIAL_REBALANCE === $this->message_code;
    }
}
