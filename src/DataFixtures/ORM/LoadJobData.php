<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Job;
use App\Entity\User;

class LoadJobData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $ria */
        $ria = $manager->getRepository('App\Entity\User')->findOneBy([
            'email' => 'raiden@wealthbot.io',
        ]);

        $job1 = new Job();
        $job1->setUser($ria);
        $job1->setNameRebalancer();
        $job1->setStartedAt(new \DateTime('2013-02-13'));
        $job1->setRebalanceType(Job::REBALANCE_TYPE_FULL_AND_TLH);

        $manager->persist($job1);

        $date = new \DateTime('2013-02-14');
        $job2 = new Job();
        $job2->setUser($ria);
        $job2->setNameRebalancer();
        $job2->setStartedAt($date);
        $job2->setFinishedAt($date);

        $manager->persist($job2);

        $job3 = new Job();
        $job3->setUser($ria);
        $job3->setStartedAt(new \DateTime('2013-02-16'));
        $job3->setNameRebalancer();

        $manager->persist($job3);

        $job4 = new Job();
        $job4->setUser($ria);
        $job4->setNameRebalancer();

        $manager->persist($job4);

        $manager->flush();

        $this->setReference('job1', $job1);
        $this->setReference('job2', $job2);
        $this->setReference('job3', $job3);
        $this->setReference('job4', $job4);
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 9;
    }
}
