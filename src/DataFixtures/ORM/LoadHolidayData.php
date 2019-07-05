<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Holiday;
use App\Model\AbstractCsvFixture;

class LoadHolidayData extends AbstractCsvFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $days = $this->getCsvData('calendar.csv');
        foreach ($days as $dayData) {
            $type = $dayData[1];
            if ('Open' !== $type) {
                $date = new \DateTime($dayData[0]);

                $holiday = new Holiday();
                $holiday->setDate($date);

                if ('Weekend' === $type) {
                    $holiday->setType(Holiday::HOLIDAY_TYPE_WEEKEND);
                } else {
                    $holiday->setType(Holiday::HOLIDAY_TYPE_MARKET_HOLIDAY);
                }
                $manager->persist($holiday);
            }
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
