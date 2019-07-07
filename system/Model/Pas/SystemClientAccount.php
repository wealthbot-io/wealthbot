<?php

namespace Model\Pas;

use Wealthbot\ClientBundle\Model\SystemAccount as WealthbotSystemAccount;

class SystemClientAccount extends Base
{
    /**
     * @var  string
     */
    protected $clientId;

    /**
     * @var  string
     */
    protected $accountNumber;

    /**
     * @var string
     */
    protected $performanceInception;

    protected $status;
    protected $closed;

    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    public function getPerformanceInception()
    {
        return $this->performanceInception;
    }

    public function getPerformanceInceptionAsDateTime()
    {
        if ($this->performanceInception) {
            return new \DateTime($this->performanceInception);
        }

        return null;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    public function getClosed()
    {
        return $this->closed;
    }

    public function getClosedAsDateTime()
    {
        if ($this->closed) {
            return new \DateTime($this->closed);
        }

        return null;
    }

    public function isClosedExpired(\DateTime $date)
    {
        $closedAt = $this->getClosedAsDateTime();

        if (null == $closedAt) {
            return false;
        }

        return ($this->getStatus() == WealthbotSystemAccount::STATUS_CLOSED && $date->getTimestamp() > $closedAt->getTimestamp());
    }
}