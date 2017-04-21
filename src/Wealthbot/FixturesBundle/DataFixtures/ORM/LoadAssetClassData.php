<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.09.12
 * Time: 14:07
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wealthbot\AdminBundle\Entity\AssetClass;

class LoadAssetClassData extends AbstractFixture implements OrderedFixtureInterface
{
    private $assetClasses = [
        'Assert class 1',
        'Assert class 2',
        'Assert class 3',
    ];

    public function load(ObjectManager $manager)
    {
        $index = 1;
        foreach ($this->assetClasses as $name) {
            $model = $this->getReference('strategy-'.$index);

            $assetClass = new AssetClass();
            $assetClass->setName($name);
            $assetClass->setType(AssetClass::TYPE_STOCKS);
            $assetClass->setModel($model);

            $manager->persist($assetClass);

            $this->addReference('asset-class-'.$index, $assetClass);
            ++$index;
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
