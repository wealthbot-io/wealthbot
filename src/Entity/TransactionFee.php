<?php

namespace App\Entity;

/**
 * Class TransactionFee
 * @package App\Entity
 */
class TransactionFee
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $fee;

    /**
     * @var int
     */
    private $transaction_id;

    /**
     * @var int
     */
    private $fee_type_id;

    /**
     * @param \App\Entity\Transaction
     */
    private $transaction;

    /**
     * @param \App\Entity\TransactionFeeType
     */
    private $feeType;

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
     * Set fee.
     *
     * @param int $fee
     *
     * @return TransactionFee
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get fee.
     *
     * @return int
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set transaction_id.
     *
     * @param int $transactionId
     *
     * @return TransactionFee
     */
    public function setTransactionId($transactionId)
    {
        $this->transaction_id = $transactionId;

        return $this;
    }

    /**
     * Get transaction_id.
     *
     * @return int
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * Set fee_type_id.
     *
     * @param int $feeTypeId
     *
     * @return TransactionFee
     */
    public function setFeeTypeId($feeTypeId)
    {
        $this->fee_type_id = $feeTypeId;

        return $this;
    }

    /**
     * Get fee_type_id.
     *
     * @return int
     */
    public function getFeeTypeId()
    {
        return $this->fee_type_id;
    }

    /**
     * Set transaction.
     *
     * @param \App\Entity\Transaction $transaction
     *
     * @return TransactionFee
     */
    public function setTransaction(Transaction $transaction = null)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get transaction.
     *
     * @return \App\Entity\Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set feeType.
     *
     * @param \App\Entity\TransactionFeeType $feeType
     *
     * @return TransactionFee
     */
    public function setFeeType(TransactionFeeType $feeType = null)
    {
        $this->feeType = $feeType;

        return $this;
    }

    /**
     * Get feeType.
     *
     * @return \App\Entity\TransactionFeeType
     */
    public function getFeeType()
    {
        return $this->feeType;
    }
}
