<?php

namespace Pas\Lot;

use Model\Pas\Lot as LotModel;
use Model\Pas\ArrayCollection;
use Model\Pas\RebalancerQueue as RebalancerQueueModel;
use Model\Pas\Repository\LotRepository as LotRepo;
use Wealthbot\ClientBundle\Entity\Lot as WealthbotLot;
use Lib\Util;

class Sell extends AbstractLot
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param ArrayCollection $initialLots
     * @param LotModel $model
     * @return int|null
     */
    protected function compareEqualQuantity(ArrayCollection $initialLots, LotModel $model)
    {
        foreach ($initialLots as $initialLot) {
            if (Util::floatcmp($initialLot->getQuantity(), $model->getQuantity())) {

                $model->setStatus(WealthbotLot::LOT_CLOSED);
                $model->setWasClosed(true);
                $model->setInitialLotId($initialLot->getId());
                $model->setRealizedGain($model->getAmount() - $initialLot->getAmount());

                // Close initial lot
                $this->closedLot($initialLot->getId());

                // Save lot
                return $this->lotRepo->save($model);
            }
        }

        return null;
    }

    /**
     * @param ArrayCollection $initialLots
     * @param LotModel $model
     * @return int|null
     */
    protected function compareRtQuantity(ArrayCollection $initialLots, LotModel $model)
    {
        foreach ($initialLots as $initialLot) {
            if ($initialLot->getQuantity() > $model->getQuantity()) {

                // Close lot
                $model->setStatus(WealthbotLot::LOT_CLOSED);
                $model->setWasClosed(true);
                $model->setInitialLotId($initialLot->getId());
                $model->setRealizedGain($model->getAmount() - ($model->getQuantity() * $initialLot->calcPrice()));

                //$this->lotRepo->insert($model);
                $this->lotRepo->save($model);

                // Create new lot
                $newModel = new LotModel();
                $newModel->setDate($model->getDate());
                $newModel->setStatus(WealthbotLot::LOT_INITIAL);
                $newModel->setQuantity($initialLot->getQuantity() - $model->getQuantity());
                $newModel->setAmount($newModel->getQuantity() * $initialLot->calcPrice());
                $newModel->setWasClosed(false);
                $newModel->setCostBasis($model->getCostBasis());
                $newModel->setSecurityId($model->getSecurityId());
                $newModel->setRealizedGain(null);
                $newModel->setInitialLotId($initialLot->getId());
                $newModel->setClientSystemAccountId($model->getClientSystemAccountId());
                $newModel->setWasRebalancerDiff(false);

                // Divide initial lot
                $this->dividedLot($initialLot->getId());

                // Save lot
                return $this->lotRepo->save($newModel);
            }
        }

        return null;
    }

    /**
     * @param LotModel $model
     * @return int|null
     */
    public function create(LotModel $model)
    {
        if ($model->isMF()) {
            if (null == $previewMFLot = $this->lotRepo->findOnePreviewMFLot($model)) {
                // TODO: add error
                return null;
            } else {
                $amount  = $previewMFLot->getAmount();
                $amount -= $model->getAmount();

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
            $initialLots = $this->lotRepo->findAllInitialLots($model);
            $id = $this->compareEqualQuantity($initialLots, $model);
            $id = is_null($id) ? $this->compareRtQuantity($initialLots, $model) : $id;
       }

        $model->isClosed() && $this->lotRepo->updateRebalancerDiff($id, $this->compareRebalancerQueue($model, RebalancerQueueModel::STATUS_SELL));

        return $id;
   }
}