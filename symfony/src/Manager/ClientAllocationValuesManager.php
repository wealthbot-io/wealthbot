<?php

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use App\Entity\RebalancerAction;
use App\Entity\ClientAccount;
use App\Entity\RebalancerQueue;
use App\Repository\PositionRepository;
use App\Repository\RebalancerQueueRepository;

class ClientAllocationValuesManager
{
    private $em;
    private $am;
    private $totalAmount;

    public function __construct(EntityManager $em, SystemAccountManager $am)
    {
        $this->em = $em;
        $this->am = $am;
    }

    private function dollarsToPercents($value)
    {
        if (0 === $value) {
            return $value;
        }

        return $value / $this->totalAmount * 100;
    }

    private function percentsToDollars($value)
    {
        return $value * $this->totalAmount / 100;
    }

    private function getRandomColor()
    {
        return '#'.str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }

    public function getValues($user, $isClientView = false, $accountId = null)
    {
        $tableData = [];
        $lastRow = [
            'targetPercent' => 0,
            'targetValue' => 0,
            'currentPercent' => 0,
            'currentValue' => 0,
        ];
        mt_srand(0xFFFF);
        /** @var PositionRepository $positionsRepo */
        $positionsRepo = $this->em->getRepository('App\Entity\Position');

        $clientPortfolio = $this->em->getRepository('App\Entity\ClientPortfolio')
            ->findOneBy([
                'client' => $user,
                'is_active' => 1,
            ]);

        $activeClientAccounts = $this->am->getAccountsForClient($user, $isClientView);

        //get accounts...
        $accounts = $activeClientAccounts;
        if ($accountId) {
            /** @var ClientAccount $account */
            if ($account = $this->em->getRepository('App\Entity\ClientAccount')->findOneBy(['id' => $accountId, 'client' => $user])) {
                if ($systemAccount = $account->getSystemAccount()) {
                    $accounts = [$systemAccount];
                }
            }
        }

        $positions = $positionsRepo->getOpenPositions($accounts);

        $actualData = $positionsRepo->getAllocation($user, $positions);
        $targetData = $this->em->getRepository('App\Entity\CeModelEntity')
            ->getModelSubclasses($clientPortfolio->getPortfolio());

        foreach ($actualData as &$actualDataRow) {
            $subclassId = $actualDataRow['subclass_id'];
            $actualDataRow['color'] = $this->getRandomColor();

            $tableData[$subclassId] = [
                'subclassTitle' => $actualDataRow['label'],
                'targetPercent' => 0,
                'targetValue' => 0,
                'currentPercent' => 0,
                'color' => $actualDataRow['color'],
                'currentValue' => $actualDataRow['amount'],

                'dollarVariance' => $actualDataRow['amount'],
                'percentVariance' => 0,
            ];

            $lastRow['currentValue'] += $tableData[$subclassId]['currentValue'];
        }
        $this->totalAmount = $lastRow['currentValue'];

        foreach ($actualData as &$actualDataRow) {
            $subclassId = $actualDataRow['subclass_id'];
            $tableData[$subclassId]['currentPercent']
                = $tableData[$subclassId]['percentVariance']
                = $actualDataRow['data']
                = $this->dollarsToPercents($tableData[$subclassId]['currentValue']);

            $lastRow['currentPercent'] += $tableData[$subclassId]['currentPercent'];
        }

        foreach ($targetData as &$targetDataRow) {
            $subclassId = $targetDataRow['subclass_id'];
            $targetDataRow['color'] = $this->getRandomColor();

            if (isset($tableData[$subclassId])) {
                $targetDataRow['color'] = $tableData[$subclassId]['color'];
            } else {
                $tableData[$subclassId] = [
                    'subclassTitle' => $targetDataRow['label'],
                    'currentPercent' => 0,
                    'currentValue' => 0,
                    'dollarVariance' => 0,
                    'percentVariance' => 0,
                ];
            }

            $tableData[$subclassId]['targetPercent'] = $targetDataRow['data'];
            $tableData[$subclassId]['targetValue'] = $this->percentsToDollars($targetDataRow['data']);

            $tableData[$subclassId]['dollarVariance'] = $tableData[$subclassId]['dollarVariance'] - $targetDataRow['data'] * $this->totalAmount / 100;
            $tableData[$subclassId]['percentVariance'] = $tableData[$subclassId]['percentVariance'] - $targetDataRow['data'];

            $lastRow['targetValue'] += $this->percentsToDollars($targetDataRow['data']);
            $lastRow['targetPercent'] += $targetDataRow['data'];
        }

        return [
            'clientPortfolio' => $clientPortfolio,
            'actualData' => $actualData,
            'targetData' => $targetData,
            'tableData' => $tableData,
            'lastRow' => $lastRow,
            'totalAmount' => $this->totalAmount,
        ];
    }

    public function refundValues($tableData, RebalancerAction $rebalancerAction)
    {
        /** @var RebalancerQueueRepository $rebalancerQueueRepo */
        $rebalancerQueueRepo = $this->em->getRepository('App\Entity\RebalancerQueue');
        $rebalancerQueueCollection = $rebalancerQueueRepo->findGroupedInformationByRebalancerAction($rebalancerAction, false);

        $totalAmount = 0;
        foreach ($tableData as $key => &$item) {
            $item['postRebalancerValue'] = $item['currentValue'];

            foreach ($rebalancerQueueCollection as $rebalancerQueueData) {
                /** @var RebalancerQueue $rebalancerQueue */
                $rebalancerQueue = $rebalancerQueueData[0];
                if ($rebalancerQueue->getSubclass()->getId() === $key) {
                    if ($rebalancerQueue->isBuy()) {
                        $item['postRebalancerValue'] += $rebalancerQueueData['total_amount'];
                    } else {
                        $item['postRebalancerValue'] -= $rebalancerQueueData['total_amount'];
                    }
                }
            }

            $totalAmount += $item['postRebalancerValue'];
        }

        reset($tableData);

        foreach ($tableData as &$item) {
            if (0 === $totalAmount) {
                $item['postRebalancerPercent'] = 0;
            } else {
                $item['postRebalancerPercent'] = round(($item['postRebalancerValue'] / $totalAmount) * 100, 2);
            }

            $item['differenceValue'] = $item['postRebalancerValue'] - $item['targetValue'];
            $item['differencePercent'] = $item['postRebalancerPercent'] - $item['targetPercent'];
        }

        reset($tableData);

        return $tableData;
    }
}
