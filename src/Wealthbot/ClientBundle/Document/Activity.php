<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 15.01.14
 * Time: 19:21.
 */

namespace Wealthbot\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="activity")
 */
class Activity
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Int
     */
    protected $clientUserId;

    /**
     * @MongoDB\Int
     */
    protected $clientStatus;

    /**
     * @MongoDB\Int
     */
    protected $riaUserId;

    /**
     * @MongoDB\String
     */
    protected $firstName;

    /**
     * @MongoDB\String
     */
    protected $lastName;

    /**
     * @MongoDB\String
     */
    protected $message;

    /**
     * @MongoDB\Date
     */
    protected $createdAt;

    /**
     * @MongoDB\Boolean
     */
    protected $isShowRia;

    /**
     * @MongoDB\Float
     */
    protected $amount;

    public function __construct()
    {
        $this->isShowRia = true;
    }

    /**
     * Get id.
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set clientUserId.
     *
     * @param int $clientUserId
     *
     * @return self
     */
    public function setClientUserId($clientUserId)
    {
        $this->clientUserId = $clientUserId;

        return $this;
    }

    /**
     * Get clientUserId.
     *
     * @return int $clientUserId
     */
    public function getClientUserId()
    {
        return $this->clientUserId;
    }

    /**
     * Set clientStatus.
     *
     * @param int $clientStatus
     *
     * @return self
     */
    public function setClientStatus($clientStatus)
    {
        $this->clientStatus = $clientStatus;

        return $this;
    }

    /**
     * Get clientStatus.
     *
     * @return int $clientStatus
     */
    public function getClientStatus()
    {
        return $this->clientStatus;
    }

    /**
     * Set riaUserId.
     *
     * @param int $riaUserId
     *
     * @return self
     */
    public function setRiaUserId($riaUserId)
    {
        $this->riaUserId = $riaUserId;

        return $this;
    }

    /**
     * Get riaUserId.
     *
     * @return int $riaUserId
     */
    public function getRiaUserId()
    {
        return $this->riaUserId;
    }

    /**
     * Set firstName.
     *
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName.
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName.
     *
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName.
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return self
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set isShowRia.
     *
     * @param bool $isShowRia
     *
     * @return self
     */
    public function setIsShowRia($isShowRia)
    {
        $this->isShowRia = $isShowRia;

        return $this;
    }

    /**
     * Get isShowRia.
     *
     * @return bool $isShowRia
     */
    public function getIsShowRia()
    {
        return $this->isShowRia;
    }

    /**
     * Set amount.
     *
     * @param float $amount
     *
     * @return self
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return float $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }
}
