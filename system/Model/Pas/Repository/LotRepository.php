<?php

namespace Model\Pas\Repository;

use Model\Pas\Lot;
use Wealthbot\ClientBundle\Entity\Lot as WealthbotLot;

class LotRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_LOT,
            'model_name' => 'Model\Pas\Lot'
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
            'was_closed'  => null,
            'quantity'    => null,
            'status'      => null,
            'date'        => null,
            'date_to'     => null,
            'date_from'   => null
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

        if (!is_null($parameters['was_closed'])) {
            $query->where('was_closed', $parameters['was_closed']);
        }

        if (!is_null($parameters['date_to'])) {
            $query->where('date <= DATE(?)', $parameters['date_to']);
        }

        if (!is_null($parameters['date_from'])) {
            $query->where('date >= DATE(?)', $parameters['date_from']);
        }

        return $query;
    }

    /**
     * Find all initials lots
     *
     * @param Lot $model
     * @return array|null
     */
    public function findAllInitialLots(Lot $model)
    {
        $results = $this
            ->getAllQuery(array(
                'status'      => WealthbotLot::LOT_INITIAL,
                'was_closed'  => false,
                'security_id' => $model->getSecurityId(),
                'account_id'  => $model->getClientSystemAccountId()
            ))
            ->where('date <= DATE(?)', $model->getDate())
            ->orderBy('id ASC')
            ->fetchAll();

        return $this->bindCollection($results);
    }

    /**
     * Get lots by params for clone
     *
     * @param string $date
     * @return array
     */
    public function findAllForClone($date)
    {
        $results = $this->fpdo
            ->from($this->table)
            ->where('status', WealthbotLot::LOT_INITIAL)
            ->where('was_closed', false)
            ->where('date < DATE(?)', $date)
            ->orderBy('id ASC')
            ->fetchAll()
        ;

        return $this->bindCollection($results);
    }

    /**
     * @param $id
     * @param $state
     */
    public function updateRebalancerDiff($id, $state)
    {
        return $this->fpdo->update($this->table, array(
            'was_rebalancer_diff' => $state
        ), $id)->execute();
    }

    /**
     * Update lot status
     *
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function updateStatus($id, $status)
    {
        $parameters['status'] = $status;

        $status == WealthbotLot::LOT_CLOSED && $parameters['was_closed'] = true;

        return $this->fpdo->update($this->table, $parameters, $id)->execute();
    }

    /**
     * @param array $parameters
     * @return ArrayCollection
     */
    public function findAllBy($parameters)
    {
        $results = $this->getAllQuery($parameters)
            ->orderBy('id ASC')
            ->fetchAll()
        ;

        return $this->bindCollection($results);
    }

    /**
     * @param array $parameters
     * @return array
     */
    public function findAllClosedBy(array $parameters)
    {
        $results = $this
            ->getAllQuery(array(
                'date'       => $parameters['date'],
                'status'     => WealthbotLot::LOT_CLOSED,
                'quantity'   => $parameters['quantity'],
                'was_closed' => true,
                'account_id' => $parameters['account_id']
            ))
            ->orderBy('id ASC')
            ->fetchAll()
        ;

        return $this->bindCollection($results);
    }

    /**
     * Get prev MF lot by params
     *
     * @param Lot $model
     * @return Lot|null
     */
    public function findOnePreviewMFLot(Lot $model)
    {
        $results = $this
            ->getAllQuery(array(
                'security_id' => $model->getSecurityId(),
                'account_id'  => $model->getClientSystemAccountId()
            ))
            ->where('date <= DATE(?)', $model->getDate())
            ->orderBy('id ASC')
            ->limit(1)
            ->fetchAll()
        ;

        return $this->bindCollection($results)->first();
    }

    /**
     * Insert lot
     *
     * @param Lot $model
     * @return int|null
     */
    public function insert(Lot $model)
    {
        return $this->fpdo->insertInto($this->table, array(
            'client_system_account_id' => $model->getClientSystemAccountId(),
            'security_id'         => $model->getSecurityId(),
            'initial_lot_id'      => $model->getInitialLotId(),
            'quantity'            => $model->getQuantity(),
            'amount'              => $model->getAmount(),
            'status'              => $model->getStatus(),
            'cost_basis'          => $model->getCostBasis(),
            'was_closed'          => $model->getWasClosed(),
            'realized_gain_loss'  => $model->getRealizedGain(),
            'date'                => $model->getDate(),
            'position_id'         => $model->getPositionId(),
            'is_cost_basis_known' => false,
            'is_wash_sale'        => false,
            'was_rebalancer_diff' => $model->getWasRebalancerDiff()
        ))->execute();
    }

    /**
     * Update lot
     *
     * @param int $id
     * @param Lot $model
     * @return bool
     */
    public function update($id, Lot $model)
    {
        $this->fpdo->update($this->table, array(
            'quantity'      => $model->getQuantity(),
            'amount'        => $model->getAmount(),
            'cost_basis'    => $model->getCostBasis(),
            'was_closed'     => $model->getWasClosed(),
            'realized_gain_loss' => $model->getRealizedGain(),
            'position_id'   => $model->getPositionId()
        ), $id)->execute();

        return $id;
    }

    /**
     * @param Lot $model
     * @return bool|int|null
     */
    public function save(Lot $model)
    {
        $result = $this
            ->getAllQuery(array(
                'account_id'  => $model->getClientSystemAccountId(),
                'security_id' => $model->getSecurityId(),
                'quantity'    => $model->getQuantity(),
                'date'        => $model->getDate()
            ))
            ->limit(1)
            ->fetch('id')
        ;

        return $result ? $this->update($result, $model) : $this->insert($model);
    }

    /**
     * Set position for lots
     *
     * @param Lot $model
     * @return bool
     */
    public function updatePositionBy(Lot $model)
    {
        return $this->fpdo
            ->update($this->table)
            ->set('position_id', $model->getPositionId())
            ->where('security_id', $model->getSecurityId())
            ->where('date = DATE(?)', $model->getDate())
            ->where('client_system_account_id', $model->getClientSystemAccountId())
            ->execute()
        ;
    }
}