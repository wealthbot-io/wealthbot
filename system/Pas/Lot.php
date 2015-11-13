<?php

namespace Pas;

use Pas\Lot\Factory;
use Model\Pas\Lot as LotModel;
use Model\Pas\Security as SecurityModel;
use Model\Pas\Position as PositionModel;
use Model\Pas\SystemClientAccount as SystemClientAccountModel;

use Model\Pas\Repository\LotRepository;
use Model\Pas\Repository\PositionRepository;
use Model\Pas\Repository\SecurityRepository;
use Model\Pas\Repository\TransactionRepository;
use Model\Pas\Repository\SystemClientAccountRepository;

use Wealthbot\ClientBundle\Entity\Lot as WealthbotLot;
use Wealthbot\ClientBundle\Entity\Position as WealthbotPosition;

class Lot extends Pas
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initRepositories();
    }

    /**
     * Init repositories
     */
    public function initRepositories()
    {
        $this->addRepository(new LotRepository());
        $this->addRepository(new PositionRepository());
        $this->addRepository(new TransactionRepository());
        $this->addRepository(new SystemClientAccountRepository());
        $this->addRepository(new SecurityRepository());
    }

    protected function getSecurity($symbol)
    {
        if (null == $security = $this->getRepository('Security')->findOneBySymbol($symbol)) {
            // TODO: add Error log
        }
        return $security;
    }

    protected function getAccount($number)
    {
        if (null == $account = $this->getRepository('SystemClientAccount')->findOneByAccountNumber($number)) {
            // TODO: add Error log
        }
        return $account;
    }

    /**
     * @param string $date
     */
    protected function generateClones($date)
    {
        $clones = $this->getRepository('Lot')->findAllForClone($date);
        foreach ($clones as $clone) {
            $model = clone $clone;
            $model->setDate($date);
            $model->setStatus(WealthbotLot::LOT_IS_OPEN);
            $model->setPositionId(null);
            $model->setInitialLotId($clone->getId());
            $this->getRepository('Lot')->save($model);
        }
    }

    /**
     * @param SystemClientAccountModel $account
     * @param SecurityModel $security
     * @param array $data
     * @return mixed
     */
    public function process(SystemClientAccountModel $account, SecurityModel $security, array $data)
    {
        $model = new LotModel();
        $model->setSymbol($data['symbol']);
        $model->setTransactionCode($data['transaction_code']);
        $model->setInitialLotId(null);
        $model->setQuantity($data['qty']);
        $model->setStatus(WealthbotLot::LOT_INITIAL);
        $model->setDate($data['tx_date']);
        $model->setWasClosed(false);
        $model->setAmount($data['net_amount']);
        $model->setCostBasis($data['gross_amount']);
        $model->setRealizedGain(null);
        $model->setSecurityId($security->getId());
        $model->setClientSystemAccountId($account->getId());
        $model->setWasRebalancerDiff(false);

        $class = Factory::make($model->isBuy() ? 'Buy' : 'Sell');

        // Create new lot
        return $class->create($model);
    }

    /**
     * @param array $collection
     */
    public function run(array $collection)
    {
        foreach ($collection as $key => $data) {
            foreach ($data as $row) {
                if (null == $account = $this->getAccount($row['account_number'])) continue;
                if (null == $security = $this->getSecurity($row['symbol'])) continue;
                $id = $this->process($account, $security, $row);

                // Set lot id for transaction
                $this->getRepository('Transaction')->setLotId($row['transaction_id'], $id);
            }

            $this->generateClones($key);
            $this->generatePosition($key);
        }
    }

    /**
     * @param string $date
     * @return array
     */
    public function getLotHash($date)
    {
        $lots = $this->getRepository('Lot')->findAllBy(array('date' => $date));

        $hash = [];
        foreach($lots as $lot) {
            $accountId  = $lot->getClientSystemAccountId();
            $securityId = $lot->getSecurityId();

            if ( ! array_key_exists($accountId, $hash)) $hash[$accountId] = [];
            if ( ! array_key_exists($securityId, $hash[$accountId])) $hash[$accountId][$securityId] = [];

            $hash[$accountId][$securityId][] = $lot;
        }

        return $hash;
    }

    /**
     * @param int $positionId
     * @param array $lots
     */
    public function setPositionIdForLots($positionId, $lots)
    {
        // Set position is for lot
        foreach ($lots as $lot) {
            $lot->setPositionId($positionId);
            $this->getRepository('Lot')->update($lot->getId(), $lot);
        }
    }

    /**
     * @param array $lots
     * @return int|null
     */
    public function createPosition($lots)
    {
        if ( ! count($lots)) return null;

        $amount = $quantity = 0;
        $status = $lots[0]->getStatus();

        foreach ($lots as $lot) {
            if ($lot->getStatus() !== $status) $status = WealthbotLot::LOT_IS_OPEN;

            if ($lot->isMF()) {
                $amount   = $lot->getAmount();
                $quantity = $lot->getQuantity();
            } else {
                if ($lot->isOpen()) {
                    $amount   += $lot->getAmount();
                    $quantity += $lot->getQuantity();
                }

                if ($lot->isInitial()) {
                    $amount   += $lot->getAmount();
                    $quantity += $lot->getQuantity();
                }
            }
        }

        $position = new PositionModel();
        $position->setAmount($amount);
        $position->setQuantity($quantity);

        if ($status == WealthbotLot::LOT_IS_OPEN) $position->setStatus(WealthbotPosition::POSITION_STATUS_IS_OPEN);
        if ($status == WealthbotLot::LOT_INITIAL) $position->setStatus(WealthbotPosition::POSITION_STATUS_INITIAL);
        if ($status == WealthbotLot::LOT_CLOSED)  $position->setStatus(WealthbotPosition::POSITION_STATUS_IS_CLOSE);

        $position->setDate($lots[0]->getDate());
        $position->setClientSystemAccountId($lots[0]->getClientSystemAccountId());
        $position->setSecurityId($lots[0]->getSecurityId());

        return $position;
    }

    /**
     * Generate new position

     * @param $date
     * @return int|null
     */
    public function generatePosition($date)
    {
        $hash = $this->getLotHash($date);
        foreach($hash as $securities) {
            foreach($securities as $lots) {
                $position = $this->createPosition($lots);
                if ($position instanceof PositionModel) {
                    if ( ! $id = $this->getRepository('Position')->save($position)) {
                        // TODO: add error log
                    } else {
                        $this->setPositionIdForLots($id, $lots);
                    }
                }
            }
        }
    }
}