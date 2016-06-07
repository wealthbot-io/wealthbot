<?php

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wealthbot\AdminBundle\Entity\Transaction;
use Wealthbot\AdminBundle\Repository\SecurityRepository;
use Wealthbot\ClientBundle\Entity\Lot;
use Wealthbot\FixturesBundle\Model\AbstractCsvFixture;

class LoadLotData extends AbstractCsvFixture implements OrderedFixtureInterface
{
    /** @var  Lot[] */
    private $lots;

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var Transaction[] $transactions */
        $transactions = $manager->getRepository('WealthbotAdminBundle:Transaction')->findBy([], ['id' => 'ASC']);

        $this->lots = [];

        foreach ($transactions as $transaction) {
            $date = $transaction->getTxDate();
            if ($transaction->getTransactionType()->getName() === 'BUY') {
                if ($transaction->getSecurity()->getSymbol() === 'IDA12') {
                    //FOR MONEY (MF)
                    $oldLot = $this->findLot($date, $transaction->getSecurity(), $transaction->getAccount(), 0, self::FINDLOT_MF);
                    if (!$oldLot) {
                        $lot = new Lot();
                        $lot->setSecurity($transaction->getSecurity());
                        $lot->setClientSystemAccount($transaction->getAccount());
                        $lot->setPosition(null);
                        $lot->setStatus(Lot::LOT_INITIAL);
                        $lot->setWashSale(false);
                        $lot->setCostBasisKnown(false);
                        $lot->setCostBasis($transaction->getNetAmount());
                        $lot->setQuantity($transaction->getNetAmount());
                        $lot->setAmount($transaction->getNetAmount());
                        $lot->setDate($date);
                        $transaction->setLot($lot);

                        $manager->persist($lot);
                        $this->lots[] = $lot;
                    } else {
                        if ($oldLot->getDate()->format('Y-m-d') === $date->format('Y-m-d')) {
                            $amount = $oldLot->getAmount() + $transaction->getNetAmount();
                            $oldLot->setAmount($amount);
                            $oldLot->setQuantity($amount);
                            $oldLot->setCostBasis($amount);
                            $transaction->setLot($oldLot);
                        } else {
                            $amount = $oldLot->getAmount() + $transaction->getNetAmount();
                            $lot = new Lot();
                            $lot->setSecurity($transaction->getSecurity());
                            $lot->setClientSystemAccount($transaction->getAccount());
                            $lot->setPosition(null);
                            $lot->setStatus(Lot::LOT_IS_OPEN);
                            $lot->setWashSale(false);
                            $lot->setCostBasisKnown(false);
                            $lot->setAmount($amount);
                            $lot->setQuantity($amount);
                            $lot->setCostBasis($amount);
                            $lot->setDate($date);
                            $transaction->setLot($lot);

                            $manager->persist($lot);
                            $this->lots[] = $lot;
                        }
                    }
                } else {
                    //FOR SHARES
                    $lot = new Lot();
                    $lot->setSecurity($transaction->getSecurity());
                    $lot->setClientSystemAccount($transaction->getAccount());
                    $lot->setPosition(null);
                    $lot->setStatus(Lot::LOT_INITIAL);
                    $lot->setWashSale(false);
                    $lot->setCostBasisKnown(true);
                    $lot->setCostBasis($transaction->getGrossAmount());
                    $lot->setQuantity($transaction->getQty());
                    $lot->setAmount($transaction->getNetAmount());
                    $lot->setDate($date);
                    $transaction->setLot($lot);

                    $manager->persist($lot);
                    $this->lots[] = $lot;
                }
            }
            //----------

            if ($transaction->getTransactionType()->getName() === 'SELL') {
                if ($transaction->getSecurity()->getSymbol() === 'IDA12') {
                    //FOR MONEY (MF)
                    $oldLot = $this->findLot($date, $transaction->getSecurity(), $transaction->getAccount(), 0, self::FINDLOT_MF);
                    if (!$oldLot) {
                        throw new \Exception('Error, can\'t find previous MF before SALE trn');
                    } else {
                        if ($oldLot->getDate()->format('Y-m-d') === $date->format('Y-m-d')) {
                            $amount = $oldLot->getAmount() - $transaction->getNetAmount();
                            $oldLot->setAmount($amount);
                            $oldLot->setQuantity($amount);
                            $oldLot->setCostBasis($amount);
                            $transaction->setLot($oldLot);
                        } else {
                            $amount = $oldLot->getAmount() - $transaction->getNetAmount();
                            $lot = new Lot();
                            $lot->setSecurity($transaction->getSecurity());
                            $lot->setClientSystemAccount($transaction->getAccount());
                            $lot->setPosition(null);
                            $lot->setStatus(Lot::LOT_IS_OPEN);
                            $lot->setWashSale(false);
                            $lot->setCostBasisKnown(false);
                            $lot->setAmount($amount);
                            $lot->setQuantity($amount);
                            $lot->setCostBasis($amount);
                            $lot->setDate($date);
                            $transaction->setLot($lot);

                            $manager->persist($lot);
                            $this->lots[] = $lot;
                        }
                    }
                } else {
                    //FOR SHARES
                    $initLot = $this->findLot($date, $transaction->getSecurity(), $transaction->getAccount(), $transaction->getQty(), self::FINDLOT_INITIAL);
                    if ($initLot) {
                        $gain = $transaction->getNetAmount() - $initLot->getAmount();

                        $lot = new Lot();
                        $lot->setSecurity($transaction->getSecurity());
                        $lot->setClientSystemAccount($transaction->getAccount());
                        $lot->setPosition(null);
                        $lot->setStatus(Lot::LOT_CLOSED);
                        $lot->setWashSale(false);
                        $lot->setCostBasisKnown(false);
                        $lot->setAmount($transaction->getNetAmount());
                        $lot->setQuantity($transaction->getQty());
                        $lot->setCostBasis($transaction->getGrossAmount());
                        $lot->setInitial($initLot);
                        $lot->setDate($date);
                        $lot->setRealizedGain($gain);
                        $initLot->setWasClosed(true);
                        $transaction->setLot($lot);

                        $manager->persist($lot);
                        $this->lots[] = $lot;
                    } else {
                        throw new \Exception('Not found opened LOT for trn.');
                    }
                }
            }
        }

        /** @var SecurityRepository $securityRepo */
        $securityRepo = $manager->getRepository('WealthbotAdminBundle:Security');

        //FOR MUNI
        $date = new \DateTime();

        $lot = new Lot();
        $lot->setSecurity($securityRepo->findOneBySymbol('VTI'));
        $lot->setClientSystemAccount($this->getReference('system-account-214888609'));
        $lot->setPosition(null);
        $lot->setStatus(Lot::LOT_INITIAL);
        $lot->setWashSale(false);
        $lot->setCostBasisKnown(false);
        $lot->setAmount(1000);
        $lot->setQuantity(100);
        $lot->setCostBasis(800);
        $lot->setInitial(null);
        $lot->setDate($date->modify('-2 months'));
        $lot->setRealizedGain(0);

        $manager->persist($lot);

        $manager->flush();

        $dates = [];
        $accounts = [];

        //next we have to create additional lots for every LOT DAY.
        /** @var Lot[] $lots */
        $lots = $manager->getRepository('WealthbotClientBundle:Lot')->findAll();
        $lots[0]->setWasRebalancerDiff(1);
        foreach ($lots as $lot) {
            $date = $lot->getDate()->format('Y-m-d');
            if (!in_array($date, $dates)) {
                $dates[] = $date;
            }
            $accountId = $lot->getClientSystemAccount()->getId();
            if (!in_array($accountId, $accounts)) {
                $accounts[] = $accountId;
            }
        }

        foreach ($dates as $date) {
            $dateTime = new \DateTime($date);
            /** @var Lot[] $openLots */
            $openLots = $manager->getRepository('WealthbotClientBundle:Lot')->getOpenedOnDate($dateTime);
            foreach ($openLots as $lot) {
                if ($lot->getDate()->format('Y-m-d') !== $dateTime->format('Y-m-d')) {
                    $cloneLot = new Lot();
                    $cloneLot->setSecurity($lot->getSecurity());
                    $cloneLot->setClientSystemAccount($lot->getClientSystemAccount());
                    $cloneLot->setPosition(null);
                    $cloneLot->setStatus(Lot::LOT_IS_OPEN);
                    $cloneLot->setWashSale(false);
                    $cloneLot->setCostBasisKnown($lot->isCostBasisKnown());
                    $cloneLot->setAmount($lot->getAmount());
                    $cloneLot->setQuantity($lot->getQuantity());
                    $cloneLot->setCostBasis($lot->getCostBasis());
                    $cloneLot->setInitial($lot);
                    $cloneLot->setDate($dateTime);

                    $manager->persist($cloneLot);
                    $this->lots[] = $cloneLot;
                }
            }
        }

        foreach ($this->lots as $key => $lot) {
            $this->setReference('lot-'.$key, $lot);
        }

        $manager->flush();
    }

    const FINDLOT_MF = 1;
    const FINDLOT_INITIAL = 2; //not wasClosed

    /**
     * @param $date
     * @param $security
     * @param $account
     * @param bool $isMF
     *
     * @return null|Lot
     */
    public function findLot(\DateTime $date, $security, $account, $qty, $type)
    {
        if ($type === self::FINDLOT_INITIAL) {
            foreach ($this->lots as $lot) {
                if ($lot->getQuantity() === $qty
                    && $lot->getSecurity() === $security
                    && $lot->getClientSystemAccount() === $account
                    && $lot->getStatus() === Lot::LOT_INITIAL
                    && $lot->getWasClosed() === false
                    && $lot->getDate()->getTimestamp() < $date->getTimestamp()
                ) {
                    return $lot;
                }
            }
        }
        if ($type === self::FINDLOT_MF) {
            foreach ($this->lots as $lot) {
                if ($lot->getSecurity() === $security
                    && $lot->getDate()->getTimestamp() <= $date->getTimestamp()
                    && $lot->getClientSystemAccount() === $account
                ) {
                    return $lot;
                }
            }
        }

        return;
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 10;
    }
}
