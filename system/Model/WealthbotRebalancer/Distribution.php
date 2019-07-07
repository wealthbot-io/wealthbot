<?php

namespace Model\WealthbotRebalancer;


class Distribution extends Base
{
    /**
     * @var string
     */
    private $type;

    const TYPE_SCHEDULED = 'scheduled';
    const TYPE_ONE_TIME  = 'one_time';

    /**
     * @var string
     */
    private $transferMethod;

    const TRANSFER_METHOD_RECEIVE_CHECK = 'receive_check';
    const TRANSFER_METHOD_WIRE_TRANSFER = 'wire_transfer';
    const TRANSFER_METHOD_BANK_TRANSFER = 'bank_transfer';
    const TRANSFER_METHOD_NOT_FUNDING   = 'not_funding';

    /**
     * @var float
     */
    private $amount;

    /**
     * @var \DateTime
     */
    private $transferDate;

    /**
     * @var integer
     */
    private $frequency;

    const FREQUENCY_EVERY_OTHER_WEEK = 2;
    const FREQUENCY_MONTHLY          = 3;
    const FREQUENCY_QUARTERLY        = 4;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;


    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $frequency
     * @return $this
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param \DateTime $transferDate
     * @return $this
     */
    public function setTransferDate($transferDate)
    {
        $this->transferDate = $transferDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTransferDate()
    {
        return $this->transferDate;
    }

    /**
     * @param string $transferMethod
     * @return $this
     */
    public function setTransferMethod($transferMethod)
    {
        $this->transferMethod = $transferMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransferMethod()
    {
        return $this->transferMethod;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function loadFromArray(array $data = array())
    {
        $dateFields = array(
            'transferDate',
            'transfer_date',
            'createdAt',
            'created_at',
            'updatedAt',
            'updated_at'
        );

        foreach ($data as $key => $value) {
            if (in_array($key, $dateFields) && !($value instanceof \DateTime)) {
                $this->$key = new \DateTime($value);
            } else {
                $this->$key = $value;
            }
        }
    }

}