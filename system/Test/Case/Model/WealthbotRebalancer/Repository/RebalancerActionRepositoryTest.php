<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Database\WealthbotMySqlSqliteConnection;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\RebalancerAction;
use Model\WealthbotRebalancer\Repository\BaseRepository;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\JobRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Model\WealthbotRebalancer\Repository\RebalancerActionRepository;
use Model\WealthbotRebalancer\Repository\RiaRepository;
use Test\Suit\ExtendedTestCase;

class RebalancerActionRepositoryTest extends ExtendedTestCase
{
    /** @var RebalancerActionRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new RebalancerActionRepository();
    }

    public function testBindForJob()
    {
        $riaRepo = new RiaRepository();
        $ria = $riaRepo->findOneBy(array(
            'email' => 'raiden@wealthbot.io'
        ));

        $jobRepo = new JobRepository();
        $job = $jobRepo->findOneBy(array(
            'user_id' => $ria->getId()
        ));

        $rebalancerActions = $this->repository->bindForJob($job);

        $this->assertCount(2, $rebalancerActions);

        /** @var RebalancerAction $rebalancerAction */
        foreach ($rebalancerActions as $rebalancerAction) {
            $this->assertEquals($job, $rebalancerAction->getJob());
        }
    }

    public function testFindByPortfolioAndJob()
    {
        $jobRepo = new JobRepository();
        $clientRepo = new ClientRepository();
        $portfolioRepo = new PortfolioRepository();

        $job = $jobRepo->findFirst();

        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');
        $portfolio = $portfolioRepo->findPortfolioByClient($client);

        $rebalancerActions = $this->repository->findByPortfolioAndJob($portfolio, $job);

        $this->assertCount(2, $rebalancerActions);

        /** @var RebalancerAction $rebalancerAction */
        foreach ($rebalancerActions as $rebalancerAction) {
            $this->assertEquals($job->getId(), $rebalancerAction->getJob()->getId());
            $this->assertEquals($portfolio->getId(), $rebalancerAction->getPortfolioId());
        }
    }

    public function testSaveStatus()
    {
        $jobRepo = new JobRepository();
        $clientRepo = new ClientRepository();
        $portfolioRepo = new PortfolioRepository();

        $job = $jobRepo->findFirst();

        $client = $clientRepo->findClientByEmail('liu@wealthbot.io');
        $portfolio = $portfolioRepo->findPortfolioByClient($client);

        $rebalancerActions = $this->repository->findByPortfolioAndJob($portfolio, $job);

        /** @var RebalancerAction $rebalancerAction */
        $rebalancerAction = $rebalancerActions->first();

        $sql = "SELECT * FROM ".BaseRepository::TABLE_REBALANCER_ACTION." ra where ra.id = :id";

        $connection = WealthbotMySqlSqliteConnection::getInstance();
        $pdo = $connection->getPdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array('id' => $rebalancerAction->getId()));
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertNotEquals(Job::REBALANCE_TYPE_NO_ACTIONS, $result['status']);
        $tmpStatus = $result['status'];

        $rebalancerAction->setStatus(Job::REBALANCE_TYPE_NO_ACTIONS);

        $this->repository->saveStatus($rebalancerAction);

        $statement = $pdo->prepare($sql);
        $statement->execute(array('id' => $rebalancerAction->getId()));
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        $this->assertEquals(Job::REBALANCE_TYPE_NO_ACTIONS, $result['status']);

        $rebalancerAction->setStatus($tmpStatus);
        $this->repository->saveStatus($rebalancerAction);
    }
}
