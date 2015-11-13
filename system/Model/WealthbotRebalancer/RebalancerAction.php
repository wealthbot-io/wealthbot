<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RebalancerAction extends Base {

    /** @var  Job */
    private $job;

    /** @var  Account */
    private $accountId;

    /** @var  int */
    private $portfolioId;

    /** @var  Client */
    private $client;

    /** @var string */
    private $status;

    /**
     * @param int $accountId
     * @return $this
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param Job $job
     * @return $this
     */
    public function setJob(Job $job)
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return Job
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param int $portfolioId
     * @return $this
     */
    public function setPortfolioId($portfolioId)
    {
        $this->portfolioId = $portfolioId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPortfolioId()
    {
        return $this->portfolioId;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($key === 'job') {
                $class = 'Model\WealthbotRebalancer\Job';
                $job = new $class;
                $job->loadFromArray($value);

                $this->setJob($job);

            } elseif ($key === 'client') {
                $class = 'Model\WealthbotRebalancer\Client';
                $client = new $class;
                $client->loadFromArray($value);

                $this->setClient($client);

            } else {
                $this->$key = $value;
            }
        }
    }

}