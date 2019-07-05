<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 28.08.13
 * Time: 14:32
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\SecurityPrice;

class LoadSecurityPriceData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $securities = $manager->getRepository('App\Entity\Security')->findAll();
        $securityPrice = null;

        mt_srand(0);
        foreach ($securities as $security) {
            $iterations = 6;
            $firstDate = new \DateTime('04-01-2013 08:05:00');

            for ($i = 0; $i < $iterations; ++$i) {
                $securityPrice = new SecurityPrice();

                $securityPrice->setSecurity($security);
                $securityPrice->setSource('admin');
                $securityPrice->setPrice($this->getRandom(25, 150));
                $securityPrice->setIsCurrent(false);

                $currentDate = clone $firstDate->add(new \DateInterval('P7D'));
                $securityPrice->setDatetime($currentDate);
                $manager->persist($securityPrice);
            }

            if ($securityPrice) {
                $securityPrice->setIsCurrent(true);
                $manager->persist($securityPrice);
            }
        }

        $manager->flush();
    }

    private function getRandom($min, $max)
    {
        $factor = (float) mt_rand() / (float) mt_getrandmax();
        $random = mt_rand($min, $max) * $factor;

        $result = (($random + $min) > $max) ? $random : ($random + $min);

        return round($result, 2);
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 5;
    }
}
