<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 21.08.13
 * Time: 14:25
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\ClosingMethod;

class LoadClosingMethodData extends AbstractFixture implements OrderedFixtureInterface
{
    private $data = [
        [
            'name' => 'FIFO',
            'description' => 'First in First out',
        ],
        [
            'name' => 'LIFO',
            'description' => 'Last in First out',
        ],
        [
            'name' => 'HCLOT',
            'description' => 'High Cost',
        ],
        [
            'name' => 'LCLOT',
            'description' => 'Low Cost',
        ],
        [
            'name' => 'ACOST',
            'description' => 'Average Cost',
        ],
        [
            'name' => 'SLLOT',
            'description' => 'Selected Lot',
        ],
        [
            'name' => 'BTAX',
            'description' => 'Tax Lot O',
        ],
        [
            'name' => 'None',
            'description' => 'None',
        ],
    ];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $index => $item) {
            $closingMethod = new ClosingMethod();

            $closingMethod->setName($item['name']);
            $closingMethod->setDescription($item['description']);

            $manager->persist($closingMethod);
            $this->addReference('closing-method'.($index + 1), $closingMethod);
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
