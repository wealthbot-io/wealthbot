<?php

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use App\Entity\Bill;
use App\Entity\BillItem;
use App\Entity\ClientAccount;
use App\Entity\User;

class BillManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var FeeManager
     */
    protected $feeManager;

    /**
     * @param EntityManager $em
     * @param FeeManager    $feeManager
     */
    public function __construct(EntityManager $em, FeeManager $feeManager)
    {
        $this->em = $em;
        $this->feeManager = $feeManager;
    }

    /**
     * @param User $client
     * @param int  $year
     * @param int  $quarter
     * @param bool $flush
     *
     * @return Bill
     */
    public function createBill(User $client, $year, $quarter, $flush = true)
    {
        $bill = new Bill();
        $bill->setCreatedAt(new \DateTime());
        $bill->setClient($client);
        $bill->setYear($year);
        $bill->setQuarter($quarter);

        $this->em->persist($bill);

        if ($flush) {
            $this->em->flush();
        }

        return $bill;
    }

    public function prepareBill(User $client, $year, $quarter, $flush = false)
    {
        if (!$bill = $this->em->getRepository('App\Entity\Bill')->findByClientAndPeriod($client, $year, $quarter)) {
            $bill = $this->createBill($client, $year, $quarter, $flush);
        }

        return $bill;
    }

    /**
     * @param ClientAccount $account
     * @param int           $year
     * @param int           $quarter
     * @param bool          $flush
     */
    public function generateBill(ClientAccount $account, Bill $bill, $flush = false)
    {
        if (!$billItem = $this->em->getRepository('App\Entity\BillItem')->getByAccountAndPeriod($account, $bill->getYear(), $bill->getQuarter())) {
            $this->createBillItem($account, $bill, $flush);
        }
    }

    /**
     * @param ClientAccount $account
     * @param Bill          $bill
     * @param int           $year
     * @param int           $quarter
     * @param bool          $flush
     *
     * @return BillItem
     */
    public function createBillItem(ClientAccount $account, Bill $bill, $flush = true)
    {
        $sysAccount = $account->getSystemAccount();
        $year = $bill->getYear();
        $quarter = $bill->getQuarter();

        $riaFee = $this->getRiaBillSumByPeriod($account, $year, $quarter);
        $adminFee = $this->getAdminBillSumByPeriod($account, $year, $quarter);

        $billItem = new BillItem();
        $billItem->setSystemAccount($sysAccount);
        $billItem->setBill($bill);
        $billItem->setFeeBilled($riaFee + $adminFee);
        $billItem->setRiaFee($riaFee);
        $billItem->setAdminFee($adminFee);
        $billItem->setCreatedAt(new \DateTime());
        $billItem->setStatus(BillItem::STATUS_BILL_GENERATED);

        $this->em->persist($billItem);

        $flush && $this->em->flush();

        return $billItem;
    }

    /**
     * @param ClientAccount $account
     * @param $year
     * @param $quarter
     *
     * @return float|int
     */
    public function getRiaBillSumByPeriod(ClientAccount $account, $year, $quarter)
    {
        $fee = $this->feeManager->getRiaFee($account, $year, $quarter);

        return $this->calculateBillSumByPeriod($account, $fee, $year, $quarter);
    }

    /**
     * @param ClientAccount $account
     * @param $year
     * @param $quarter
     *
     * @return float|int
     */
    public function getAdminBillSumByPeriod(ClientAccount $account, $year, $quarter)
    {
        $fee = $this->feeManager->getCEFee($account, $year, $quarter);

        return $this->calculateBillSumByPeriod($account, $fee, $year, $quarter);
    }

    /**
     * @param ClientAccount $account
     * @param float         $fee
     * @param int           $year
     * @param int           $quarter
     *
     * @return float|int
     */
    public function calculateBillSumByPeriod(ClientAccount $account, $fee, $year, $quarter)
    {
        $period = $this->feeManager->getPeriod($year, $quarter);
        $systemAccount = $account->getSystemAccount();
        $accountValues = $this->em->getRepository('App\Entity\ClientAccountValue')->getAverageAccountValues($account, $period['startDate'], $period['endDate']);
        $dayInPeriod = $period['endDate']->diff($period['startDate'])->format('%a');

        return $this->feeManager->calculateFeeBilled($fee, $accountValues['count_values'], $dayInPeriod);
    }

    /**
     * @param BillItem $billItem
     * @param bool     $flush
     */
    public function setNoBill(BillItem $billItem, $flush = false)
    {
        $billItem->setStatus(BillItem::STATUS_WILL_NOT_BILL);
        $billItem->setRiaFee(0);
        $billItem->setAdminFee(0);
        $billItem->setFeeBilled(0);
        $billItem->setFeeCollected(0);

        $this->em->persist($billItem);

        $flush && $this->em->flush();
    }

    /**
     * @param ClientAccount $account
     * @param $year
     * @param $quarter
     * @param bool $flush
     *
     * @throws \Exception
     */
    public function approveAccount(ClientAccount $account, $year, $quarter, $flush = false)
    {
        $client = $account->getClient();
        /** @var Bill $bill */
        if (!$bill = $this->em->getRepository('App\Entity\Bill')->findByClientAndPeriod($client, $year, $quarter)) {
            throw new \Exception('Bill was not generated'); //todo: make custom exception
        }
        $bill->setApprovedAt(new \DateTime());

        if ($billItem = $this->em->getRepository('App\Entity\BillItem')->getByAccountAndPeriod($account, $year, $quarter)) {
            BillItem::STATUS_BILL_GENERATED === $billItem->getStatus() && $billItem->setStatus(BillItem::STATUS_BILL_APPROVED);
        } else {
            return false;
        }

        $flush && $this->em->flush();

        return true;
    }
}
