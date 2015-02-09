<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.08.13
 * Time: 12:47
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wealthbot\AdminBundle\Entity\SecurityType;

class LoadSecurityTypeData extends AbstractFixture implements OrderedFixtureInterface
{
    private $data = array(
        array('name' => 'EQ', 'description' => 'Equity'),
        array('name' => 'FI', 'description' => 'Fixed income'),
        array('name' => 'TB', 'description' => 'T-bill'),
        array('name' => 'MU', 'description' => 'Mutual fund'),
        array('name' => 'CD', 'description' => 'CD'),
        array('name' => 'CP', 'description' => 'Commercial paper'),
        array('name' => 'OP', 'description' => 'Option'),
        array('name' => 'UI', 'description' => 'Unit trust'),
        array('name' => 'MB', 'description' => 'Mortgaged backed'),
        array('name' => 'OT', 'description' => 'User defined'),
        array('name' => 'MF', 'description' => 'Money'),
        array('name' => 'IN', 'description' => 'Index'),
    );

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        foreach ($this->data as $item) {
            $securityType = new SecurityType();
            $securityType->setName($item['name']);
            $securityType->setDescription($item['description']);

            $manager->persist($securityType);
            $this->setReference('security-type-' . $item['name'], $securityType);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    function getOrder()
    {
        return 1;
    }

}