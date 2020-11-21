<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 15.07.13
 * Time: 12:55
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\BillingSpec;
use App\Entity\Fee;
use App\Entity\Group;
use App\Entity\User;

class LoadAdminData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adminUser = $this->createUser();
        $manager->persist($adminUser);
        $this->addReference('user-admin', $adminUser);

        $this->createAdminFees($manager, $adminUser);
        $this->createGroupAll($manager);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }

    private function createUser()
    {
        $adminUser = new User();
        $adminUser->setUsername('webo');
        $adminUser->setEmail('webo@example.com');
        $adminUser->setPlainPassword('weboDemo32');
        $adminUser->setEnabled(true);
        $adminUser->setRoles(['ROLE_SUPER_ADMIN']);

        return $adminUser;
    }

    private function createAdminFees(ObjectManager $manager, User $adminUser)
    {
        $fees = [
            ['fee_with_retirement' => 0.0040, 'fee_without_retirement' => 0.0025, 'tier_top' => Fee::INFINITY],
        ];

        $adminBillingSpec = new BillingSpec();
        $adminBillingSpec->setName('Admin Billing Spec for new RIA');
        $adminBillingSpec->setMinimalFee(0);
        $adminBillingSpec->setType(BillingSpec::TYPE_TIER);
        $adminBillingSpec->setMaster(true);
        $adminBillingSpec->setOwner(null);

        foreach ($fees as $feeRow) {
            $fee = new Fee();
            $fee->setFeeWithRetirement($feeRow['fee_with_retirement']);
            $fee->setFeeWithoutRetirement($feeRow['fee_without_retirement']);
            $fee->setTierTop($feeRow['tier_top']);

            $adminBillingSpec->addFee($fee);
        }
        $manager->persist($adminBillingSpec);
    }

    private function createGroupAll(ObjectManager $manager)
    {
        $group = new Group('All');
        $group->setName('All');
        $manager->persist($group);

        $this->addReference('group-all', $group);
    }
}
