<?php

namespace Model\Pas\Repository;

use \Model\Pas\ClientAccountValue;

class ClientAccountValueRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_CLIENT_ACCOUNT_VALUE,
            'model_name' => 'Model\Pas\ClientAccountValue'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $clientId
     * @param \DateTime $date
     * @return float
     */
    public function getTodayValue($clientId, \DateTime $date)
    {
        $result = $this->fpdo
            ->from($this->table)
            ->where('system_client_account_id', $clientId)
            ->where('date = DATE(?)', $date->format('Y-m-d'))
            ->orderBy('date DESC')
            ->limit(1)
            ->fetch('total_value');
        ;

        return (float) $result;
    }

    /**
     * @param int $clientId
     * @param \DateTime $date
     * @return float
     */
    public function getYesterdayValue($clientId, \DateTime $date)
    {
        $result = $this->fpdo
            ->from($this->table)
            ->where('system_client_account_id', $clientId)
            ->where('date < DATE(?)', $date->format('Y-m-d'))
            ->orderBy('date DESC')
            ->limit(1)
            ->fetch('total_value');
        ;

        return (float) $result;
    }

    /**
     * Insert
     *
     * @param ClientAccountValue $model
     * @return int
     */
    public function insert(ClientAccountValue $model)
    {
        return $this->fpdo->insertInto($this->table, array(
            'client_portfolio_id'        => $model->getClientPortfolioId(),
            'system_client_account_id'   => $model->getSystemClientAccountId(),
            'source'                     => $model->getSource(),
            'total_value'                => $model->getTotalValue(),
            'total_in_securities'        => $model->getTotalInSecurities(),
            'total_cash_in_account'      => $model->getTotalCashInAccount(),
            'total_cash_in_money_market' => $model->getTotalCashInMoneyMarket(),
            'sas_cash'                   => $model->getSasCash(),
            'cash_buffer'                => $model->getCashBuffer(),
            'billing_cash'               => $model->getBillingCash(),
            'date'                       => $model->getDate()
        ))->execute();
    }

    /**
     * Update
     *
     * @param int $id
     * @param ClientAccountValue $model
     * @return bool
     */
    public function update($id, ClientAccountValue $model)
    {
        return $this->fpdo->update($this->table, array(
            'total_value'                => $model->getTotalValue(),
            'total_in_securities'        => $model->getTotalInSecurities(),
            'total_cash_in_account'      => $model->getTotalCashInAccount(),
            'total_cash_in_money_market' => $model->getTotalCashInMoneyMarket(),
            'sas_cash'                   => $model->getSasCash(),
            'cash_buffer'                => $model->getCashBuffer(),
            'billing_cash'               => $model->getBillingCash()
        ), $id)->execute();
    }

    /**
     * @param ClientAccountValue $model
     * @return int|bool
     */
    public function save(ClientAccountValue $model)
    {
        $result = $this->fpdo
            ->from($this->table)
            ->where('system_client_account_id', $model->getSystemClientAccountId())
            ->where('date = DATE(?)', $model->getDate())
            ->limit(1)
            ->fetch('id');
        ;

       return $result ? $this->update($result, $model) : $this->insert($model);
    }

    /**
     * @param string $date
     * @return array|null
     */
    public function getAllSumByDate($date)
    {
        $results = $this->fpdo
            ->from($this->table)
            ->select('
                client_portfolio_id,
                SUM(total_value) as total_value,
                SUM(total_in_securities) as total_in_securities,
                SUM(total_cash_in_account) as total_cash_in_accounts,
                SUM(total_cash_in_money_market) as total_cash_in_money_market,
                SUM(sas_cash) as sas_cash,
                SUM(cash_buffer) as cash_buffer,
                SUM(billing_cash) as billing_cash
            ')
            ->where('date = DATE(?)', $date)
            ->limit(1)
            ->groupBy('client_portfolio_id')
            ->fetchAll();
        ;

        return $this->bindCollection($results);
    }
}