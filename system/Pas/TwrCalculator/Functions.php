<?php

namespace Pas\TwrCalculator;

class Functions
{
    /**
     * Calculate today's TWR value
     *
     * @param float $todayValue
     * @param float $yesterdayCashFlow
     * @param float $yesterdayValue
     * @throws \RangeException
     * @return float
     */
    public static function calculateTodayTwr($todayValue, $yesterdayCashFlow, $yesterdayValue)
    {
        if ($yesterdayValue == 0) {
            return 0;
        }

        $todayTWR = (((float) $todayValue - (float) $yesterdayCashFlow) / (float) $yesterdayValue);

        return $todayTWR ? ($todayTWR - 1.0) : $todayTWR;
    }

    /**
     * Calculate actual TWR for period
     *
     * @param array $twrValues
     * @return float
     */
    public static function calculateActualTwr(array $twrValues)
    {
        $twrList = $twrValues;
        $actualTWR = 1.0;

        foreach ($twrList as $twrItem) {
            $actualTWR *= ((float) $twrItem['value'] + 1.0);
        }

        $actualTWR -= 1.0;

        return $actualTWR * 100;
    }

    /**
     * Calculate annualized TWR
     *
     * @param $actualTwr
     * @param $daysInTotalReviewPeriod
     * @throws \RangeException
     * @return int
     */
    public static function calculateAnnualizedTwr($actualTwr, $daysInTotalReviewPeriod)
    {
        if ($daysInTotalReviewPeriod == 0) {
            throw new \RangeException('$daysInTotalReviewPeriod can\'t be 0.');
        }

        return ((1.0 + (float)$actualTwr) ^ (365 / $daysInTotalReviewPeriod ) - 1.0) * 100.0;
    }

    /**
     * @param float $contribution
     * @param float $withdrawal
     * @return float mixed
     */
    public static function calculateCashFlow($contribution, $withdrawal)
    {
        return $contribution - $withdrawal;
    }
}