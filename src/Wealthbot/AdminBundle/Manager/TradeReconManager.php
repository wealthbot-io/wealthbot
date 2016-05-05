<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 01.05.14
 * Time: 10:44.
 */

namespace Wealthbot\AdminBundle\Manager;

use Doctrine\ORM\EntityManager;
use Wealthbot\ClientBundle\Entity\Lot;
use Wealthbot\UserBundle\Entity\User;

class TradeReconManager
{
    /** @var EntityManager $em */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function getValues(\DateTime $dateFrom, \DateTime $dateTo, User $ria = null, $clientName = '')
    {
        $tableData = [];
        $dateFrom->setTime(0, 0, 0);
        $dateTo->setTime(23, 59, 59);

        /* @var \Wealthbot\ClientBundle\Repository\LotRepository $lotRepo */
        $lotRepo = $this->em->getRepository('WealthbotClientBundle:Lot');

        /* @var \Wealthbot\ClientBundle\Repository\RebalancerQueueRepository $rebalancerQueueRepo */
        $rebalancerQueueRepo = $this->em->getRepository('WealthbotClientBundle:RebalancerQueue');

        $rebalancerQueue = $rebalancerQueueRepo->getTradeRecon($dateFrom, $dateTo, $ria, $clientName);
        $ids = [];
        foreach ($rebalancerQueue as $queueItem) {
            /* @var \Wealthbot\ClientBundle\Entity\RebalancerQueue $queueItem */
            $lot = $queueItem->getLot();
            if ($lot) {
                $ids[] = $lot->getId();
            }
        }
        $transactions = $lotRepo->getTradeRecon($dateFrom, $dateTo, $ria, $ids, $clientName);

        /* @var \Wealthbot\ClientBundle\Entity\RebalancerQueue $queueItem */
        foreach ($rebalancerQueue as $queueItem) {
            $systemAccount = $queueItem->getSystemClientAccount();
            $client = $systemAccount->getClient();
            $ria = $client->getRiaCompanyInformation();
            $lot = $queueItem->getLot();

            $tableData[] = [
                'ria' => $ria->getName(),
                'custodian' => $ria->getCustodian()->getName(),
                'last_name' => $client->getLastName(),
                'first_name' => $client->getFirstName(),
                'acct_number' => $systemAccount->getAccountNumber(),
                'symbol' => $queueItem->getSecurity()->getName(),
                'submitted_action' => $queueItem->getStatus(),
                'executed_action' => $lot ? (Lot::LOT_CLOSED === $lot->getStatus() ? 'Sell' : 'Buy') : '',
                'submitted_amount' => $queueItem->getAmount(),
                'executed_amount' => $lot ? $lot->getAmount() : '',
                'error' => $queueItem->getAmount() === 0 || empty($lot) || $lot->getWasRebalancerDiff(),
            ];
        }

        /* @var \Wealthbot\ClientBundle\Entity\Lot $transaction */
        foreach ($transactions as $transaction) {
            $systemAccount = $transaction->getClientSystemAccount();
            $client = $systemAccount->getClient();
            $ria = $client->getRiaCompanyInformation();

            $tableData[] = [
                'ria' => $ria->getName(),
                'custodian' => $ria->getCustodian()->getName(),
                'last_name' => $client->getLastName(),
                'first_name' => $client->getFirstName(),
                'acct_number' => $systemAccount->getAccountNumber(),
                'symbol' => $transaction->getSecurity()->getName(),
                'submitted_action' => '',
                'executed_action' => Lot::LOT_CLOSED === $transaction->getStatus() ? 'Sell' : 'Buy',
                'submitted_amount' => '',
                'executed_amount' => $transaction->getAmount(),
                'error' => true,
            ];
        }

        return $tableData;
    }
}
