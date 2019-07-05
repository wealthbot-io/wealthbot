<?php

namespace App\Model;

abstract class AbstractBusinessCalendar
{
    /** @var array */
    protected $holidays;

    const SECONDS_IN_DAY = 86400;

    public function __construct()
    {
        $this->holidays = [];
    }

    /**
     * Adds or subtracts business days from date.
     * Returns date with same time as in "date".
     *
     * @param \DateTime $date
     * @param $daysCount
     *
     * @return \DateTime
     */
    public function addBusinessDays(\DateTime $date, $daysCount)
    {
        if (0 === $daysCount) {
            return $date;
        }

        $date1 = clone $date;
        $date1->setTime(0, 0, 0);
        $timeDiff = $date->getTimestamp() - $date1->getTimestamp();

        $timeFrom = $date1->getTimestamp();
        $timeTo = $timeFrom + self::SECONDS_IN_DAY * $daysCount;

        $dateFrom = new \DateTime();
        $dateFrom->setTimestamp($timeFrom);
        $dateTo = new \DateTime();
        $dateTo->setTimestamp($timeTo);

        if ($daysCount < 0) {
            $this->loadHolidays($dateTo, $dateFrom);
        } else {
            $this->loadHolidays($dateFrom, $dateTo);
        }
        $sign = ($daysCount < 0 ? -1 : 1);

        $tryHolidays = true;
        while ($tryHolidays) {
            $holidays = 0;
            while ($timeFrom !== $timeTo) {
                $timeFrom += $sign * self::SECONDS_IN_DAY;
                $dateFrom->setTimestamp($timeFrom);
                $dateStr = $dateFrom->format('m/d/Y');
                if (array_key_exists($dateStr, $this->holidays)) {
                    ++$holidays;
                }
            }
            $tryHolidays = ($holidays > 0);
            $timeTo += $sign * $holidays * self::SECONDS_IN_DAY;
        }

        $dateTo->setTimestamp($timeFrom + $timeDiff);

        return $dateTo;
    }

    abstract protected function loadHolidays(\DateTime $dateFrom, \DateTime $dateTo);
}
