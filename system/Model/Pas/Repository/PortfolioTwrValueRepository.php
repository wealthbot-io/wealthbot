<?php

namespace Model\Pas\Repository;

use Model\Pas\PortfolioTwrValue;

class PortfolioTwrValueRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_PORTFOLIO_TWR_VALUE,
            'model_name' => 'Model\Pas\PortfolioTwrValue'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $clientId
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return array
     */
    public function findAllByPeriod($clientId, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $query = $this->fpdo
            ->from($this->table)
            ->where('client_id', $clientId)
        ;

        if (!is_null($dateTo)) {
            $query->where('date <= DATE(?)', $dateTo->format('Y-m-d'));
        }

        if (!is_null($dateFrom)) {
            $query->where('date >= DATE(?)', $dateFrom->format('Y-m-d'));
        }

        $results = $query->fetchAll();

        return $this->bindCollection($results);
    }

    /**
     * @param PortfolioTwrValue $model
     * @return int
     */
    public function insert(PortfolioTwrValue $model)
    {
        return $this->fpdo->insertInto($this->table, array(
            'date'        => $model->getDate(),
            'net_value'   => $model->getNetValue(),
            'gross_value' => $model->getGrossValue(),
            'client_id'   => $model->getClientId()
        ))->execute();
    }

    /**
     * @param int $id
     * @param PortfolioTwrValue $model
     * @return bool
     */
    public function update($id, PortfolioTwrValue $model)
    {
        return $this->fpdo->update($this->table, array(
            'net_value'   => $model->getNetValue(),
            'gross_value' => $model->getGrossValue(),
        ), $id)->execute();
    }

    /**
     * @param PortfolioTwrValue $model
     * @return mixed
     */
    public function save(PortfolioTwrValue $model)
    {
        $result = $this->fpdo
            ->from($this->table)
            ->where('date = DATE(?)', $model->getDate())
            ->where('client_id', $model->getClientId())
            ->limit(1)
            ->fetch('id')
        ;

        return $result ? $this->update($result, $model) : $this->insert($model);
    }
}