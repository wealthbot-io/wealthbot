<?php

namespace Wealthbot\AdminBundle\Entity;

/**
 * TransactionFee.
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
     * @var \Wealthbot\AdminBundle\Entity\Transaction
     */
    private $transaction;

    /**
     * @var \Wealthbot\AdminBundle\Entity\TransactionFeeType
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
     * @param \Wealthbot\AdminBundle\Entity\Transaction $transaction
     *
     * @return TransactionFee
     */
    public function setTransaction(\Wealthbot\AdminBundle\Entity\Transaction $transaction = null)
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * Get transaction.
     *
     * @return \Wealthbot\AdminBundle\Entity\Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set feeType.
     *
     * @param \Wealthbot\AdminBundle\Entity\TransactionFeeType $feeType
     *
     * @return TransactionFee
     */
    public function setFeeType(\Wealthbot\AdminBundle\Entity\TransactionFeeType $feeType = null)
    {
        $this->feeType = $feeType;

        return $this;
    }

    /**
     * Get feeType.
     *
     * @return \Wealthbot\AdminBundle\Entity\TransactionFeeType
     */
    public function getFeeType()
    {
        return $this->feeType;
    }
}
