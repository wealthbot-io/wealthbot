<?php

namespace Wealthbot\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TransactionFee
 */
class TransactionFee
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $fee;

    /**
     * @var integer
     */
    private $transaction_id;

    /**
     * @var integer
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fee
     *
     * @param integer $fee
     * @return TransactionFee
     */
    public function setFee($fee)
    {
        $this->fee = $fee;
    
        return $this;
    }

    /**
     * Get fee
     *
     * @return integer 
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set transaction_id
     *
     * @param integer $transactionId
     * @return TransactionFee
     */
    public function setTransactionId($transactionId)
    {
        $this->transaction_id = $transactionId;
    
        return $this;
    }

    /**
     * Get transaction_id
     *
     * @return integer 
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * Set fee_type_id
     *
     * @param integer $feeTypeId
     * @return TransactionFee
     */
    public function setFeeTypeId($feeTypeId)
    {
        $this->fee_type_id = $feeTypeId;
    
        return $this;
    }

    /**
     * Get fee_type_id
     *
     * @return integer 
     */
    public function getFeeTypeId()
    {
        return $this->fee_type_id;
    }

    /**
     * Set transaction
     *
     * @param \Wealthbot\AdminBundle\Entity\Transaction $transaction
     * @return TransactionFee
     */
    public function setTransaction(\Wealthbot\AdminBundle\Entity\Transaction $transaction = null)
    {
        $this->transaction = $transaction;
    
        return $this;
    }

    /**
     * Get transaction
     *
     * @return \Wealthbot\AdminBundle\Entity\Transaction 
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set feeType
     *
     * @param \Wealthbot\AdminBundle\Entity\TransactionFeeType $feeType
     * @return TransactionFee
     */
    public function setFeeType(\Wealthbot\AdminBundle\Entity\TransactionFeeType $feeType = null)
    {
        $this->feeType = $feeType;
    
        return $this;
    }

    /**
     * Get feeType
     *
     * @return \Wealthbot\AdminBundle\Entity\TransactionFeeType 
     */
    public function getFeeType()
    {
        return $this->feeType;
    }
}
