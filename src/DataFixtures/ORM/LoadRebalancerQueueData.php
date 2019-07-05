<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Security;
use App\Entity\Lot;
use App\Entity\RebalancerQueue;
use App\Entity\SystemAccount;

class LoadRebalancerQueueData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Security $securityRWX */
        $securityRWX = $this->getReference('security-RWX');
        /** @var Security $securityVCIT */
        $securityVCIT = $this->getReference('security-VCIT');
        /** @var Security $securityBND */
        $securityBND = $this->getReference('security-BND');

        /** @var SystemAccount $account916985328 */
        $account916985328 = $this->getReference('system-account');
        /** @var SystemAccount $account480888811 */
        $account480888811 = $this->getReference('system-account-480888811');
        /** @var SystemAccount $account122223334 */
        $account122223334 = $this->getReference('system-account-122223334');
        /** @var SystemAccount $account744888385 */
        $account744888385 = $this->getReference('system-account-744888385');

        /** @var Lot $lot0 */
        $lot0 = $this->getReference('lot-0');
        /** @var Lot $lot1 */
        $lot1 = $this->getReference('lot-1');

        $rebalancerAction = $this->getReference('miles-rebalancer-action');

        $rebalancerQueue1 = new RebalancerQueue();
        $rebalancerQueue1->setRebalancerAction($rebalancerAction);
        $rebalancerQueue1->setAmount(0);
        $rebalancerQueue1->setQuantity(36);
        $rebalancerQueue1->setStatus(RebalancerQueue::STATUS_SELL);
        $rebalancerQueue1->setLot($lot0);
        $rebalancerQueue1->setSecurity($securityRWX);
        $rebalancerQueue1->setSystemClientAccount($account916985328);

        $manager->persist($rebalancerQueue1);

        $rebalancerQueue2 = new RebalancerQueue();
        $rebalancerQueue2->setRebalancerAction($rebalancerAction);
        $rebalancerQueue2->setAmount(0);
        $rebalancerQueue2->setQuantity(12);
        $rebalancerQueue2->setStatus(RebalancerQueue::STATUS_BUY);
        $rebalancerQueue2->setSecurity($securityVCIT);
        $rebalancerQueue2->setSystemClientAccount($account480888811);

        $manager->persist($rebalancerQueue2);

        $rebalancerQueue3 = new RebalancerQueue();
        $rebalancerQueue3->setRebalancerAction($rebalancerAction);
        $rebalancerQueue3->setAmount(0);
        $rebalancerQueue3->setQuantity(1);
        $rebalancerQueue3->setStatus(RebalancerQueue::STATUS_BUY);
        $rebalancerQueue3->setSystemClientAccount($account122223334);
        $rebalancerQueue3->setSecurity($securityBND);

        $manager->persist($rebalancerQueue3);

        $rebalancerQueue4 = new RebalancerQueue();
        $rebalancerQueue4->setRebalancerAction($rebalancerAction);
        $rebalancerQueue4->setAmount(0);
        $rebalancerQueue4->setQuantity(20);
        $rebalancerQueue4->setStatus(RebalancerQueue::STATUS_SELL);
        $rebalancerQueue4->setLot($lot1);
        $rebalancerQueue4->setSecurity($securityRWX);
        $rebalancerQueue4->setSystemClientAccount($account916985328);

        $manager->persist($rebalancerQueue4);

        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 22;
    }
}
