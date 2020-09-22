<?php

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use App\Entity\RebalancerAction;
use App\Entity\SecurityTransaction;
use App\Repository\SecurityTransactionRepository;
use App\Entity\RebalancerQueue;
use App\Model\TradeData;
use App\Repository\RebalancerQueueRepository;
use App\Entity\RiaCompanyInformation;

class RebalancerQueueManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var RebalancerQueueRepository
     */
    protected $repository;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('App\Entity\RebalancerQueue');
    }

    /**
     * @param RebalancerAction $rebalancerAction
     *
     * @return array
     */
    public function prepareSummary(RebalancerAction $rebalancerAction)
    {
        /** @var SecurityTransactionRepository $securityTransactionRepo */
        $securityTransactionRepo = $this->em->getRepository('App\Entity\SecurityTransaction');

        $rebalancerQueue = $this->repository->findByRebalancerAction($rebalancerAction, false);

        $client = $rebalancerAction->getClientPortfolioValue()->getClientPortfolio()->getClient();

        $result = [
            'rebalance_total' => 0,
            'short_term_gains' => 0,
            'long_term_gains' => 0,
            'short_term_losses' => 0,
            'long_term_losses' => 0,
            'transactions_costs' => 0,
            'tlh_savings' => 0,
        ];

        /** @var RebalancerQueue $item */
        foreach ($rebalancerQueue as $item) {
            $result['rebalance_total'] += $item->getAmount();

            if ($item->getLot()) {
                if ($item->getLot()->isShortTerm()) {
                    $prefix = 'short';
                } else {
                    $prefix = 'long';
                }

                if ($item->getLot()->getShortTermGain() > 0) {
                    $suffix = 'gains';
                } else {
                    $suffix = 'losses';
                }

                $result[$prefix.'_term_'.$suffix] += abs($item->getLot()->getRealizedGain());
            }

            /** @var SecurityTransaction $securityTransaction */
            $securityTransaction = $securityTransactionRepo->findOneByRebalancerQueue($item);

            if ($securityTransaction) {
                $result['transactions_costs'] += $securityTransaction->getTransactionFee();
            }
        }

//        $result['tlh_savings'] = ($result['short_term_losses'] * $client->getProfile()->getEstimatedIncomeTax()) + ($result['long_term_losses'] * 0.15 - $result['transactions_costs']) / beginning balance
        return $result;
    }

    /**
     * Get Trade Data for generate file.
     *
     * @param RiaCompanyInformation $riaCompanyInformation
     * @param array                 $clientValuesIds
     *
     * @return TradeData[]
     */
    public function getTradeDataCollection(RiaCompanyInformation $riaCompanyInformation, array $clientValuesIds)
    {
        if ($riaCompanyInformation->isHouseholdManagedLevel()) {
            $tradeDataArray = $this->repository->findTradeDataArrayForClientPortfolioValuesIds($clientValuesIds);
        } else {
            $tradeDataArray = $this->repository->findTradeDataArrayForClientAccountValuesIds($clientValuesIds);
        }

        $tradeDataCollection = [];
        foreach ($tradeDataArray as $data) {
            $tradeData = new TradeData();
            $tradeData->loadFromArray($data);

            if (RebalancerQueue::STATUS_SELL === $tradeData->getAction()) {
                $vsps = $this->repository->findVSPForTradeData($tradeData);

                $tradeData->setVsps($vsps);
            }

            $tradeDataCollection[] = $tradeData;
        }

        return $tradeDataCollection;
    }
}
