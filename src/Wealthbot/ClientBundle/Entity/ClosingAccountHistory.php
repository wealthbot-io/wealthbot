<?php

namespace Wealthbot\ClientBundle\Entity;

use Wealthbot\ClientBundle\Model\ClosingAccountHistory as BaseClosingAccountHistory;

/**
 * ClosingAccountHistory.
 */
class ClosingAccountHistory extends BaseClosingAccountHistory
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $account_id;

    /**
     * @var array
     */
    protected $messages;

    /**
     * @var \DateTime
     */
    private $closing_date;

    /**
     * @var \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    private $account;

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
     * Set account_id.
     *
     * @param int $accountId
     *
     * @return ClosingAccountHistory
     */
    public function setAccountId($accountId)
    {
        $this->account_id = $accountId;

        return $this;
    }

    /**
     * Get account_id.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Set closing_date.
     *
     * @param \DateTime $closingDate
     *
     * @return ClosingAccountHistory
     */
    public function setClosingDate($closingDate)
    {
        $this->closing_date = $closingDate;

        return $this;
    }

    /**
     * Get closing_date.
     *
     * @return \DateTime
     */
    public function getClosingDate()
    {
        return $this->closing_date;
    }

    /**
     * Set account.
     *
     * @param \Wealthbot\ClientBundle\Entity\SystemAccount $account
     *
     * @return ClosingAccountHistory
     */
    public function setAccount(\Wealthbot\ClientBundle\Entity\SystemAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set messages.
     *
     * @param array $messages
     *
     * @return ClosingAccountHistory
     */
    public function setMessages(array $messages)
    {
        parent::setMessages($messages);

        return $this;
    }

    /**
     * Get messages.
     *
     * @return array
     */
    public function getMessages()
    {
        return parent::getMessages();
    }
}
