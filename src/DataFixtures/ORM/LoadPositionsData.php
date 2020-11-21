<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Lot;
use App\Entity\Position;
use App\Model\AbstractCsvFixture;

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
        $lots = $manager->getRepository('App\Entity\Lot')->findAll();

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

                        if ('IDA12' === $lot->getSecurity()->getSymbol()) {
                            $amount = $lot->getAmount();
                            $qty = $lot->getQuantity();
                        } else {
                            if (Lot::LOT_INITIAL === $lot->getStatus()) {
                                $amount += $lot->getAmount();
                                $qty += $lot->getQuantity();
                            }
                            if (Lot::LOT_IS_OPEN === $lot->getStatus()) {
                                $amount += $lot->getAmount();
                                $qty += $lot->getQuantity();
                            }
                        }
                    }
                    $posStatus = 0;
                    if (Lot::LOT_IS_OPEN === $status) {
                        $posStatus = Position::POSITION_STATUS_IS_OPEN;
                    }
                    if (Lot::LOT_INITIAL === $status) {
                        $posStatus = Position::POSITION_STATUS_INITIAL;
                    }
                    if (Lot::LOT_CLOSED === $status) {
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
