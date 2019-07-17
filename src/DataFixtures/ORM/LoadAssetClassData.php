<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.09.12
 * Time: 14:07
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\AssetClass;

class LoadAssetClassData extends AbstractFixture implements OrderedFixtureInterface
{
    private $assetClasses = [
        'Asset class 1',
        'Asset class 2',
        'Asset class 3',
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
