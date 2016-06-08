<?php

namespace Pas\Lot;

use Model\Pas\Lot as LotModel;
use Model\Pas\RebalancerQueue as RebalancerQueueModel;
use Model\Pas\Repository\LotRepository as LotRepo;
use Model\Pas\Repository\RebalancerQueueRepository as RebalancerQueueRepo;
use Wealthbot\ClientBundle\Entity\Lot as WealthbotLot;

abstract class AbstractLot
{
    public function __construct()
    {
        $this->lotRepo = new LotRepo();
        $this->rebalancerQueueRepo = new RebalancerQueueRepo();
    }

    protected function closedLot($id)
    {
        return $this->lotRepo->updateStatus($id, WealthbotLot::LOT_CLOSED);
    }

    protected function openLot($id)
    {
        return $this->lotRepo->updateStatus($id, WealthbotLot::LOT_IS_OPEN);
    }

    protected function dividedLot($id)
    {
        return $this->lotRepo->updateStatus($id, WealthbotLot::LOT_DIVIDED);
    }

    /**
     * @param LotModel $model
     * @param int $status
     * @return bool
     */
    protected function compareRebalancerQueue(LotModel $model, $status)
    {
        $amount = $model->getAmount();
        if (!empty($amount)) {
            $parameters['status']  = $status;
            $parameters['quantity'] = $model->getQuantity();
            $parameters['security_id'] = $model->getSecurityId();
            $status == RebalancerQueueModel::STATUS_SELL && $parameters['lot_id'] = $model->getInitialLotId();

            if ($rebalancerQueue = $this->rebalancerQueueRepo->findOneBy($parameters)) {
                $percent = abs((($model->getAmount() - $rebalancerQueue->getAmount()) / $model->getAmount()) * 100);
                return $percent > 10;
            }
        }

        return false;
    }



    /**
     * @param LotModel $model
     * @return mixed
     */
    abstract public function create(LotModel $model);
}