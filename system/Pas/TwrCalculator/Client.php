<?php

namespace Pas\TwrCalculator;

use Model\Pas\PortfolioTwrValue as PortfolioTwrValueModel;
use Model\Pas\PortfolioTwrPeriod as PortfolioTwrPeriodModel;
use Model\Pas\Repository\PortfolioTwrValueRepository as PortfolioTwrValueRepo;
use Model\Pas\Repository\PortfolioTwrPeriodRepository as PortfolioTwrPeriodRepo;
use Pas\TwrCalculator\Actual\PortfolioNetRule as PortfolioActualNetRule;
use Pas\TwrCalculator\Actual\PortfolioGrossRule as PortfolioActualGrossRule;
use Lib\Util;

class Client
{
    protected $key;

    /**
     * @var float
     */
    protected $sumTodayClientValue;

    /**
     * @var float
     */
    protected $sumYesterdayClientValue;

    /**
     * @var float
     */
    protected $sumYesterdayClientNetCashFlow;

    /**
     * @var float
     */
    protected $sumYesterdayClientGrossCashFlow;

    /**
     * @param $key
     * @param \DateTime $curDate
     */
    public function __construct($key, \DateTime $curDate)
    {
        $this->key = $key;
        $this->curDate = $curDate;

        $this->sumTodayClientValue
            = $this->sumYesterdayClientValue
            = $this->sumYesterdayClientNetCashFlow
            = $this->sumYesterdayClientGrossCashFlow = 0;

        $this->valueRepo = new PortfolioTwrValueRepo();
        $this->periodRepo = new PortfolioTwrPeriodRepo();
    }

    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return \DateTime
     */
    protected function getCurDate()
    {
        return $this->curDate;
    }

    /**
     * @return \DateTime
     */
    protected function getModifyDate($period)
    {
        $date = clone $this->getCurDate();

        return $date->modify($period);
    }

    public function getSumTodayClientValue()
    {
        return $this->sumTodayClientValue;
    }

    public function getSumYesterdayClientValue()
    {
        return $this->sumYesterdayClientValue;
    }

    public function getSumYesterdayClientNetCashFlow()
    {
        return $this->sumYesterdayClientNetCashFlow;
    }

    public function getSumYesterdayClientGrossCashFlow()
    {
        return $this->sumYesterdayClientGrossCashFlow;
    }

    public function addTodayValue($value)
    {
        $this->sumTodayClientValue += $value;

        return $this;
    }

    public function addYesterdayValue($value)
    {
        $this->sumYesterdayClientValue += $value;

        return $this;
    }

    public function addYesterdayNetCashFlow($value)
    {
        $this->sumYesterdayClientNetCashFlow += $value;

        return $this;
    }

    public function addYesterdayGrossCashFlow($value)
    {
        $this->sumYesterdayClientGrossCashFlow += $value;

        return $this;
    }

    public function calculateValue()
    {
        $model = new PortfolioTwrValueModel();
        $model->setDate($this->getCurDate()->format('Y-m-d'));
        $model->setClientId($this->getKey());
        $model->setNetValue(Functions::calculateTodayTwr($this->getSumTodayClientValue(), $this->getSumYesterdayClientNetCashFlow(), $this->getSumYesterdayClientValue()));
        $model->setGrossValue(Functions::calculateTodayTwr($this->getSumTodayClientValue(), $this->getSumYesterdayClientGrossCashFlow(), $this->getSumYesterdayClientValue()));

        // Save portfolio value
        return ($model->getNetValue() && $model->getGrossValue()) ? $this->valueRepo->save($model) : null;
    }

    public function calculatePeriod()
    {
        $curDate   = $this->getCurDate();
        $last1Year = $this->getModifyDate('-1 year');
        $last3Year = $this->getModifyDate('-3 year');
        $actualTwr = new Actual();

        // NET
        $model = new PortfolioTwrPeriodModel();
        $model->setNetMtd($actualTwr->rule(new PortfolioActualNetRule($this->getKey(), Util::firstDayOf('month', $curDate), $curDate)));
        $model->setNetQtd($actualTwr->rule(new PortfolioActualNetRule($this->getKey(), Util::firstDayOf('quarter', $curDate), $curDate)));
        $model->setNetYtd($actualTwr->rule(new PortfolioActualNetRule($this->getKey(), Util::firstDayOf('year', $curDate), $curDate)));
        $model->setNetYr1($actualTwr->rule(new PortfolioActualNetRule($this->getKey(), $last1Year, $curDate)));
        $model->setNetYr3($actualTwr->rule(new PortfolioActualNetRule($this->getKey(), $last3Year, $curDate)));
        $model->setNetSinceInception($actualTwr->rule(new PortfolioActualNetRule($this->getKey())));

        // GROSS
        $model->setGrossMtd($actualTwr->rule(new PortfolioActualGrossRule($this->getKey(), Util::firstDayOf('month', $curDate), $curDate)));
        $model->setGrossQtd($actualTwr->rule(new PortfolioActualGrossRule($this->getKey(), Util::firstDayOf('quarter', $curDate), $curDate)));
        $model->setGrossYtd($actualTwr->rule(new PortfolioActualGrossRule($this->getKey(), Util::firstDayOf('year', $curDate), $curDate)));
        $model->setGrossYr1($actualTwr->rule(new PortfolioActualGrossRule($this->getKey(), $last1Year, $curDate)));
        $model->setGrossYr3($actualTwr->rule(new PortfolioActualGrossRule($this->getKey(), $last3Year, $curDate)));
        $model->setGrossSinceInception($actualTwr->rule(new PortfolioActualGrossRule($this->getKey())));
        $model->setClientId($this->getKey());

        // Save portfolio period
        return $this->periodRepo->save($model);
    }

    public function process()
    {
        // Calculate portfolio values
        $this->calculateValue();

        // Calculate portfolio period values
        $this->calculatePeriod();
    }
}