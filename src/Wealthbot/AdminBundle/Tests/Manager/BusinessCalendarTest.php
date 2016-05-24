<?php

namespace Wealthbot\AdminBundle\Tests\Manager;

use Wealthbot\AdminBundle\Service\BusinessCalendar;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;

class BusinessCalendarTest extends ExtendedWebTestCase
{
    public function testIndexAction()
    {
        /** @var BusinessCalendar $bc */
        $bc = $this->container->get('wealthbot_admin.business_calendar');

        $fromDate = new \DateTime('2014-02-24');
        $date = $bc->addBusinessDays($fromDate, -1);
        $dateStr = $date->format('Y-m-d');
        $this->assertSame('2014-02-21', $dateStr);

        $fromDate = new \DateTime('2014-02-24');
        $date = $bc->addBusinessDays($fromDate, -10);
        $dateStr = $date->format('Y-m-d');
        $this->assertSame('2014-02-07', $dateStr);

        $fromDate = new \DateTime('2014-02-21');
        $date = $bc->addBusinessDays($fromDate, 1);
        $dateStr = $date->format('Y-m-d');
        $this->assertSame('2014-02-24', $dateStr);

        $fromDate = new \DateTime('2014-02-24');
        $date = $bc->addBusinessDays($fromDate, 10);
        $dateStr = $date->format('Y-m-d');
        $this->assertSame('2014-03-10', $dateStr);
    }
}
