<?php

namespace Model\Pas\Repository;

use Model\Pas\AccountTwrValue;

class AccountTwrValueRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_ACCOUNT_TWR_VALUE,
            'model_name' => 'Model\Pas\AccountTwrValue'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $accountNumber
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return array
     */
    public function findAllByPeriod($accountNumber, \DateTime $dateFrom = null, \DateTime $dateTo = null)
    {
        $query = $this->fpdo
            ->from($this->table)
            ->where('account_number', $accountNumber)
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
     * @param AccountTwrValue $model
     * @return int
     */
    public function insert(AccountTwrValue $model)
    {
        return $this->fpdo->insertInto($this->table, array(
            'date'           => $model->getDate(),
            'net_value'      => $model->getNetValue(),
            'gross_value'    => $model->getGrossValue(),
            'account_number' => $model->getAccountNumber()
        ))->execute();
    }

    /**
     * @param int $id
     * @param AccountTwrValue $model
     * @return bool
     */
    public function update($id, AccountTwrValue $model)
    {
        return $this->fpdo->update($this->table, array(
            'net_value'   => $model->getNetValue(),
            'gross_value' => $model->getGrossValue(),
        ), $id)->execute();
    }

    /**
     * @param AccountTwrValue $model
     * @return mixed
     */
    public function save(AccountTwrValue $model)
    {
        $result = $this->fpdo
            ->from($this->table)
            ->where('date = DATE(?)', $model->getDate())
            ->where('account_number', $model->getAccountNumber())
            ->limit(1)
            ->fetch('id')
        ;

        return $result ? $this->update($result, $model) : $this->insert($model);
    }
}