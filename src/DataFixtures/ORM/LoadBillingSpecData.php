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
use App\Entity\User;

class LoadBillingSpecData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var User $riaUser */
        $riaUser = $this->getReference('user-ria');

        $billingSpec = new BillingSpec();
        $billingSpec->setMaster(1);
        $billingSpec->setName('Flat spec');
        $billingSpec->setOwner($riaUser);
        $billingSpec->setType(BillingSpec::TYPE_FLAT);
        $billingSpec->setMinimalFee(35);
        $manager->persist($billingSpec);

        $this->setReference('ria-flat-spec', $billingSpec);

        $billingSpec = new BillingSpec();
        $billingSpec->setMaster(0);
        $billingSpec->setName('Tier spec');
        $billingSpec->setType(BillingSpec::TYPE_TIER);
        $billingSpec->setOwner($riaUser);
        $billingSpec->setMinimalFee(20);
        $manager->persist($billingSpec);

        $this->setReference('ria-tier-spec', $billingSpec);

        $weboUserRia = $this->getReference('user-wealthbot-io-ria');

        $billingSpec = new BillingSpec();
        $billingSpec->setMaster(false);
        $billingSpec->setName('Webo Flat Spec');
        $billingSpec->setType(BillingSpec::TYPE_FLAT);
        $billingSpec->setOwner($weboUserRia);
        $billingSpec->setMinimalFee(10);
        $manager->persist($billingSpec);

        $this->setReference('wealthbot-ria-flat-spec', $billingSpec);

        $billingSpec = new BillingSpec();
        $billingSpec->setMaster(true);
        $billingSpec->setName('Webo Tier Spec');
        $billingSpec->setType(BillingSpec::TYPE_TIER);
        $billingSpec->setOwner($weboUserRia);
        $billingSpec->setMinimalFee(20);
        $manager->persist($billingSpec);

        $this->setReference('wealthbot-ria-tier-spec', $billingSpec);

        $manager->flush();

        /** @var User $client */
        $client = $this->getReference('clientN1');
        $client->setAppointedBillingSpec($billingSpec);

        /** @var User $client */
        $client = $this->getReference('clientN2');
        $client->setAppointedBillingSpec($this->getReference('wealthbot-ria-flat-spec'));

        /** @var User $client */
        $client = $this->getReference('clientN3');
        $client->setAppointedBillingSpec($this->getReference('wealthbot-ria-tier-spec'));

        /** @var User $client */
        $client = $this->getReference('clientN4');
        $client->setAppointedBillingSpec($this->getReference('wealthbot-ria-tier-spec'));

        $manager->flush();
    }

    public function getOrder()
    {
        return 9;
    }
}
