<?php

namespace Pas\Lot;

use Model\Pas\Lot as LotModel;
use Model\Pas\RebalancerQueue as RebalancerQueueModel;
use Wealthbot\ClientBundle\Entity\Lot as WealthbotLot;

class Buy extends AbstractLot
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param LotModel $model
     * @return int
     */
    public function create(LotModel $model)
    {
        if ($model->isMF()) {
            if (null == $previewMFLot = $this->lotRepo->findOnePreviewMFLot($model)) {
                $model->setQuantity($model->getAmount());
                $model->setCostBasis($model->getAmount());

                // Save lot
                $id = $this->lotRepo->save($model);
            } else {
                $amount  = $previewMFLot->getAmount();
                $amount += $model->getAmount();

                if ($previewMFLot->getDate() == $model->getDate()) {
                    $model->setAmount($amount);
                    $model->setQuantity($amount);
                    $model->setCostBasis($amount);

                    // Save lot changes
                    $this->lotRepo->update($previewMFLot->getId(), $model);

                    // Return preview MF lot
                    $id = $previewMFLot->getId();
                } else {
                    $model->setStatus(WealthbotLot::LOT_IS_OPEN);
                    $model->setQuantity($amount);
                    $model->setCostBasis($amount);

                    // Save lot
                    $id = $this->lotRepo->save($model);
                }
            }
        } else {
            // Save lot
            $id = $this->lotRepo->save($model);
        }

        ($id && $model->isInitial()) && $this->lotRepo->updateRebalancerDiff($id, $this->compareRebalancerQueue($model, RebalancerQueueModel::STATUS_BUY));

        return $id;
    }
}