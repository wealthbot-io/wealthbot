<?php

namespace Model\Pas\Repository;

use Lib\Util;
use Model\Pas\BillItem;

class BillItemRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_BILL_ITEM,
            'model_name' => 'Model\Pas\BillItem'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }

    /**
     * @param int $accountId
     * @param \DateTime $date
     * @return array
     */
    public function findOneByAccountAndPeriod($accountId, \DateTime $date)
    {
        $period = Util::getPreviousQuarter($date);

        $query = $this
            ->fpdo
            ->from($this->table)
            ->leftJoin('bill ON bill.id = bill_item.bill_id')
            ->select('bill.year')
            ->where('bill_item.system_account_id', $accountId)
            ->where('bill.year', $period['year'])
            ->where('bill.quarter', $period['quarter'])
        ;

        $result = $query->limit(1)->fetchAll();

        return $this->bindCollection($result)->first();
    }

    /**
     * Update
     *
     * @param int $id
     * @param BillItem $model
     * @return bool
     */
    public function update($id, BillItem $model)
    {
        return $this->fpdo->update($this->table, array(
            'status' => $model->getStatus(),
            'feeCollected' => $model->getFeeCollected()
        ), $id)->execute();
    }
}