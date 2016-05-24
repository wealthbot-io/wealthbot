<?php

namespace Wealthbot\AdminBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="rebalanceProgress")
 */
class RebalanceProgress
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\int
     */
    protected $userId;

    /**
     * @MongoDB\int
     */
    protected $totalCount;

    /**
     * @MongoDB\int
     */
    protected $completeCount;

    public function __construct($totalCount)
    {
        $this->totalCount = $totalCount;
        $this->completeCount = 0;
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
     * Set userId.
     *
     * @param int $userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set totalCount.
     *
     * @param int $totalCount
     *
     * @return self
     */
    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    /**
     * Get totalCount.
     *
     * @return int $totalCount
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Set completeCount.
     *
     * @param int $completeCount
     *
     * @return $this
     */
    public function setCompleteCount($completeCount)
    {
        $this->completeCount = $completeCount;

        return $this;
    }

    /**
     * Get completeCount.
     *
     * @return int $completeCount
     */
    public function getCompleteCount()
    {
        return $this->completeCount;
    }
}
