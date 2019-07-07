<?php

namespace Model\Pas\Repository;

use Model\Pas\Position;

class PositionRepository extends BaseRepository
{
    const POSITION_STATUS_INITIAL = 1; //status when shares was bought, i.e. first position.
    const POSITION_STATUS_IS_OPEN = 2;
    const POSITION_STATUS_IS_CLOSE = 3; //status when shares was sold, i.e. last position.
    const POSITION_STATUS_IS_NOT_VERIFIED = 4;

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_POSITION,
            'model_name' => 'Model\Pas\Position'
        );
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
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
            'quantity'    => null,
            'status'      => null,
            'date'        => null
        ), $parameters);

        $query = $this->fpdo->from($this->table);

        if (!empty($parameters['account_id'])) {
            $query->where('client_system_account_id', $parameters['account_id']);
        }

        if (!empty($parameters['security_id'])) {
            $query->where('security_id', $parameters['security_id']);
        }

        if (!empty($parameters['date'])) {
            $query->where('date = DATE(?)', $parameters['date']);
        }

        if (!empty($parameters['quantity'])) {
            $query->where('quantity', $parameters['quantity']);
        }

        if (!empty($parameters['status'])) {
            $query->where('status', $parameters['status']);
        }

        return $query;
    }

    /**
     * @param Position $model
     * @return int|null
     */
    public function insert(Position $model)
    {
        return $this->fpdo->insertInto($this->table, array(
            'security_id' => $model->getSecurityId(),
            'quantity'    => $model->getQuantity(),
            'amount'      => $model->getAmount(),
            'status'      => $model->getStatus(),
            'date'        => $model->getDate(),
            'client_system_account_id' => $model->getClientSystemAccountId(),
        ))->execute();
    }

    /**
     * Update Position
     *
     * @param int $id
     * @param Position $model
     * @return bool
     */
    public function update($id, Position $model)
    {
        $this->fpdo->update($this->table, array(
            'quantity' => $model->getQuantity(),
            'amount'   => $model->getAmount(),
            'status'   => $model->getStatus(),
        ), $id)->execute();

        return $id;
    }

    /**
     * @param Position $model
     * @return bool|int|null
     */
    public function save(Position $model)
    {
        $result = $this
            ->getAllQuery(array(
                'account_id'  => $model->getClientSystemAccountId(),
                'security_id' => $model->getSecurityId(),
                'date'        => $model->getDate()
            ))
            ->limit(1)
            ->fetch('id')
        ;

        return $result ? $this->update($result, $model) : $this->insert($model);
    }

    /**
     * Get position by parameters
     *
     * @param $accountId
     * @param $securityId
     * @param $date
     * @return array|null
     */
    public function getPositionBy($accountId, $securityId, $date)
    {
        return $this->db->query("SELECT * FROM {$this->table} WHERE client_system_account_id = :account_id AND security_id = :security_id AND date = DATE(:date)", array(
            'account_id'  => $accountId,
            'security_id' => $securityId,
            'date'        => $date
        ));
    }

    /**
     * Get position id by parameters
     *
     * @param $accountId
     * @param $securityId
     * @param $date
     * @return bool
     */
    public function getPositionId($accountId, $securityId, $date)
    {
        $data = $this->getPosition($accountId, $securityId, $date);

        return isset($data[0]['id']) ? $data[0]['id'] : false;
    }

    /**
     * Change position status
     *
     * @param int $positionId
     * @param int $status
     */
    public function changeStatus($positionId, $status)
    {
        $this->db->query("UPDATE {$this->table} SET status = :status WHERE id = :id", array(
            'status' => $status,
            'id'     => $positionId
        ));
    }

    /**
     * @param $accountId
     * @param $date
     * @return int
     */
    public function getSumByAccountId($accountId, $date)
    {
        $data = $this->db->query("SELECT SUM(amount) as total FROM {$this->table} WHERE client_system_account_id = :account_id AND date = DATE(:date)", array(
            'account_id' => $accountId,
            'date'       => $date
        ));

        return isset($data[0]['total']) ? $data[0]['total'] : 0;
    }
}