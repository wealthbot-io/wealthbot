<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 31.07.13
 * Time: 15:55
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="tempPortfolio")
 */
class TempPortfolio
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\int
     */
    protected $clientUserId;

    /**
     * @MongoDB\int
     */
    protected $modelId;

    const STATUS_NOT_APPROVED = 'not approved';
    const STATUS_APPROVED = 'approved';

    /**
     * @MongoDB\String
     */
    protected $status;

    public function __construct()
    {
        $this->status = self::STATUS_NOT_APPROVED;
    }

    /**
     * Get id.
     *
     * @return int $id
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
     * Set modelId.
     *
     * @param int $modelId
     *
     * @return self
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;

        return $this;
    }

    /**
     * Get modelId.
     *
     * @return int $modelId
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if ($status !== self::STATUS_NOT_APPROVED && $status !== self::STATUS_APPROVED) {
            throw new \InvalidArgumentException(sprintf('Invalid value for status: %s', $status));
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Is portfolio approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getStatus() === self::STATUS_APPROVED;
    }
}
