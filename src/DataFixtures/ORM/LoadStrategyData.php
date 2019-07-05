<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vova
 * Date: 13.06.13
 * Time: 13:40
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\CeModel;

class LoadStrategyData extends AbstractFixture implements OrderedFixtureInterface
{
    private $strategyModels = ['100 Bonds', '10/90', '20/80', '30/70', '40/60', '50/50', '60/40', '70/30', '80/20', '90/10', '100 Stocks'];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->strategyModels as $index => $value) {
            $ceModel = new CeModel();
            $ceModel->setName($value);
            $ceModel->setType(CeModel::TYPE_CUSTOM);
            $ceModel->setRiskRating(0);

            $manager->persist($ceModel);
            $this->addReference('strategy-'.($index + 1), $ceModel);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
