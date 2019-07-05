<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 15.07.13
 * Time: 14:09
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\BillingSpec;
use App\Entity\Fee;

class LoadFeeData extends AbstractFixture implements OrderedFixtureInterface
{
    protected $maxTier = 1000000000000;

    protected $data = [
        ['fee_without_retirement' => 0.0212, 'tier_top' => 1000],
        ['fee_without_retirement' => 0.0105, 'tier_top' => 10000],
        ['fee_without_retirement' => 0.0025, 'tier_top' => 1000000000000],
    ];

    public function load(ObjectManager $manager)
    {
        /** @var BillingSpec $tierSpec */
        $tierSpec = $this->getReference('ria-tier-spec');

        foreach ($this->data as $feeData) {
            $fee = new Fee();
            $fee->setFeeWithoutRetirement($feeData['fee_without_retirement'])
                ->setBillingSpec($tierSpec)
                ->setTierTop($feeData['tier_top']);
            $tierSpec->addFee($fee);

            $manager->persist($fee);
        }

        /** @var BillingSpec $tierSpec */
        $tierSpec = $this->getReference('wealthbot-ria-tier-spec');

        foreach ($this->data as $feeData) {
            $fee = new Fee();
            $fee->setFeeWithoutRetirement($feeData['fee_without_retirement'])
                ->setTierTop($feeData['tier_top']);
            $tierSpec->addFee($fee);

            $manager->persist($fee);
        }

        /** @var BillingSpec $flatSpec */
        $flatSpec = $this->getReference('wealthbot-ria-flat-spec');
        $fee = new Fee();
        $fee->setFeeWithoutRetirement(300);
        $fee->setTierTop($this->maxTier);
        $flatSpec->addFee($fee);
        $manager->persist($fee);

        $manager->flush();
    }

    public function getOrder()
    {
        return 10;
    }
}
