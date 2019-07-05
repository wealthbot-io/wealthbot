<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\RebalancerAction;
use App\Entity\User;

class LoadRebalancerActionData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var User $milesClient */
        $milesClient = $this->getReference('clientN2');
        $milesRebalancerAction = $this->buildRebalancerAction($manager, $milesClient);

        /** @var User $everhartClient */
        $everhartClient = $this->getReference('clientN3');
        $everhartRebalancerAction = $this->buildRebalancerAction($manager, $everhartClient);

        $this->setReference('miles-rebalancer-action', $milesRebalancerAction);

        $manager->persist($milesRebalancerAction);
        $manager->persist($everhartRebalancerAction);
        $manager->flush();
    }

    private function buildRebalancerAction(ObjectManager $manager, User $client, $isHousehold = true)
    {
        $job1 = $this->getReference('job1');

        $clientPortfolioValue = $manager->getRepository('App\Entity\ClientPortfolioValue')->getLastValueByClient($client);

        $rebalancerAction = new RebalancerAction();
        $rebalancerAction->setJob($job1);
        $rebalancerAction->setClientPortfolioValue($clientPortfolioValue);
        $rebalancerAction->setStartedAt(new \DateTime());

        if (!$isHousehold) {
            $clientAccountValue = $manager->getRepository('App\Entity\ClientAccountValue')->getLastByDate($client);
            $rebalancerAction->setClientAccountValue($clientAccountValue); // setSystemClientAccount($systemAccounts->first());
        }

        return $rebalancerAction;
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 21;
    }
}
