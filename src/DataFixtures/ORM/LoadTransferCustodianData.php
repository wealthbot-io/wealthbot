<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.08.13
 * Time: 19:35
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\TransferCustodian;
use App\Model\AbstractCsvFixture;

class LoadTransferCustodianData extends AbstractCsvFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $data = $this->getCsvData('transfer_custodians.csv');
        foreach ($data as $index => $item) {
            if (0 === $index) {
                continue;
            }

            $name = trim($item[0]);

            $transferCustodian = new TransferCustodian();
            $transferCustodian->setName($name);

            $manager->persist($transferCustodian);
            $this->setReference('transfer-custodian-'.$index, $transferCustodian);
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
        return 1;
    }
}
