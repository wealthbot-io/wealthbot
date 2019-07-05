<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\SecurityTransaction;

class LoadSecurityTransactionData extends AbstractFixture implements OrderedFixtureInterface
{
    private $securityTxsData = [
        1 => [
            'transaction_fee' => 250,
            'transaction_fee_percent' => 10,
            'minimum_buy' => 50,
            'minimum_initial_buy' => 150,
            'minimum_sell' => 50,
            'redemption_penalty_interval' => 21,
            'redemption_fee' => 15,
            'redemption_percent' => 5,
        ],
        2 => [
            'transaction_fee' => 11,
            'transaction_fee_percent' => 22,
            'minimum_buy' => 33,
            'minimum_initial_buy' => 44,
            'minimum_sell' => 55,
            'redemption_penalty_interval' => 66,
            'redemption_fee' => 77,
            'redemption_percent' => 88,
        ],
    ];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->securityTxsData[1]['security_assignment'] = $this->getReference('model-security-assignment-1');
        $this->securityTxsData[2]['security_assignment'] = $this->getReference('model-security-assignment-asset-index-0-subclass-index-1-security-VTV');

        foreach ($this->securityTxsData as $securityTxData) {
            $securityTx = new SecurityTransaction();
            $securityTx->setSecurityAssignment($securityTxData['security_assignment']);
            $securityTx->setTransactionFee($securityTxData['transaction_fee']);
            $securityTx->setTransactionFeePercent($securityTxData['transaction_fee_percent']);
            $securityTx->setMinimumBuy($securityTxData['minimum_buy']);
            $securityTx->setMinimumInitialBuy($securityTxData['minimum_initial_buy']);
            $securityTx->setMinimumSell($securityTxData['minimum_sell']);
            $securityTx->setRedemptionPenaltyInterval($securityTxData['redemption_penalty_interval']);
            $securityTx->setRedemptionFee($securityTxData['redemption_fee']);
            $securityTx->setRedemptionPercent($securityTxData['redemption_percent']);

            $manager->persist($securityTx);
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
        return 9;
    }
}
