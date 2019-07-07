<?php

namespace Pas\TwrCalculator;

use Model\Pas\Repository\TransactionRepository as TransactionRepo;
use Model\Pas\Repository\ClientAccountValueRepository as ClientAccountValueRepo;
use Model\Pas\Repository\AccountTwrValueRepository as AccountTwrValueRepo;
use Model\Pas\Repository\AccountTwrPeriodRepository as AccountTwrPeriodRepo;
use Model\Pas\AccountTwrValue as AccountTwrValueModel;
use Model\Pas\AccountTwrPeriod as AccountTwrPeriodModel;
use Model\Pas\SystemClientAccount as SystemClientAccountModel;
use Pas\TwrCalculator\Actual\AccountNetRule as AccountActualNetRule;
use Pas\TwrCalculator\Actual\AccountGrossRule as AccountActualGrossRule;
use Lib\Util;

class Account
{
    /**
     * @var \DateTime
     */
    protected $curDate;

    /**
     * @param string $date
     */
    public function __construct($date)
    {
        $this->curDate = new \DateTime($date);
        $this->valueRepo = new AccountTwrValueRepo();
        $this->periodRepo = new AccountTwrPeriodRepo();
        $this->transactionRepo = new TransactionRepo();
        $this->accountValueRepo = new ClientAccountValueRepo();
    }

    /**
     * @return \DateTime
     */
    protected function getCurDate()
    {
        return $this->curDate;
    }

    /**
     * @param string $period
     * @return \DateTime
     */
    protected function getModifyDate($period)
    {
        $date = clone $this->getCurDate();

        return $date->modify($period);
    }

    /**
     * @param SystemClientAccountModel $account
     */
    public function calculateValue(SystemClientAccountModel $account)
    {
        $todayDate = $this->getCurDate();
        $yesterdayDate = $this->getModifyDate('-1 day');

        // Get today account value
        $todayValue = $this->accountValueRepo->getTodayValue($account->getId(), $todayDate);

        // Get yesterday account value
        $yesterdayValue = $this->accountValueRepo->getYesterdayValue($account->getId(), $todayDate);

        // Calculate NET Cash Flow
        $yesterdayNetCashFlow = Functions::calculateCashFlow(
            $this->transactionRepo->sumContribution($account->getId(), $yesterdayDate),
            $this->transactionRepo->sumWithdrawal($account->getId(), $yesterdayDate, TransactionRepo::AMOUNT_TYPE_NET)
        );

        // Calculate today NET TWR
        $todayNetTWR = Functions::calculateTodayTwr($todayValue, $yesterdayNetCashFlow, $yesterdayValue);

        // Calculate GROSS Cash Flow
        $yesterdayGrossCashFlow = Functions::calculateCashFlow(
            $this->transactionRepo->sumContribution($account->getId(), $yesterdayDate),
            $this->transactionRepo->sumWithdrawal($account->getId(), $yesterdayDate, TransactionRepo::AMOUNT_TYPE_GROSS)
        );

        // Calculate today GROSS TWR
        $todayGrossTWR = Functions::calculateTodayTwr($todayValue, $yesterdayGrossCashFlow, $yesterdayValue);

        $model = new AccountTwrValueModel();
        $model->setDate($todayDate->format('Y-m-d'));
        $model->setNetValue($todayNetTWR);
        $model->setGrossValue($todayGrossTWR);
        $model->setAccountNumber($account->getAccountNumber());

        // Save today TWR value
        $this->valueRepo->save($model);

        Factory::get($account->getClientId())->addTodayValue($todayValue);
        Factory::get($account->getClientId())->addYesterdayValue($yesterdayValue);
        Factory::get($account->getClientId())->addYesterdayNetCashFlow($yesterdayNetCashFlow);
        Factory::get($account->getClientId())->addYesterdayGrossCashFlow($yesterdayGrossCashFlow);
    }

    /**
     * @param SystemClientAccountModel $account
     */
    public function calculatePeriod(SystemClientAccountModel $account)
    {
        $curDate   = $this->getCurDate();
        $last1Year = $this->getModifyDate('-1 year');
        $last3Year = $this->getModifyDate('-3 year');
        $actualTwr = new Actual();

        // NET
        $model = new AccountTwrPeriodModel();
        $model->setNetMtd($actualTwr->rule(new AccountActualNetRule($account->getAccountNumber(), Util::firstDayOf('month', $curDate), $curDate)));
        $model->setNetQtd($actualTwr->rule(new AccountActualNetRule($account->getAccountNumber(), Util::firstDayOf('quarter', $curDate), $curDate)));
        $model->setNetYtd($actualTwr->rule(new AccountActualNetRule($account->getAccountNumber(), Util::firstDayOf('year', $curDate), $curDate)));
        $model->setNetYr1($actualTwr->rule(new AccountActualNetRule($account->getAccountNumber(), $last1Year, $curDate)));
        $model->setNetYr3($actualTwr->rule(new AccountActualNetRule($account->getAccountNumber(), $last3Year, $curDate)));
        $model->setNetSinceInception($actualTwr->rule(new AccountActualNetRule($account->getAccountNumber(), $account->getPerformanceInceptionAsDateTime())));

        // GROSS
        $model->setGrossMtd($actualTwr->rule(new AccountActualGrossRule($account->getAccountNumber(), Util::firstDayOf('month', $curDate), $curDate)));
        $model->setGrossQtd($actualTwr->rule(new AccountActualGrossRule($account->getAccountNumber(), Util::firstDayOf('quarter', $curDate), $curDate)));
        $model->setGrossYtd($actualTwr->rule(new AccountActualGrossRule($account->getAccountNumber(), Util::firstDayOf('year', $curDate), $curDate)));
        $model->setGrossYr1($actualTwr->rule(new AccountActualGrossRule($account->getAccountNumber(), $last1Year, $curDate)));
        $model->setGrossYr3($actualTwr->rule(new AccountActualGrossRule($account->getAccountNumber(), $last3Year, $curDate)));
        $model->setGrossSinceInception($actualTwr->rule(new AccountActualGrossRule($account->getAccountNumber(), $account->getPerformanceInceptionAsDateTime())));
        $model->setAccountNumber($account->getAccountNumber());

        // Save
        $this->periodRepo->save($model);
    }

    /**
     * @param array $clients
     */
    public function process(array $clients)
    {
        foreach ($clients as $clientId => $accounts) {
            $client = new Client($clientId, $this->getCurDate());
            Factory::add($client);
            foreach ($accounts as $account) {

                // TODO: CE-282 PAS Account closing logic
                if ($account->isClosedExpired($this->getCurDate())) continue;

                $this->calculateValue($account);
                $this->calculatePeriod($account);
            }
            $client->process();
        }
    }
}