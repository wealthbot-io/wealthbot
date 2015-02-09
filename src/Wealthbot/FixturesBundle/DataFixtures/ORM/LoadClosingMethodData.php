<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 21.08.13
 * Time: 14:25
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Wealthbot\AdminBundle\Entity\ClosingMethod;

class LoadClosingMethodData extends AbstractFixture implements OrderedFixtureInterface
{
    private $data = array(
        array(
            'name' => 'FIFO',
            'description' => 'First in First out'
        ),
        array(
            'name' => 'LIFO',
            'description' => 'Last in First out'
        ),
        array(
            'name' => 'HCLOT',
            'description' => 'High Cost'
        ),
        array(
            'name' => 'LCLOT',
            'description' => 'Low Cost'
        ),
        array(
            'name' => 'ACOST',
            'description' => 'Average Cost'
        ),
        array(
            'name' => 'SLLOT',
            'description' => 'Selected Lot'
        ),
        array(
            'name' => 'BTAX',
            'description' => 'Tax Lot O'
        ),
        array(
            'name' => 'None',
            'description' => 'None'
        ),
    );

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        foreach ($this->data as $index => $item) {
            $closingMethod = new ClosingMethod();

            $closingMethod->setName($item['name']);
            $closingMethod->setDescription($item['description']);

            $manager->persist($closingMethod);
            $this->addReference('closing-method' . ($index + 1), $closingMethod);
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