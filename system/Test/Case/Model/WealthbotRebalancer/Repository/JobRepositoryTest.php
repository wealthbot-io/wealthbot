<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Repository\JobRepository;
use Test\Suit\ExtendedTestCase;

class JobRepositoryTest extends ExtendedTestCase
{
    /** @var JobRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new JobRepository();
    }

    public function testFindAllCurrentJob()
    {
        $jobs = $this->repository->findAllCurrentRebalancerJob();

        $this->assertCount(3, $jobs);

        $now = new \DateTime();

        /** @var Job $job */
        foreach ($jobs as $job) {
            $jobStartedAt = new \DateTime($job->getStartedAt());

            $this->assertNull($job->getFinishedAt());
            $this->assertLessThan($now->getTimestamp(), $jobStartedAt->getTimestamp());
            $this->assertEquals(Job::JOB_NAME_REBALANCER, $job->getName());
        }
    }
}