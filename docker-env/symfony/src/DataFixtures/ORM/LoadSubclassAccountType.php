<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.09.12
 * Time: 13:55
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\SubclassAccountType;

class LoadSubclassAccountType extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $type1 = new SubclassAccountType();
        $type1->setName('Roth IRA');
        $manager->persist($type1);

        $type2 = new SubclassAccountType();
        $type2->setName('Traditional IRA');
        $manager->persist($type2);

        $type3 = new SubclassAccountType();
        $type3->setName('Taxable');
        $manager->persist($type3);

        $manager->flush();

        $this->addReference('subclass-account-type-1', $type1);
        $this->addReference('subclass-account-type-2', $type2);
        $this->addReference('subclass-account-type-3', $type3);
    }

    public function getOrder()
    {
        return 1;
    }
}
