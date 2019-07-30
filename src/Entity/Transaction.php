<?php

namespace App\Entity;

use App\Entity\Lot;
use App\Entity\SystemAccount;
use App\Model\PaymentActivityInterface;
use App\Entity\User;

/**
 * Class Transaction
 * @package App\Entity
 */
class Transaction implements PaymentActivityInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $qty;

    /**
     * @var float
     */
    private $netAmount;

    /**
     * @var float
     */
    private $grossAmount;

    /**
     * @var \DateTime
     */
    private $txDate;

    /**
     * @var \DateTime
     */
    private $settleDate;

    /**
     * @var float
     */
    private $accruedInterest;

    /**
     * @var string
     */
    private $notes;

    /**
     * @var string
     */
    private $status;

    // Constants for status column
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_PLACED = 'placed';
    const STATUS_VERIFIED = 'verified';
    const STATUS_NOT_POSTED = 'not posted';

    private static $_statuses = null;

    /**
     * @param \App\Entity\SystemAccount
     */
    private $account;

    /**
     * @param \App\Entity\TransactionType
     */
    private $transactionType;

    /**
     * @param \App\Entity\ClosingMethod
     */
    private $closingMethod;

    /**
     * @var bool
     */
    private $cancelStatus;

    /**
     * @param \App\Entity\SystemAccount
     */
    private $transferAccount;

    /**
     * @var Lot
     */
    private $lot;

    /**
     * TODO: remove this later (in position exists security).
     *
     * @param \App\Entity\Security
     */
    private $security;

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
     * Set qty.
     *
     * @param float $qty
     *
     * @return Transaction
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get qty.
     *
     * @return float
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Set netAmount.
     *
     * @param float $netAmount
     *
     * @return Transaction
     */
    public function setNetAmount($netAmount)
    {
        $this->netAmount = $netAmount;

        return $this;
    }

    /**
     * Get netAmount.
     *
     * @return float
     */
    public function getNetAmount()
    {
        return $this->netAmount;
    }

    /**
     * Set grossAmount.
     *
     * @param float $grossAmount
     *
     * @return Transaction
     */
    public function setGrossAmount($grossAmount)
    {
        $this->grossAmount = $grossAmount;

        return $this;
    }

    /**
     * Get grossAmount.
     *
     * @return float
     */
    public function getGrossAmount()
    {
        return $this->grossAmount;
    }

    /**
     * Set txDate.
     *
     * @param \DateTime $txDate
     *
     * @return Transaction
     */
    public function setTxDate($txDate)
    {
        $this->txDate = $txDate;

        return $this;
    }

    /**
     * Get txDate.
     *
     * @return \DateTime
     */
    public function getTxDate()
    {
        return $this->txDate;
    }

    /**
     * Set settleDate.
     *
     * @param \DateTime $settleDate
     *
     * @return Transaction
     */
    public function setSettleDate($settleDate)
    {
        $this->settleDate = $settleDate;

        return $this;
    }

    /**
     * Get settleDate.
     *
     * @return \DateTime
     */
    public function getSettleDate()
    {
        return $this->settleDate;
    }

    /**
     * Set accruedInterest.
     *
     * @param float $accruedInterest
     *
     * @return Transaction
     */
    public function setAccruedInterest($accruedInterest)
    {
        $this->accruedInterest = $accruedInterest;

        return $this;
    }

    /**
     * Get accruedInterest.
     *
     * @return float
     */
    public function getAccruedInterest()
    {
        return $this->accruedInterest;
    }

    /**
     * Set notes.
     *
     * @param string $notes
     *
     * @return Transaction
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes.
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    public static function getStatusChoices()
    {
        if (null === self::$_statuses) {
            self::$_statuses = [];

            $oClass = new \ReflectionClass('\App\Entity\Transaction');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'STATUS_';

            foreach ($classConstants as $key => $value) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_statuses[$value] = $value;
                }
            }
        }

        return self::$_statuses;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, self::getStatusChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid value for status column: %s', $status));
        }

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

    /**
     * Set account.
     *
     * @param SystemAccount $account
     *
     * @return Transaction
     */
    public function setAccount(SystemAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \App\Entity\SystemAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set transactionType.
     *
     * @param \App\Entity\TransactionType $transactionType
     *
     * @return Transaction
     */
    public function setTransactionType(TransactionType $transactionType = null)
    {
        $this->transactionType = $transactionType;

        return $this;
    }

    /**
     * Get transactionType.
     *
     * @return \App\Entity\TransactionType
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Set closingMethod.
     *
     * @param \App\Entity\ClosingMethod $closingMethod
     *
     * @return Transaction
     */
    public function setClosingMethod(ClosingMethod $closingMethod = null)
    {
        $this->closingMethod = $closingMethod;

        return $this;
    }

    /**
     * Get closingMethod.
     *
     * @return \App\Entity\ClosingMethod
     */
    public function getClosingMethod()
    {
        return $this->closingMethod;
    }

    /**
     * Set cancelStatus.
     *
     * @param bool $cancelStatus
     *
     * @return Transaction
     */
    public function setCancelStatus($cancelStatus)
    {
        $this->cancelStatus = $cancelStatus;

        return $this;
    }

    /**
     * Get cancelStatus.
     *
     * @return bool
     */
    public function getCancelStatus()
    {
        return $this->cancelStatus;
    }

    /**
     * Set transferAccount.
     *
     * @param \App\Entity\SystemAccount $transferAccount
     *
     * @return Transaction
     */
    public function setTransferAccount(SystemAccount $transferAccount = null)
    {
        $this->transferAccount = $transferAccount;

        return $this;
    }

    /**
     * Get transferAccount.
     *
     * @return \App\Entity\SystemAccount
     */
    public function getTransferAccount()
    {
        return $this->transferAccount;
    }

    /**
     * Set security.
     *
     * @param \App\Entity\Security $security
     *
     * @return Transaction
     */
    public function setSecurity(Security $security = null)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * Get security.
     *
     * @return \App\Entity\Security
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Get activity message.
     *
     * @return string
     */
    public function getActivityMessage()
    {
        $message = null;

        $type = $this->getTransactionType();
        if ($type) {
            if ('DEP' === $type->getName()) {
                $message = 'Deposit';
            } elseif ('WITH' === $type->getName()) {
                $message = 'Withdrawal';
            }
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
        $account = $this->getAccount();

        return $account ? $account->getClient() : null;
    }

    /**
     * Get activity amount.
     *
     * @return float
     */
    public function getActivityAmount()
    {
        return $this->getNetAmount();
    }

    /**
     * @param \App\Entity\Lot $lot
     */
    public function setLot($lot)
    {
        $this->lot = $lot;
    }

    /**
     * @return \App\Entity\Lot
     */
    public function getLot()
    {
        return $this->lot;
    }
}
