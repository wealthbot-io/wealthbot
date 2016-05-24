<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 24.01.14
 * Time: 15:38.
 */

namespace Wealthbot\RiaBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="firmMetrics")
 */
class FirmMetric
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Int
     */
    protected $companyInformationId;

    /**
     * @MongoDB\Float
     */
    protected $clients;

    /**
     * @MongoDB\Float
     */
    protected $clientsQtdChange;

    /**
     * @MongoDB\Float
     */
    protected $clientsYearChange;

    /**
     * @MongoDB\Float
     */
    protected $accounts;

    /**
     * @MongoDB\Float
     */
    protected $accountsQtdChange;

    /**
     * @MongoDB\Float
     */
    protected $accountsYearChange;

    /**
     * @MongoDB\Float
     */
    protected $prospects;

    /**
     * @MongoDB\Float
     */
    protected $prospectsQtdChange;

    /**
     * @MongoDB\Float
     */
    protected $prospectsYearChange;

    /**
     * @MongoDB\Float
     */
    protected $feesCollected;

    /**
     * @MongoDB\Float
     */
    protected $feesCollectedQtdChange;

    /**
     * @MongoDB\Float
     */
    protected $feesCollectedYearChange;

    /**
     * @MongoDB\Date
     */
    protected $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * Set companyInformationId.
     *
     * @param int $companyInformationId
     *
     * @return self
     */
    public function setCompanyInformationId($companyInformationId)
    {
        $this->companyInformationId = $companyInformationId;

        return $this;
    }

    /**
     * Get companyInformationId.
     *
     * @return int $companyInformationId
     */
    public function getCompanyInformationId()
    {
        return $this->companyInformationId;
    }

    /**
     * Set clients.
     *
     * @param float $clients
     *
     * @return self
     */
    public function setClients($clients)
    {
        $this->clients = $clients;

        return $this;
    }

    /**
     * Get clients.
     *
     * @return float $clients
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Set clientsQtdChange.
     *
     * @param float $clientsQtdChange
     *
     * @return self
     */
    public function setClientsQtdChange($clientsQtdChange)
    {
        $this->clientsQtdChange = $clientsQtdChange;

        return $this;
    }

    /**
     * Get clientsQtdChange.
     *
     * @return float $clientsQtdChange
     */
    public function getClientsQtdChange()
    {
        return $this->clientsQtdChange;
    }

    /**
     * Set clientsYearChange.
     *
     * @param float $clientsYearChange
     *
     * @return self
     */
    public function setClientsYearChange($clientsYearChange)
    {
        $this->clientsYearChange = $clientsYearChange;

        return $this;
    }

    /**
     * Get clientsYearChange.
     *
     * @return float $clientsYearChange
     */
    public function getClientsYearChange()
    {
        return $this->clientsYearChange;
    }

    /**
     * Set accounts.
     *
     * @param float $accounts
     *
     * @return self
     */
    public function setAccounts($accounts)
    {
        $this->accounts = $accounts;

        return $this;
    }

    /**
     * Get accounts.
     *
     * @return float $accounts
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Set accountsQtdChange.
     *
     * @param float $accountsQtdChange
     *
     * @return self
     */
    public function setAccountsQtdChange($accountsQtdChange)
    {
        $this->accountsQtdChange = $accountsQtdChange;

        return $this;
    }

    /**
     * Get accountsQtdChange.
     *
     * @return float $accountsQtdChange
     */
    public function getAccountsQtdChange()
    {
        return $this->accountsQtdChange;
    }

    /**
     * Set accountsYearChange.
     *
     * @param float $accountsYearChange
     *
     * @return self
     */
    public function setAccountsYearChange($accountsYearChange)
    {
        $this->accountsYearChange = $accountsYearChange;

        return $this;
    }

    /**
     * Get accountsYearChange.
     *
     * @return float $accountsYearChange
     */
    public function getAccountsYearChange()
    {
        return $this->accountsYearChange;
    }

    /**
     * Set prospects.
     *
     * @param float $prospects
     *
     * @return self
     */
    public function setProspects($prospects)
    {
        $this->prospects = $prospects;

        return $this;
    }

    /**
     * Get prospects.
     *
     * @return float $prospects
     */
    public function getProspects()
    {
        return $this->prospects;
    }

    /**
     * Set prospectsQtdChange.
     *
     * @param float $prospectsQtdChange
     *
     * @return self
     */
    public function setProspectsQtdChange($prospectsQtdChange)
    {
        $this->prospectsQtdChange = $prospectsQtdChange;

        return $this;
    }

    /**
     * Get prospectsQtdChange.
     *
     * @return float $prospectsQtdChange
     */
    public function getProspectsQtdChange()
    {
        return $this->prospectsQtdChange;
    }

    /**
     * Set prospectsYearChange.
     *
     * @param float $prospectsYearChange
     *
     * @return self
     */
    public function setProspectsYearChange($prospectsYearChange)
    {
        $this->prospectsYearChange = $prospectsYearChange;

        return $this;
    }

    /**
     * Get prospectsYearChange.
     *
     * @return float $prospectsYearChange
     */
    public function getProspectsYearChange()
    {
        return $this->prospectsYearChange;
    }

    /**
     * Set feesCollected.
     *
     * @param float $feesCollected
     *
     * @return self
     */
    public function setFeesCollected($feesCollected)
    {
        $this->feesCollected = $feesCollected;

        return $this;
    }

    /**
     * Get feesCollected.
     *
     * @return float $feesCollected
     */
    public function getFeesCollected()
    {
        return $this->feesCollected;
    }

    /**
     * Set feesCollectedQtdChange.
     *
     * @param float $feesCollectedQtdChange
     *
     * @return self
     */
    public function setFeesCollectedQtdChange($feesCollectedQtdChange)
    {
        $this->feesCollectedQtdChange = $feesCollectedQtdChange;

        return $this;
    }

    /**
     * Get feesCollectedQtdChange.
     *
     * @return float $feesCollectedQtdChange
     */
    public function getFeesCollectedQtdChange()
    {
        return $this->feesCollectedQtdChange;
    }

    /**
     * Set feesCollectedYearChange.
     *
     * @param float $feesCollectedYearChange
     *
     * @return self
     */
    public function setFeesCollectedYearChange($feesCollectedYearChange)
    {
        $this->feesCollectedYearChange = $feesCollectedYearChange;

        return $this;
    }

    /**
     * Get feesCollectedYearChange.
     *
     * @return float $feesCollectedYearChange
     */
    public function getFeesCollectedYearChange()
    {
        return $this->feesCollectedYearChange;
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
}
