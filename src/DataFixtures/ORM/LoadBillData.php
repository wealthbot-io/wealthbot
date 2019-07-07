<?php
/**
 * Created by PhpStorm.
 * User: virtustilus
 * Date: 30.12.13
 * Time: 0:03.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Manager\FeeManager;
use App\Entity\Bill;
use App\Entity\BillItem;
use App\Manager\PeriodManager;
use App\Entity\User;

class LoadBillData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /** @var Container */
    private $container;

    /** @var PeriodManager */
    private $periodManager;

    /** @var FeeManager */
    private $feeManager;

    public function load(ObjectManager $manager)
    {
        $this->feeManager = $this->container->get('wealthbot.manager.fee');
        $this->periodManager = $this->container->get('wealthbot_ria.period.manager');

        /** @var User $userMiles */
        $userMiles = $this->getReference('clientN2');
        $this->createBill($userMiles, '2013-04-03 13:12:00', $manager);

        /** @var User $userEverhart */
        $userEverhart = $this->getReference('clientN3');
        $this->createBill($userEverhart, '2013-07-02 16:15:12', $manager);
    }

    public function getOrder()
    {
        return 11;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function createBill(User $user, $dateStr, ObjectManager $manager)
    {
        $date = new \DateTime($dateStr);
        $period = $this->periodManager->getPreviousQuarter($date);
        $accounts = $user->getClientAccounts();
        $bill = new Bill();
        $bill->setCreatedAt(new \DateTime($dateStr));
        $bill->setClient($user);
        $bill->setYear($period['year']);
        $bill->setQuarter($period['quarter']);
        $manager->persist($bill);

        foreach ($accounts as $account) {
            $systemAccount = $account->getSystemAccount();
            if ($systemAccount) {
                $billItem = new BillItem();
                $billItem->setSystemAccount($systemAccount);
                $billItem->setBill($bill);
                $billItem->setFeeBilled($this->feeManager->getRiaFee($account, $period['year'], $period['quarter']));
                $billItem->setCreatedAt(new \DateTime($dateStr));
                $manager->persist($billItem);
            }
        }

        $manager->flush();
    }
}
