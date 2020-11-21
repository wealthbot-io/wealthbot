<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 09.01.13
 * Time: 12:43
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

class ClientPortfolio implements WorkflowableInterface
{
    /**
     * @var string
     */
    protected $status;

    /**
     * @var \DateTime
     */
    protected $approved_at;

    /**
     * @var \DateTime
     */
    protected $accepted_at;

    /**
     * @var \DateTime
     */
    protected $created_at;

    /**
     * @var bool
     */
    protected $is_active;

    // ENUM values status column
    const STATUS_PROPOSED = 'proposed';
    const STATUS_ADVISOR_APPROVED = 'advisor approved';
    const STATUS_CLIENT_ACCEPTED = 'client accepted';

    private static $_statusValues = null;

    public function __construct()
    {
        $this->is_active = true;
        $this->status = self::STATUS_PROPOSED;
        //$this->setApprovedAt(new \DateTime());
    }

    /**
     * Get array ENUM values status column.
     *
     * @static
     *
     * @return array
     */
    public static function getStatusChoices()
    {
        // Build $_statusValues if this is the first call
        if (null === self::$_statusValues) {
            self::$_statusValues = [];
            $oClass = new \ReflectionClass('\App\Model\ClientPortfolio');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'STATUS_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_statusValues[$val] = $val;
                }
            }
        }

        return self::$_statusValues;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return ClientPortfolio
     *
     * @throws \InvalidArgumentException
     */
    public function setStatus($status)
    {
        if (!in_array($status, self::getStatusChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for client_portfolio.status : %s.', $status)
            );
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status as proposed.
     *
     * @return $this
     */
    public function setStatusProposed()
    {
        $this->status = self::STATUS_PROPOSED;

        return $this;
    }

    /**
     * Is proposed portfolio.
     *
     * @return bool
     */
    public function isProposed()
    {
        return (bool) (self::STATUS_PROPOSED === $this->getStatus());
    }

    /**
     * Set status as advisor approved.
     *
     * @return $this
     */
    public function setStatusAdvisorApproved()
    {
        $this->status = self::STATUS_ADVISOR_APPROVED;
        $this->setApprovedAt(new \DateTime());

        return $this;
    }

    /**
     * Is advisor approved portfolio.
     *
     * @return bool
     */
    public function isAdvisorApproved()
    {
        return (bool) (self::STATUS_ADVISOR_APPROVED === $this->getStatus());
    }

    /**
     * Set status as client accepted.
     *
     * @return $this
     */
    public function setStatusClientAccepted()
    {
        $this->status = self::STATUS_CLIENT_ACCEPTED;
        $this->setAcceptedAt(new \DateTime());

        return $this;
    }

    /**
     * Is client accepted portfolio.
     *
     * @return bool
     */
    public function isClientAccepted()
    {
        return (bool) (self::STATUS_CLIENT_ACCEPTED === $this->getStatus());
    }

    /**
     * Set approved_at.
     *
     * @param \DateTime $approvedAt
     *
     * @return ClientPortfolio
     */
    public function setApprovedAt($approvedAt)
    {
        $this->approved_at = $approvedAt;

        return $this;
    }

    /**
     * Get approved_at.
     *
     * @return \DateTime
     */
    public function getApprovedAt()
    {
        return $this->approved_at;
    }

    /**
     * Set accepted_at.
     *
     * @param \DateTime $acceptedAt
     *
     * @return ClientPortfolio
     */
    public function setAcceptedAt($acceptedAt)
    {
        $this->accepted_at = $acceptedAt;

        return $this;
    }

    /**
     * Get accepted_at.
     *
     * @return \DateTime
     */
    public function getAcceptedAt()
    {
        return $this->accepted_at;
    }

    /**
     * Set created_at.
     *
     * @param \DateTime $createdAt
     *
     * @return ClientPortfolio
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Get workflow message code.
     *
     * @return string
     */
    public function getWorkflowMessageCode()
    {
        return Workflow::MESSAGE_CODE_PAPERWORK_PORTFOLIO_PROPOSED;
    }
}
