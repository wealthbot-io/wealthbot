<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 25.02.14
 * Time: 20:12
 */

namespace Test\Manager;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Manager\BusinessCalendar;
use Test\Suit\ExtendedTestCase;

class BusinessCalendarTest extends ExtendedTestCase
{

    public function testAddBusinessDays()
    {
        $bc = new BusinessCalendar();

        $fromDate = new \DateTime('2014-02-24');
        $date = $bc->addBusinessDays($fromDate, -1);
        $dateStr = $date->format('Y-m-d');

        $this->assertEquals('2014-02-21', $dateStr);

        $fromDate = new \DateTime('2014-02-24');
        $date = $bc->addBusinessDays($fromDate, -10);
        $dateStr = $date->format('Y-m-d');
        $this->assertEquals('2014-02-07', $dateStr);

        $fromDate = new \DateTime('2014-02-21');
        $date = $bc->addBusinessDays($fromDate, 1);
        $dateStr = $date->format('Y-m-d');
        $this->assertEquals('2014-02-24', $dateStr);

        $fromDate = new \DateTime('2014-02-24');
        $date = $bc->addBusinessDays($fromDate, 10);
        $dateStr = $date->format('Y-m-d');
        $this->assertEquals('2014-03-10', $dateStr);
    }

    protected static function getFixtures()
    {
        return array('src/Wealthbot/FixturesBundle/DataFixtures/ORM/LoadHolidayData.php');
    }


}
 