<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Ria;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class JobTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\Job */
    private $job;

    public function setUp()
    {
        $data = array(
            'id' => 10,
            'name' => Job::JOB_NAME_REBALANCER,
            'rebalance_type' => Job::REBALANCE_TYPE_FULL,
            'started_at' => '2014-03-04 16:36:44',
            'finished_at' => '2014-03-04 20:20:20',
            'is_error' => false,
            'ria' => array(
                'id' => 15
            )
        );

        $this->job = $this->getMockBuilder('Model\WealthbotRebalancer\Job')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->job->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $ria = $this->job->getRia();

        $this->assertEquals(10, $this->job->getId());
        $this->assertEquals(Job::JOB_NAME_REBALANCER, $this->job->getName());
        $this->assertEquals(Job::REBALANCE_TYPE_FULL, $this->job->getRebalanceType());
        $this->assertEquals('2014-03-04 16:36:44', $this->job->getStartedAt());
        $this->assertEquals('2014-03-04 20:20:20', $this->job->getFinishedAt());
        $this->assertFalse($this->job->getIsError());
        $this->assertEquals(15, $ria->getId());
    }

    public function testGetName()
    {
        $this->assertEquals(Job::JOB_NAME_REBALANCER, $this->job->getName());
    }

    public function testGetRebalanceType()
    {
        $this->assertEquals(Job::REBALANCE_TYPE_FULL, $this->job->getRebalanceType());
    }

    public function testSetRebalanceType()
    {
        $this->job->setRebalanceType(Job::JOB_NAME_REBALANCER);
        $this->assertEquals(Job::JOB_NAME_REBALANCER, $this->job->getRebalanceType());
    }

    public function testIsFullRebalance()
    {
        $this->assertTrue($this->job->isFullRebalance());

        $this->job->setRebalanceType(Job::REBALANCE_TYPE_FULL_AND_TLH);
        $this->assertFalse($this->job->isFullRebalance());
    }

    public function testIsRequiredCashRebalance()
    {
        $this->assertFalse($this->job->isRequiredCashRebalance());

        $this->job->setRebalanceType(Job::REBALANCE_TYPE_REQUIRED_CASH);
        $this->assertTrue($this->job->isRequiredCashRebalance());
    }

    public function testIsFullAndTlhRebalance()
    {
        $this->assertFalse($this->job->isFullAndTlhRebalance());

        $this->job->setRebalanceType(Job::REBALANCE_TYPE_FULL_AND_TLH);
        $this->assertTrue($this->job->isFullAndTlhRebalance());
    }

    public function testGetStartedAt()
    {
        $this->assertEquals('2014-03-04 16:36:44', $this->job->getStartedAt());
    }

    public function testSetStartedAt()
    {
        $this->job->setStartedAt('2012-01-01 11:11:11');

        $this->assertEquals('2012-01-01 11:11:11', $this->job->getStartedAt());
    }

    public function testGetFinishedAt()
    {
        $this->assertEquals('2014-03-04 20:20:20', $this->job->getFinishedAt());
    }

    public function testSetFinishedAt()
    {
        $this->job->setFinishedAt('2000-12-12 12:12:12');

        $this->assertEquals('2000-12-12 12:12:12', $this->job->getFinishedAt());
    }

    public function testGetIsError()
    {
        $this->assertFalse($this->job->getIsError());
    }

    public function testSetIsError()
    {
        $this->job->setIsError(true);

        $this->assertTrue($this->job->getIsError());
    }

    public function testGetRia()
    {
        $ria = $this->job->getRia();

        $this->assertEquals(15, $ria->getId());
    }

    public function testSetRia()
    {
        $newRia = new Ria();
        $newRia->setId(78);

        $this->job->setRia($newRia);

        $this->assertEquals(78, $this->job->getRia()->getId());
    }
}