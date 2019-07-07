<?php

namespace Manager;

require_once __DIR__ . '/../../src/Wealthbot/AdminBundle/Model/AbstractBusinessCalendar.php';

use Model\WealthbotRebalancer\Holiday;
use Model\WealthbotRebalancer\Repository\HolidayRepository;
use Wealthbot\AdminBundle\Model\AbstractBusinessCalendar;

class BusinessCalendar extends AbstractBusinessCalendar
{
    private $repository;

    public function __construct()
    {
        $this->repository = new HolidayRepository();

        parent::__construct();
    }

    protected function loadHolidays(\DateTime $dateFrom, \DateTime $dateTo)
    {
        $from = clone $dateFrom;
        $to = clone $dateTo;

        $interval = $from->diff($to);
        $days = 30 + ($interval->y * 50);
        $from->modify('-' . $days . ' days');
        $to->modify('+' . $days . ' days');
        /** @var Holiday[] $holidays */
        $holidays = $this->repository->getFromTo($from, $to);
        foreach($holidays as $holiday){
            $this->holidays[$holiday->getDate()->format('m/d/Y')] = $holiday;
        }
    }

} 