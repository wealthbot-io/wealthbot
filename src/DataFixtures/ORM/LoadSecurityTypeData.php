<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.08.13
 * Time: 12:47
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\SecurityType;

class LoadSecurityTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    private $data = [
        ['name' => 'EQ', 'description' => 'Equity'],
        ['name' => 'FI', 'description' => 'Fixed income'],
        ['name' => 'TB', 'description' => 'T-bill'],
        ['name' => 'MU', 'description' => 'Mutual fund'],
        ['name' => 'CD', 'description' => 'CD'],
        ['name' => 'CP', 'description' => 'Commercial paper'],
        ['name' => 'OP', 'description' => 'Option'],
        ['name' => 'UI', 'description' => 'Unit trust'],
        ['name' => 'MB', 'description' => 'Mortgaged backed'],
        ['name' => 'OT', 'description' => 'User defined'],
        ['name' => 'MF', 'description' => 'Money'],
        ['name' => 'IN', 'description' => 'Index'],
    ];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $item) {
            $securityType = new SecurityType();
            $securityType->setName($item['name']);
            $securityType->setDescription($item['description']);

            $manager->persist($securityType);
            $this->setReference('security-type-'.$item['name'], $securityType);
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
