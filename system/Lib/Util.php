<?php

namespace Lib; 

class Util
{
    /**
     * @param \DateTime $date
     * @return array
     */
    public static function getPreviousQuarter(\DateTime $date)
    {
        $newDate = new \DateTime();
        $newDate->setTimestamp($date->getTimestamp());
        $newDate->modify("-3 month");

        return array(
            'year' => $newDate->format('Y'),
            'quarter' => ceil($newDate->format('m') / 3)
        );
    }

    /**
     * @param $period
     * @param \DateTime $date
     * @return \DateTime
     * @throws \InvalidArgumentException
     */
    public static function firstDayOf($period, \DateTime $date = null)
    {
        $period = strtolower($period);
        $validPeriods = array('year', 'quarter', 'month', 'week');

        if ( ! in_array($period, $validPeriods)) {
            throw new \InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));
        }

        $newDate = ($date === null) ? new \DateTime() : clone $date;

        switch ($period) {
            case 'year':
                $newDate->modify('first day of january ' . $newDate->format('Y'));
                break;
            case 'quarter':
                $month = $newDate->format('n') ;
                if ($month < 4) {
                    $newDate->modify('first day of january ' . $newDate->format('Y'));
                } elseif ($month > 3 && $month < 7) {
                    $newDate->modify('first day of april ' . $newDate->format('Y'));
                } elseif ($month > 6 && $month < 10) {
                    $newDate->modify('first day of july ' . $newDate->format('Y'));
                } elseif ($month > 9) {
                    $newDate->modify('first day of october ' . $newDate->format('Y'));
                }
                break;
            case 'month':
                $newDate->modify('first day of this month');
                break;
            case 'week':
                $newDate->modify(($newDate->format('w') === '0') ? 'monday last week' : 'monday this week');
                break;
        }

        return $newDate;
    }

    /**
     * @param $period
     * @param \DateTime $date
     * @return \DateTime
     * @throws \InvalidArgumentException
     */
    public static function lastDayOf($period, \DateTime $date = null)
    {
        $period = strtolower($period);
        $validPeriods = array('year', 'quarter', 'month', 'week');

        if ( ! in_array($period, $validPeriods)) {
            throw new \InvalidArgumentException('Period must be one of: ' . implode(', ', $validPeriods));
        }

        $newDate = ($date === null) ? new \DateTime() : clone $date;

        switch ($period) {
            case 'year':
                $newDate->modify('last day of december ' . $newDate->format('Y'));
                break;
            case 'quarter':
                $month = $newDate->format('n') ;
                if ($month < 4) {
                    $newDate->modify('last day of march ' . $newDate->format('Y'));
                } elseif ($month > 3 && $month < 7) {
                    $newDate->modify('last day of june ' . $newDate->format('Y'));
                } elseif ($month > 6 && $month < 10) {
                    $newDate->modify('last day of september ' . $newDate->format('Y'));
                } elseif ($month > 9) {
                    $newDate->modify('last day of december ' . $newDate->format('Y'));
                }
                break;
            case 'month':
                $newDate->modify('last day of this month');
                break;
            case 'week':
                $newDate->modify(($newDate->format('w') === '0') ? 'now' : 'sunday this week');
                break;
        }

        return $newDate;
    }

    public static function floatcmp($a, $b)
    {
        $a = (float)$a;
        $b = (float)$b;

        return abs(($a - $b) / $b) < 0.00001;
    }
}