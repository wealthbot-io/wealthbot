<?php

namespace Model\Pas;

use Wealthbot\ClientBundle\Entity\BillItem as WealthbotBillItem;

class BillItem extends Base
{
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
     * @var integer
     */
    private $status;

    /**
     * @var SystemAccount
     */
    private $systemAccountId;

    /**
     * @var Bill
     */
    private $billId;

    /**
     * @var double
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
        $this->status = WealthbotBillItem::STATUS_BILL_GENERATED;
    }

    /**
     * @param int $billId
     * @return BillItem
     */
    public function setBillId($billId)
    {
        $this->billId = $billId;

        return $this;
    }

    /**
     * Get feeBilled
     *
     * @return float
     */
    public function getBillId()
    {
        return $this->billId;
    }

    /**
     * @param int $systemAccountId
     * @return BillItem
     */
    public function setSystemAccountId($systemAccountId)
    {
        $this->systemAccountId = $systemAccountId;

        return $this;
    }

    /**
     * Get feeBilled
     *
     * @return float
     */
    public function getSystemAccountId()
    {
        return $this->systemAccountId;
    }

    /**
     * Set feeBilled
     *
     * @param float $feeBilled
     * @return BillItem
     */
    public function setFeeBilled($feeBilled)
    {
        $this->feeBilled = $feeBilled;

        return $this;
    }

    /**
     * Get feeBilled
     *
     * @return float
     */
    public function getFeeBilled()
    {
        return $this->feeBilled;
    }

    /**
     * Set feeCollected
     *
     * @param float $feeCollected
     * @return BillItem
     */
    public function setFeeCollected($feeCollected)
    {
        $this->feeCollected = $feeCollected;

        return $this;
    }

    /**
     * Get feeCollected
     *
     * @return float
     */
    public function getFeeCollected()
    {
        return (float) $this->feeCollected;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return BillItem
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
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

    public function setStatusIsCollected()
    {
        if ($this->status < WealthbotBillItem::STATUS_BILL_COLLECTED) {
            $this->status = WealthbotBillItem::STATUS_BILL_COLLECTED;
        }
    }
}