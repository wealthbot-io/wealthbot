<?php

namespace App\Entity;

use App\Model\CeModelInterface;
use App\Model\ClientPortfolio as BaseClientPortfolio;

/**
 * Class ClientPortfolio
 * @package App\Entity
 */
class ClientPortfolio extends BaseClientPortfolio
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client_id;

    /**
     * @var int
     */
    private $portfolio_id;

    /**
     * @param \App\Entity\User
     */
    private $client;

    /**
     * @param \App\Entity\CeModel
     */
    private $portfolio;

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

    public function __construct()
    {
        parent::__construct();
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
     * Set client_id.
     *
     * @param int $clientId
     *
     * @return ClientPortfolio
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set portfolio_id.
     *
     * @param int $portfolioId
     *
     * @return ClientPortfolio
     */
    public function setPortfolioId($portfolioId)
    {
        $this->portfolio_id = $portfolioId;

        return $this;
    }

    /**
     * Get portfolio_id.
     *
     * @return int
     */
    public function getPortfolioId()
    {
        return $this->portfolio_id;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return ClientPortfolio
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set portfolio.
     *
     * @param CeModelInterface $portfolio
     *
     * @return ClientPortfolio
     */
    public function setPortfolio(CeModelInterface $portfolio = null)
    {
        $this->portfolio = $portfolio;

        return $this;
    }

    /**
     * Get portfolio.
     *
     * @return CeModelInterface
     */
    public function getPortfolio()
    {
        return $this->portfolio;
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
        return parent::setStatus($status);
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return parent::getStatus();
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
        return parent::setApprovedAt($approvedAt);
    }

    /**
     * Get approved_at.
     *
     * @return \DateTime
     */
    public function getApprovedAt()
    {
        return parent::getApprovedAt();
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
        parent::setAcceptedAt($acceptedAt);

        return $this;
    }

    /**
     * Get accepted_at.
     *
     * @return \DateTime
     */
    public function getAcceptedAt()
    {
        return parent::getAcceptedAt();
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
        parent::setCreatedAt($createdAt);

        return $this;
    }

    /**
     * Get created_at.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return parent::getCreatedAt();
    }

    /**
     * Set is_active.
     *
     * @param bool $isActive
     *
     * @return ClientPortfolio
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;

        return $this;
    }

    /**
     * Get is_active.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->is_active;
    }
}
