<?php

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wealthbot\ClientBundle\Entity\Lot;
use Wealthbot\ClientBundle\Entity\Position;
use Wealthbot\FixturesBundle\Model\AbstractCsvFixture;

class LoadPositionsData extends AbstractCsvFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Lot[] $lots */
        $lots = $manager->getRepository('WealthbotClientBundle:Lot')->findAll();

        //list - array[account][security][date] = lots
        $list = [];
        foreach ($lots as $lot) {
            $date = $lot->getDate()->format('Y-m-d');
            $securityId = $lot->getSecurity()->getId();
            $accountId = $lot->getClientSystemAccount()->getId();

            if (!array_key_exists($accountId, $list)) {
                $list[$accountId] = [];
            }
            if (!array_key_exists($securityId, $list[$accountId])) {
                $list[$accountId][$securityId] = [];
            }
            if (!array_key_exists($date, $list[$accountId][$securityId])) {
                $list[$accountId][$securityId][$date] = [];
            }

            $list[$accountId][$securityId][$date][] = $lot;
        }

        foreach ($list as $accountId => $listSec) {
            foreach ($listSec as $securityId => $listDat) {
                foreach ($listDat as $date => $lots) {
                    if (!count($lots)) {
                        continue;
                    }
                    $status = $lots[0]->getStatus();
                    $amount = 0;
                    $qty = 0;
                    $lot = $lots[0];
                    foreach ($lots as $lot) {
                        if ($lot->getStatus() !== $status) {
                            $status = Lot::LOT_IS_OPEN;
                        }

                        if ($lot->getSecurity()->getSymbol() === 'IDA12') {
                            $amount = $lot->getAmount();
                            $qty = $lot->getQuantity();
                        } else {
                            if ($lot->getStatus() === Lot::LOT_INITIAL) {
                                $amount += $lot->getAmount();
                                $qty += $lot->getQuantity();
                            }
                            if ($lot->getStatus() === Lot::LOT_IS_OPEN) {
                                $amount += $lot->getAmount();
                                $qty += $lot->getQuantity();
                            }
                        }
                    }
                    $posStatus = 0;
                    if ($status === Lot::LOT_IS_OPEN) {
                        $posStatus = Position::POSITION_STATUS_IS_OPEN;
                    }
                    if ($status === Lot::LOT_INITIAL) {
                        $posStatus = Position::POSITION_STATUS_INITIAL;
                    }
                    if ($status === Lot::LOT_CLOSED) {
                        $posStatus = Position::POSITION_STATUS_IS_CLOSE;
                    }

                    $position = new Position();
                    $position->setStatus($posStatus);
                    $position->setDate(new \DateTime($date));
                    $position->setAmount($amount);
                    $position->setQuantity($qty);
                    $position->setClientSystemAccount($lot->getClientSystemAccount());
                    $position->setSecurity($lot->getSecurity());
                    $position->setLots($lots);
                    foreach ($lots as $lot) {
                        $lot->setPosition($position);
                    }

                    $manager->persist($position);
                }
            }
        }
        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 11;
    }
}
