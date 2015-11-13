<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Portfolio;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RebalancerActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\RebalancerAction */
    private $rebalancerAction;

    public function setUp()
    {
        $data = array(
            'id' => 10,
            'accountId'=> 13,
            'portfolioId' => 214,
            'job' => array(
                'id' => 123
            ),
            'client' => array(
                'id' => 147
            ),
            'status' => Job::REBALANCE_TYPE_FULL
        );

        $this->rebalancerAction = $this->getMockBuilder('Model\WealthbotRebalancer\RebalancerAction')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->rebalancerAction->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $this->assertEquals(10, $this->rebalancerAction->getId());
        $this->assertEquals(13, $this->rebalancerAction->getAccountId());
        $this->assertEquals(123, $this->rebalancerAction->getJob()->getId());
        $this->assertEquals(214, $this->rebalancerAction->getPortfolioId());
        $this->assertEquals(147, $this->rebalancerAction->getClient()->getId());
    }

    public function testGetAccountId()
    {
        $this->assertEquals(13, $this->rebalancerAction->getAccountId());
    }

    public function testSetAccountId()
    {
        $this->rebalancerAction->setAccountId(78);

        $this->assertEquals(78, $this->rebalancerAction->getAccountId());
    }

    public function testGetJob()
    {
        $this->assertEquals(123, $this->rebalancerAction->getJob()->getId());
    }

    public function testSetJob()
    {
        $newJob = new Job();
        $newJob->setId(2);

        $this->rebalancerAction->setJob($newJob);

        $this->assertEquals(2, $this->rebalancerAction->getJob()->getId());
    }

    public function testGetPortfolioId()
    {
        $this->assertEquals(214, $this->rebalancerAction->getPortfolioId());
    }

    public function testSetPortfolioId()
    {
        $this->rebalancerAction->setPortfolioId(45);

        $this->assertEquals(45, $this->rebalancerAction->getPortfolioId());
    }

    public function testGetClient()
    {
        $this->assertEquals(147, $this->rebalancerAction->getClient()->getId());
    }

    public function testSetClient()
    {
        $newClient = new Client();
        $newClient->setId(789);

        $this->rebalancerAction->setClient($newClient);

        $this->assertEquals(789, $this->rebalancerAction->getClient()->getId());
    }

    public function testGetStatus()
    {
        $this->assertEquals(Job::REBALANCE_TYPE_FULL, $this->rebalancerAction->getStatus());
    }

    public function testSetStatus()
    {
        $this->rebalancerAction->setStatus(Job::REBALANCE_TYPE_NO_ACTIONS);

        $this->assertEquals(Job::REBALANCE_TYPE_NO_ACTIONS, $this->rebalancerAction->getStatus());
    }
}