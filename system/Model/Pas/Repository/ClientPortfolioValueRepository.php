<?php

namespace Model\Pas\Repository;

use Model\Pas\ClientPortfolioValue;

class ClientPortfolioValueRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_CLIENT_PORTFOLIO_VALUE,
            'model_name' => 'Model\Pas\ClientPortfolioValue'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $clientId
     * @param \DateTime $date
     * @return array
     */
    public function getPortfolioValueForClient($clientId, \DateTime $date)
    {
        $sql = "SELECT cpv.* FROM {$this->table} cpv
                LEFT JOIN ".static::TABLE_CLIENT_PORTFOLIO." cp ON cp.id = cpv.client_portfolio_id
                WHERE cp.client_id = :client_id AND DATE(cpv.date) = DATE(:date)
                ORDER BY cpv.date DESC
                LIMIT 1";

        $pdo = $this->db->getPdo();

        $statement = $pdo->prepare($sql);
        $statement->execute(array('client_id' => $clientId, 'date' => $date->format('Y-m-d')));

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        return $data ? $data : null;
    }

    /**
     * @param int $clientId
     * @param int $portfolioValueId
     * @return int
     */
    public function getPrevPortfolioValueForClient($clientId, $portfolioValueId)
    {
        $sql = "SELECT cpv.* FROM {$this->table} cpv
                LEFT JOIN ".static::TABLE_CLIENT_PORTFOLIO." cp ON cp.id = cpv.client_portfolio_id
                WHERE cp.client_id = :client_id AND cpv.id < :portfolio_value_id
                ORDER BY cpv.id DESC
                LIMIT 1";

        $pdo = $this->db->getPdo();

        $statement = $pdo->prepare($sql);
        $statement->execute(array('client_id' => $clientId, 'portfolio_value_id' => $portfolioValueId));

        $data = $statement->fetch(\PDO::FETCH_ASSOC);

        return $data ? (float) $data['total_value'] : 0;
    }

    /**
     * Insert
     *
     * @param ClientPortfolioValue $model
     * @return int|null
     */
    public function insert(ClientPortfolioValue $model)
    {
        return $this->fpdo->insertInto($this->table, array(
            'client_portfolio_id'        => $model->getClientPortfolioId(),
            'total_value'                => $model->getTotalValue(),
            'total_in_securities'        => $model->getTotalInSecurities(),
            'total_cash_in_accounts'     => $model->getTotalCashInAccounts(),
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
     * @param ClientPortfolioValue $model
     * @return bool
     */
    public function update($id, ClientPortfolioValue $model)
    {
        return $this->fpdo->update($this->table, array(
            'total_value'                => $model->getTotalValue(),
            'total_in_securities'        => $model->getTotalInSecurities(),
            'total_cash_in_accounts'     => $model->getTotalCashInAccounts(),
            'total_cash_in_money_market' => $model->getTotalCashInMoneyMarket(),
            'sas_cash'                   => $model->getSasCash(),
            'cash_buffer'                => $model->getCashBuffer(),
            'billing_cash'               => $model->getBillingCash()
        ), $id)->execute();
    }

    /**
     * Save
     *
     * @param ClientPortfolioValue $model
     * @return int|bool
     */
    public function save(ClientPortfolioValue $model)
    {
        $result = $this->fpdo
            ->from($this->table)
            ->where('client_portfolio_id', $model->getClientPortfolioId())
            ->where('date = DATE(?)', $model->getDate())
            ->limit(1)
            ->fetch('id');
        ;

        return $result ? $this->update($result, $model) : $this->insert($model);
    }
}