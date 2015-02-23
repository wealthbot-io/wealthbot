<?php

namespace Model\Pas\Repository;

class GainLossRepository extends BaseRepository
{
    public function __construct()
    {
        $this->table = static::TABLE_GAIN_LOSS;
        parent::__construct();
    }

    /**
     *
     */
    public function insert(array $parameters)
    {
        $sql = "INSERT INTO {$this->table} (
                    client_system_account_id,
                    transaction_id,
                    security_id,
                    qty,
                    cost_basis,
                    value,
                    is_cost_basis_known,
                    is_wash_sale,
                    date)
                VALUES (
                    :client_system_account_id,
                    :transaction_id,
                    :security_id,
                    :qty,
                    :cost_basis,
                    :value,
                    :is_cost_basis_known,
                    :is_wash_sale,
                    DATE(:date))"
        ;

        $this->db->query($sql, array(
            'client_system_account_id'  => $parameters['accountId'],
            'transaction_id'            => $parameters['transactionId'],
            'security_id'               => $parameters['securityId'],
            'qty'                       => (float) $parameters['qty'],
            'cost_basis'                => (float) $parameters['costBasis'],
            'value'                     => (float) $parameters['value'],
            'is_cost_basis_known'       => $parameters['isCostBasisKnown'],
            'is_wash_sale'              => $parameters['isWashSale'],
            'date'                      => $parameters['date']
        ));
    }

    public function update($id, $parameters)
    {
        $sql = "UPDATE {$this->table}
                SET value = :value, qty = :qty
                WHERE id = :id"
        ;

        $this->db->query($sql, array(
            'id'    => $id,
            'value' => $parameters['value']
        ));
    }

    public function save($parameters)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE transaction_id = :transaction_id
                LIMIT 1"
        ;

        $data = $this->db->query($sql, array('transaction_id' => $parameters['transactionId']));

        $data ? $this->update($data[0]['id'], $parameters) : $this->insert($parameters);
    }
}