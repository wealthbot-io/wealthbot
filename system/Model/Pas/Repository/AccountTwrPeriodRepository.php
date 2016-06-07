<?php

namespace Model\Pas\Repository;

use Model\Pas\AccountTwrPeriod;

class AccountTwrPeriodRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_ACCOUNT_TWR_PERIOD,
            'model_name' => 'Model\Pas\AccountTwrPeriod'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Insert
     *
     * @param AccountTwrPeriod $model
     * @return int
     */
    public function insert(AccountTwrPeriod $model)
    {
        return $this->fpdo->insertInto($this->table, array(
            'net_mtd'   => $model->getNetMtd(),
            'net_qtd'   => $model->getNetQtd(),
            'net_ytd'   => $model->getNetYtd(),
            'net_yr1'   => $model->getNetYr1(),
            'net_yr3'   => $model->getNetYr3(),
            'gross_mtd' => $model->getGrossMtd(),
            'gross_qtd' => $model->getGrossQtd(),
            'gross_ytd' => $model->getGrossYtd(),
            'gross_yr1' => $model->getGrossYr1(),
            'gross_yr3' => $model->getGrossYr3(),
            'net_since_inception'   => $model->getNetSinceInception(),
            'gross_since_inception' => $model->getGrossSinceInception(),
            'account_number' => $model->getAccountNumber()
        ))->execute();
    }

    /**
     * @param int $id
     * @param AccountTwrPeriod $model
     * @return bool
     */
    public function update($id, AccountTwrPeriod $model)
    {
        return $this->fpdo->update($this->table, array(
            'net_mtd'   => $model->getNetMtd(),
            'net_qtd'   => $model->getNetQtd(),
            'net_ytd'   => $model->getNetYtd(),
            'net_yr1'   => $model->getNetYr1(),
            'net_yr3'   => $model->getNetYr3(),
            'gross_mtd' => $model->getGrossMtd(),
            'gross_qtd' => $model->getGrossQtd(),
            'gross_ytd' => $model->getGrossYtd(),
            'gross_yr1' => $model->getGrossYr1(),
            'gross_yr3' => $model->getGrossYr3(),
            'net_since_inception'   => $model->getNetSinceInception(),
            'gross_since_inception' => $model->getGrossSinceInception(),
        ), $id)->execute();
    }

    /**
     * @param AccountTwrPeriod $model
     * @return mixed
     */
    public function save(AccountTwrPeriod $model)
    {
        $result = $this->fpdo
            ->from($this->table)
            ->where('account_number', $model->getAccountNumber())
            ->limit(1)
            ->fetch('id')
        ;

        return $result ? $this->update($result, $model) : $this->insert($model);
    }
}