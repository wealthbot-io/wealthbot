<?php

namespace App\Entity;

/**
 * Class BillItem
 * @package App\Entity
 */
class BillItem
{
    /**
     * Bill statuses
     * Must be integer for getClientBillStatus
     * Before changing check messages.yml
     * Status less than 0 is used for count of accounts that needs attention.
     *
     * @const
     */
    const STATUS_BILL_NOT_GENERATED = 1;
    const STATUS_BILL_GENERATED = 2;
    const STATUS_BILL_APPROVED = 3;
    const STATUS_FEE_CANNOT_BE_GENERATED = 4;
    const STATUS_FEE_GENERATED = 5;
    const STATUS_BILL_COLLECTED = 6;
    const STATUS_WILL_NOT_BILL = 7;
    const STATUS_BILL_IS_NOT_APPLICABLE = 8;

    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $feeBilled;

    /**
     * @var float
     */
    private $feeCollected;

    /**
     * @var float
     */
    private $riaFee;

    /**
     * @var float
     */
    private $adminFee;

    /**
     * @var int
     */
    private $status;

    /**
     * @var SystemAccount
     */
    private $systemAccount;

    /**
     * @var Bill
     */
    private $bill;

    /**
     * @var float
     */
    private $custodianFee;

    /**
     * @var \DateTime
     */
    private $createdAt;

    public function __construct()
    {
        $this->feeBilled = 0;
        $this->feeCollected = 0;
        $this->custodianFee = 0;
        $this->riaFee = 0;
        $this->adminFee = 0;
        $this->status = self::STATUS_BILL_GENERATED;
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
     * Set feeBilled.
     *
     * @param float $feeBilled
     *
     * @return BillItem
     */
    public function setFeeBilled($feeBilled)
    {
        $this->feeBilled = $feeBilled;

        return $this;
    }

    /**
     * Get feeBilled.
     *
     * @return float
     */
    public function getFeeBilled()
    {
        return $this->feeBilled;
    }

    /**
     * Set feeCollected.
     *
     * @param float $feeCollected
     *
     * @return BillItem
     */
    public function setFeeCollected($feeCollected)
    {
        $this->feeCollected = $feeCollected;

        return $this;
    }

    /**
     * Get feeCollected.
     *
     * @return float
     */
    public function getFeeCollected()
    {
        return $this->feeCollected;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return BillItem
     */
    public function setStatus($status)
    {
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
     * @param Bill $bill
     */
    public function setBill($bill)
    {
        $this->bill = $bill;
    }

    /**
     * @return Bill
     */
    public function getBill()
    {
        return $this->bill;
    }

    /**
     * @param SystemAccount $systemAccount
     */
    public function setSystemAccount($systemAccount)
    {
        $this->systemAccount = $systemAccount;
    }

    /**
     * @return SystemAccount
     */
    public function getSystemAccount()
    {
        return $this->systemAccount;
    }

    /**
     * @param float $custodianFee
     */
    public function setCustodianFee($custodianFee)
    {
        $this->custodianFee = $custodianFee;
    }

    /**
     * @return float
     */
    public function getCustodianFee()
    {
        return $this->custodianFee;
    }

    /**
     * @param float $riaFee
     */
    public function setRiaFee($riaFee)
    {
        $this->riaFee = $riaFee;
    }

    /**
     * @return float
     */
    public function getRiaFee()
    {
        return $this->riaFee;
    }

    /**
     * @param float $adminFee
     */
    public function setAdminFee($adminFee)
    {
        $this->adminFee = $adminFee;
    }

    /**
     * @return float
     */
    public function getAdminFee()
    {
        return $this->adminFee;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
