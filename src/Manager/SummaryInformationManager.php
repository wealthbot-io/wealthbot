<?php

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use App\Entity\Bill;
use App\Entity\BillItem;
use App\Entity\ClientAccount;
use App\Entity\ClientAccountValue;
use App\Entity\ClientPortfolioValue;
use App\Entity\SystemAccount;
use App\Manager\CashCalculationManager;
use App\Entity\User;

class SummaryInformationManager
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var \App\Manager\CashCalculationManager */
    private $cashManager;

    /** @param \App\Service\Manager\PeriodManager */
    private $periodManager;

    /** @var \Symfony\Bundle\FrameworkBundle\Translation\Translator */
    private $translator;

    public function __construct(EntityManager $em, CashCalculationManager $cashManager, PeriodManager $periodManager, Translator $translator)
    {
        $this->em = $em;
        $this->cashManager = $cashManager;
        $this->periodManager = $periodManager;
        $this->translator = $translator;
    }

    public function getClientsInformation($clients, $year, $quarter)
    {
        $result = [];
        /** @var User[] $clients */
        foreach ($clients as $client) {
            $result[] = $this->getClientInformation($client, $year, $quarter);
        }

        return $result;
    }

    public function getClientInformation(User $client, $year, $quarter)
    {
        $period = $this->periodManager->getPeriod($year, $quarter);
        $data = [
            'id' => $client->getId(),
            'createdAt' => $client->getCreated(),
            'cash' => $this->cashManager->getCashOnDate($client, $period['endDate']),
            'clientStatus' => $this->getClientStatus($client),
            'billCreatedAt' => $this->getClientBillDate($client, $year, $quarter),
            'billingSpecName' => $client->getFeeShedule(),
            'name' => $client->getName(),
            'paymentMethod' => $client->getPaymentMethod(),
            'portfolioValue' => $this->getPortfolioValue($client, $period['endDate']),
            'status' => $this->getClientBillStatusText($client, $year, $quarter),
            'statusNumber' => $this->getClientBillStatus($client, $year, $quarter),
        ];

        return $data;
    }

    public function getPortfolioValue(User $client, \DateTime $dateTo)
    {
        //1. try to get from history

        /** @var ClientPortfolioValue $lastValues */
        if ($lastValues = $this->em->getRepository('App\Entity\ClientPortfolioValue')->getLastBeforeDate($client, $dateTo)) {
            $sum = $lastValues->getTotalValue();
        } else {
            //2. else get first value
            $sum = $this->em->getRepository('App\Entity\ClientAccount')->getAccountsSum($client);
        }

        return $sum;
    }

    public function getClientStatus($client)
    {
        $notClosedAccounts = $this->em->getRepository('App\Entity\SystemAccount')->findNotClosed($client);
        if (count($notClosedAccounts)) {
            return ucwords(SystemAccount::STATUS_ACTIVE);
        }

        return ucwords(SystemAccount::STATUS_CLOSED);
    }

    public function getClientBillApproveDate($client, $year, $quarter = 0)
    {
        /** @var Bill $bill */
        $bill = $this->em->getRepository('App\Entity\Bill')->findByClientAndPeriod($client, $year, $quarter);
        if ($bill) {
            return $bill->getApprovedAt();
        }

        return '';
    }

    public function getClientBillDate($client, $year, $quarter = 0)
    {
        /** @var Bill $bill */
        $bill = $this->em->getRepository('App\Entity\Bill')->findByClientAndPeriod($client, $year, $quarter);
        if ($bill) {
            return $bill->getCreatedAt()->format('m/d/Y');
        }

        return '';
    }

    public function getClientBillStatusText(User $client, $year, $quarter)
    {
        $status = $this->getClientBillStatus($client, $year, $quarter);
        if ($status < 0) {
            return $this->translator->transChoice(
                'bill.accounts_require_attention',
                -$status,
                ['%count%' => -$status]
            );
        }

        return $this->translator->trans('bill.status'.$status);
    }

    /**
     * Returns status:
     * status > 0 - BillItem status,
     * status < 0 - count of Accounts Required Attention.
     *
     * @param User $client
     * @param $year
     * @param $quarter
     *
     * @return int|mixed
     */
    public function getClientBillStatus(User $client, $year, $quarter)
    {
        $bill = $this->em->getRepository('App\Entity\Bill')->findByClientAndPeriod($client, $year, $quarter);
        if (!$bill) {
            return BillItem::STATUS_BILL_NOT_GENERATED;
        }

        $countAttention = 0;
        $accounts = $client->getClientAccounts();
        $statuses = [];
        $maxStatus = 0;

        //Check Last status of BillItems
        foreach ($accounts as $account) {
            $status = $this->getBillItemStatus($account, $year, $quarter);

            if (BillItem::STATUS_WILL_NOT_BILL === $status) {
                continue;
            }

            $statuses[] = $status;
            $maxStatus = max($maxStatus, $status);
        }

        //If there are WILL NOT BILL accounts:
        if (0 === $maxStatus) {
            $maxStatus = BillItem::STATUS_WILL_NOT_BILL;
        }

        foreach ($statuses as $status) {
            if ($status !== $maxStatus) {
                ++$countAttention;
            }
        }

        //One status for all BillItems
        if (0 === $countAttention) {
            return $maxStatus;
        }

        return -$countAttention;
    }

    public function getAccountsInformationByClients($clients, $year, $quarter)
    {
        $result = [];
        /** @var User[] $clients */
        foreach ($clients as $client) {
            $accounts = $this->em->getRepository('App\Entity\ClientAccount')->findBy(
                ['client' => $client]
            );
            $result[$client->getId()] = $this->getAccountsInformation($accounts, $year, $quarter);
        }

        return $result;
    }

    public function getAccountsInformationByClient($client, $year, $quarter)
    {
        $accounts = $this->em->getRepository('App\Entity\ClientAccount')->findByClient($client);

        return  $this->getAccountsInformation($accounts, $year, $quarter);
    }

    public function getAccountsInformation($accounts, $year, $quarter)
    {
        $result = [];
        /** @var ClientAccount[] $accounts */
        foreach ($accounts as $account) {
            $result[] = $this->getAccountInformation($account, $year, $quarter);
        }

        return $result;
    }

    public function getAccountInformation(ClientAccount $account, $year, $quarter)
    {
        $period = $this->periodManager->getPeriod($year, $quarter);

        $billItem = $this
            ->em
            ->getRepository('App\Entity\BillItem')
            ->getByAccountAndPeriod($account, $year, $quarter)
        ;

        $data = [
            'id' => $account->getId(),
            'name' => $account->getOwnerNames(),
            'type' => $account->getSystemType(),
            'number' => $this->getAccountNumber($account),
            'status' => $this->getAccountStatus($account),
            'paysFor' => $this->getAccountPaysFor($account),
            'averageAccountValue' => $this->getAccountAverageValue($account, $period['startDate'], $period['endDate']),
            'daysInPortfolio' => $this->getAccountDaysInPortfolio($account, $period['startDate'], $period['endDate']),
            'accountValue' => $this->cashManager->getAccountValueOnDate($account, $period['endDate']),
            'billItemStatus' => $this->getBillItemStatus($account, $year, $quarter),
            'cash' => $this->cashManager->getAccountCashOnDate($account, $period['endDate']),
            'billItemId' => 0,
            'feeBilled' => 0,
            'feeCollected' => 0,
        ];

        if ($billItem) {
            $data['billItemId'] = $billItem->getId();
            $data['feeBilled'] = $billItem->getFeeBilled();
            $data['feeCollected'] = $billItem->getFeeCollected();
        }

        return $data;
    }

    public function getAccountNumber(ClientAccount $account)
    {
        $systemAccount = $account->getSystemAccount();
        if ($systemAccount) {
            return $systemAccount->getAccountNumber();
        }

        return '';
    }

    public function getAccountStatus(ClientAccount $account)
    {
        $systemAccount = $account->getSystemAccount();
        if ($systemAccount) {
            return $systemAccount->getStatus();
        }

        return '';
    }

    public function getAccountPaysFor(ClientAccount $account)
    {
        $consolidator = $account->getConsolidator();

        return $consolidator ? $this->getAccountNumber($consolidator) : null;
    }

    public function getAccountAverageValue(ClientAccount $account, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $daysCount = (int) floor(($dateTo->getTimestamp() - $dateFrom->getTimestamp()) / 86400);

        /* @var ClientAccountValue[] $clientAccountValues */
        $total = $this->em->getRepository(
            'App\Entity\ClientAccountValue'
        )->getAverageAccountValues($account, $dateFrom, $dateTo);

        if ($total['count_values'] > 0) {
            return $total['avg_value'] * $total['count_values'] / $daysCount;
        }

        return 0;
    }

    public function getAccountDaysInPortfolio(ClientAccount $account, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $total = $this->em->getRepository(
            'App\Entity\ClientAccountValue'
        )->getAverageAccountValues($account, $dateFrom, $dateTo);

        return $total['count_values'];
    }

    public function getBillItemStatus(ClientAccount $account, $year, $quarter)
    {
        $period = $this->periodManager->getPeriod($year, $quarter);

        $systemAccount = $account->getSystemAccount();
        if (!$systemAccount) {
            return BillItem::STATUS_BILL_IS_NOT_APPLICABLE;
        }
        $activityCheck = $this->em->getRepository('App\Entity\ClientAccountValue')->getFirstActivityDate($account);
        if (!$activityCheck || $activityCheck->getDate()->getTimestamp() >= $period['endDate']->getTimestamp()) {
            return BillItem::STATUS_BILL_IS_NOT_APPLICABLE;
        }

        /** @var BillItem $billItem */
        $billItem = $this->em->getRepository('App\Entity\BillItem')->getByAccountAndPeriod($account, $year, $quarter);
        if ($billItem) {
            return $billItem->getStatus();
        }

        return BillItem::STATUS_BILL_NOT_GENERATED;
    }

    public function getAccountFeeBilled(ClientAccount $account, $year, $quarter)
    {
        /** @var BillItem $billItem */
        $billItem = $this->em->getRepository('App\Entity\BillItem')->getByAccountAndPeriod($account, $year, $quarter);
        if ($billItem) {
            return $billItem->getFeeBilled();
        }

        return 0;
    }

    public function getAccountFeeCollected(ClientAccount $account, $year, $quarter)
    {
        /** @var BillItem $billItem */
        $billItem = $this->em->getRepository('App\Entity\BillItem')->getByAccountAndPeriod($account, $year, $quarter);
        if ($billItem) {
            return $billItem->getFeeCollected();
        }

        return 0;
    }

    public function getAccountBillItem(ClientAccount $account, $year, $quarter)
    {
    }

    public function getClientBillValues(User $client, $year, $quarter)
    {
        $sumGenerated = 0;
        $sumApproved = 0;
        $sumFeeGenerated = 0;
        $sumCollected = 0;
        $bill = $this->em->getRepository('App\Entity\Bill')->findByClientAndPeriod($client, $year, $quarter);
        if ($bill) {
            foreach ($bill->getBillItems() as $billItem) {
                $value = $billItem->getFeeBilled();
                $billItemStatus = $billItem->getStatus();
                $sumGenerated += $value;
                if ($billItemStatus >= BillItem::STATUS_BILL_APPROVED) {
                    $sumApproved += $value;
                }
                if ($billItemStatus >= BillItem::STATUS_FEE_GENERATED) {
                    $sumFeeGenerated += $value;
                }
                if ($billItemStatus >= BillItem::STATUS_BILL_COLLECTED) {
                    $sumCollected += $billItem->getFeeCollected();
                }
            }
        }

        return [
            'generated' => $sumGenerated,
            'approved' => $sumApproved,
            'fee_generated' => $sumFeeGenerated,
            'collected' => $sumCollected,
        ];
    }

    /**
     * @param User[] $clients
     * @param int    $year
     * @param int    $quarter
     *
     * @return array
     */
    public function getGraphData($clients, $year, $quarter)
    {
        $clientsGenerated = 0;
        $sumGenerated = 0;
        $sumApproved = 0;
        $sumFeeGenerated = 0;
        $sumCollected = 0;

        foreach ($clients as $client) {
            $status = $this->getClientBillStatus($client, $year, $quarter);
            if (BillItem::STATUS_BILL_NOT_GENERATED !== $status) {
                ++$clientsGenerated;
                $data = $this->getClientBillValues($client, $year, $quarter);
                $sumGenerated += $data['generated'];
                $sumApproved += $data['approved'];
                $sumFeeGenerated += $data['fee_generated'];
                $sumCollected += $data['collected'];
            }
        }

        if (0 === count($clients)) {
            return [
                'billGeneratedPercent' => 0,
                'billGeneratedValue' => 0,
                'billApprovedPercent' => 0,
                'billApprovedValue' => 0,
                'billFeeGeneratedPercent' => 0,
                'billFeeGeneratedValue' => 0,
                'billCollectedPercent' => 0,
                'billCollectedValue' => 0,
            ];
        }

        return [
            'billGeneratedPercent' => round($clientsGenerated / count($clients) * 100),
            'billGeneratedValue' => $sumGenerated,
            'billApprovedPercent' => (0 === $sumGenerated ? 0 : round($sumApproved / $sumGenerated * 100)),
            'billApprovedValue' => $sumApproved,
            'billFeeGeneratedPercent' => (0 === $sumGenerated ? 0 : round($sumFeeGenerated / $sumGenerated * 100)),
            'billFeeGeneratedValue' => $sumFeeGenerated,
            'billCollectedPercent' => (0 === $sumGenerated ? 0 : round($sumCollected / $sumGenerated * 100)),
            'billCollectedValue' => $sumCollected,
        ];
    }
}
