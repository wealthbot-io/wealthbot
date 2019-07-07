<?php

namespace Model\Pas\Repository;

use Model\Pas\Transaction;

class TransactionRepository extends BaseRepository
{
    const AMOUNT_TYPE_NET   = 'NET';
    const AMOUNT_TYPE_GROSS = 'GROSS';

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_TRANSACTION,
            'model_name' => 'Model\Pas\Transaction'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function sumContribution($accountId, \DateTime $date)
    {
        $result = $this->db->query("
            SELECT sum(netAmount) as total FROM {$this->table}
            WHERE
                transaction_type_id in (SELECT id FROM ".static::TABLE_TRANSACTION_TYPE." WHERE name in ('DEP','CJOUR','REC'))
                AND txDate = DATE(:date)
                AND account_id = :account_id
            LIMIT 1", array('account_id' => $accountId, 'date' => $date->format('Y-m-d')));

        return $result ? $result[0]['total'] : 0;
    }

    public function sumWithdrawal($accountId, \DateTime $date, $amountType)
    {
        $names = "'WITH','DJOUR','DEL'";
        if ($amountType == static::AMOUNT_TYPE_NET) {
            $names .= ",'MFEE'";
        }

        $result = $this->db->query("
            SELECT sum(netAmount) as total FROM {$this->table}
            WHERE
                transaction_type_id in (SELECT id FROM ".static::TABLE_TRANSACTION_TYPE." WHERE name in ({$names}))
                AND txDate = DATE(:date)
                AND account_id = :account_id
            LIMIT 1", array('account_id' => $accountId, 'date' => $date->format('Y-m-d')));

        return $result ? $result[0]['total'] : 0;
    }

    /**
     * Insert new transaction
     *
     * @param Transaction $transaction
     * @return int|null
     */
    public function insert(Transaction $transaction)
    {
        return $this->fpdo->insertInto($this->table, array(
            'account_id'          => $transaction->getAccountId() ,
            'security_id'         => $transaction->getSecurityId(),
            'transaction_type_id' => $transaction->getTransactionTypeId(),
            'closing_method_id'   => $transaction->getClosingMethodId(),
            'netAmount'           => $transaction->getNetAmount(),
            'grossAmount'         => $transaction->getGrossAmount(),
            'qty'                 => $transaction->getQty(),
            'txDate'              => $transaction->getTxDate(),
            'settleDate'          => $transaction->getSettleDate(),
            'accruedInterest'     => $transaction->getAccruedInterest(),
            'notes'               => $transaction->getNotes(),
            'cancelStatus'        => $transaction->getCancelStatus(),
            'lot_id'              => $transaction->getLotId(),
            'status'              => $transaction->getStatus()
        ))->execute();
	}

    /**
     * Update transaction
     *
     * @param int $id
     * @param Transaction $transaction
     * @return bool
     */
    public function update($id, Transaction $transaction)
    {
        $this->fpdo->update($this->table, array(
            'transaction_type_id' => $transaction->getTransactionTypeId(),
            'closing_method_id'   => $transaction->getClosingMethodId(),
            'netAmount'           => $transaction->getNetAmount(),
            'grossAmount'         => $transaction->getGrossAmount(),
            'qty'                 => $transaction->getQty(),
            'settleDate'          => $transaction->getSettleDate(),
            'accruedInterest'     => $transaction->getAccruedInterest(),
            'notes'               => $transaction->getNotes(),
            'cancelStatus'        => $transaction->getCancelStatus(),
            'lot_id'              => $transaction->getLotId(),
            'status'              => $transaction->getStatus()
        ), $id)->execute();

        return $id;
    }

    /**
     * Save
     *
     * @param Transaction $model
     * @return int|bool
     */
    public function save(Transaction $model)
    {
        $result = $this
            ->getAllQuery(array(
                'date'        => $model->getTxDate(),
                'account_id'  => $model->getAccountId(),
                'security_id' => $model->getSecurityId()
            ))
            ->limit(1)
            ->fetch('id')
        ;

        return $result ? $this->update($result, $model) : $this->insert($model);
    }

    /**
     * @param array $parameters
     * @return \SelectQuery
     */
    public function getAllQuery(array $parameters)
    {
        $parameters = array_merge(array(
            'account_id'  => null,
            'security_id' => null,
            'date'        => null
        ), $parameters);

        $query = $this->fpdo->from($this->table);

        if (!empty($parameters['account_id'])) {
            $query->where('account_id', $parameters['account_id']);
        }

        if (!empty($parameters['security_id'])) {
            $query->where('security_id', $parameters['security_id']);
        }

        if (!empty($parameters['date'])) {
            $query->where('txDate = DATE(?)', $parameters['date']);
        }

        return $query;
    }

    /**
     * @param array $parameters
     * @param array $orderBy
     * @return null|array
     */
    public function findOneBy(array $parameters, $orderBy = array())
    {
        $results = $this->getAllQuery($parameters)
            ->limit(1)
            ->fetchAll();

        return $this->bindCollection($results)->first();
    }

    /**
     * Set lot id for transaction
     *
     * @param int $id
     * @param int $lotId
     * @return bool
     */
    public function setLotId($id, $lotId)
    {
        return $this->fpdo->update($this->table, array(
            'lot_id' => $lotId
        ), $id)->execute();
    }
}