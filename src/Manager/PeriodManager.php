<?php

namespace App\Manager;

class PeriodManager
{
    /**
     * @var array
     */
    private $datesCache = [];

    /**
     * Get quarter period.
     *
     * @param int      $year
     * @param int|null $quarter
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    public function getQuarterPeriod($year, $quarter = null)
    {
        $endDate = new \DateTime("{$year}-12-31");
        $startDate = new \DateTime("{$year}-01-01");

        $periodInt = new \DateInterval('P3M');
        $period = new \DatePeriod($startDate, $periodInt, $endDate);

        $i = 1;
        $periods = [];
        foreach ($period as $val) {
            $end = clone $val;
            $end->add(new \DateInterval('P3M'));

            $periods[$i] = ['startDate' => $val, 'endDate' => $end];

            ++$i;
        }

        if (!empty($quarter)) {
            if (isset($periods[$quarter])) {
                return [$quarter => $periods[$quarter]];
            }

            throw new \InvalidArgumentException();
        }

        return $periods;
    }

    /**
     * Returning \DateTime array with keys: startDate, endDate.
     *
     * Note that endDate is not included in period. I.e you have to use conditions like:
     *
     *      startDate <= date < endDate
     *
     * @param $year
     * @param null $quarter
     *
     * @return \DateTime[]
     */
    public function getPeriod($year, $quarter = null)
    {
        $q = ($quarter ? $quarter : 0);

        if (isset($this->datesCache[$year]) && isset($this->datesCache[$year][$q])) {
            return $this->datesCache[$year][$q];
        }

        $periods = $this->getQuarterPeriod($year);

        if (!$quarter) {
            $result = [
                'startDate' => $periods[1]['startDate'],
                'endDate' => $periods[4]['endDate'],
            ];
        } else {
            $result = [
                'startDate' => $periods[$quarter]['startDate'],
                'endDate' => $periods[$quarter]['endDate'],
            ];
        }

        if (!isset($this->datesCache[$year])) {
            $this->datesCache[$year] = [];
        }

        $this->datesCache[$year][$q] = $result;

        return $result;
    }

    /**
     * @param \DateTime $date
     *
     * @return array
     */
    public function getPreviousQuarter(\DateTime $date)
    {
        $newDate = new \DateTime();
        $newDate->setTimestamp($date->getTimestamp());
        $newDate->modify('-3 month');

        return [
            'year' => $newDate->format('Y'),
            'quarter' => ceil($newDate->format('m') / 3),
        ];
    }

    /**
     * @param $period
     * @param \DateTime $date
     *
     * @return \DateTime
     *
     * @throws \InvalidArgumentException
     */
    public function firstDayOf($period, \DateTime $date = null)
    {
        $period = strtolower($period);
        $validPeriods = ['year', 'quarter', 'month', 'week'];

        if (!in_array($period, $validPeriods)) {
            throw new \InvalidArgumentException('Period must be one of: '.implode(', ', $validPeriods));
        }

        $newDate = (null === $date) ? new \DateTime() : clone $date;

        switch ($period) {
            case 'year':
                $newDate->modify('first day of january '.$newDate->format('Y'));
                break;
            case 'quarter':
                $month = $newDate->format('n');

                if ($month < 4) {
                    $newDate->modify('first day of january '.$newDate->format('Y'));
                } elseif ($month > 3 && $month < 7) {
                    $newDate->modify('first day of april '.$newDate->format('Y'));
                } elseif ($month > 6 && $month < 10) {
                    $newDate->modify('first day of july '.$newDate->format('Y'));
                } elseif ($month > 9) {
                    $newDate->modify('first day of october '.$newDate->format('Y'));
                }
                break;
            case 'month':
                $newDate->modify('first day of this month');
                break;
            case 'week':
                $newDate->modify(('0' === $newDate->format('w')) ? 'monday last week' : 'monday this week');
                break;
        }
        $newDate->setTime(0, 0, 0);

        return $newDate;
    }

    /**
     * @param $period
     * @param \DateTime $date
     *
     * @return \DateTime
     *
     * @throws \InvalidArgumentException
     */
    public function lastDayOf($period, \DateTime $date = null)
    {
        $period = strtolower($period);
        $validPeriods = ['year', 'quarter', 'month', 'week'];

        if (!in_array($period, $validPeriods)) {
            throw new \InvalidArgumentException('Period must be one of: '.implode(', ', $validPeriods));
        }

        $newDate = (null === $date) ? new \DateTime() : clone $date;

        switch ($period) {
            case 'year':
                $newDate->modify('last day of december '.$newDate->format('Y'));
                break;
            case 'quarter':
                $month = $newDate->format('n');

                if ($month < 4) {
                    $newDate->modify('last day of march '.$newDate->format('Y'));
                } elseif ($month > 3 && $month < 7) {
                    $newDate->modify('last day of june '.$newDate->format('Y'));
                } elseif ($month > 6 && $month < 10) {
                    $newDate->modify('last day of september '.$newDate->format('Y'));
                } elseif ($month > 9) {
                    $newDate->modify('last day of december '.$newDate->format('Y'));
                }
                break;
            case 'month':
                $newDate->modify('last day of this month');
                break;
            case 'week':
                $newDate->modify(('0' === $newDate->format('w')) ? 'now' : 'sunday this week');
                break;
        }

        return $newDate;
    }
}
