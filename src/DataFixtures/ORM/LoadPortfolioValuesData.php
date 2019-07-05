<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\ClientPortfolio;
use App\Entity\ClientPortfolioValue;

class LoadPortfolioValuesData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $repository = $manager->getRepository('App\Entity\ClientPortfolio');
        $clientPortfolios = $repository->findAll();

        $years = [2012, 2013];
        // $mo = 13;
        mt_srand(0);
        foreach ($years as $yr) {
            $mo = (2012 === $yr) ? 13 : (int) date('m');
            for ($m = 1; $m < $mo; ++$m) {
                /** @var ClientPortfolio $clientPortfolio */
                foreach ($clientPortfolios as $cp => $clientPortfolio) {
                    $portfolioValue = new ClientPortfolioValue();

                    $portfolioValue->setClientPortfolio($clientPortfolio);
                    $pdate = new \DateTime();
                    $pdate->setDate($yr, $m, 1);

                    if ('miles@wealthbot.io' === $clientPortfolio->getClient()->getEmail()) {
                        $securities = 758379;
                        $money_market = 881586;
                        $accounts = 685475;
                        $sasCash = 3000;
                        $cashBuffer = 7000;
                        $billingCash = 10000;
                    } else {
                        $securities = mt_rand(0, 1000000);
                        $money_market = mt_rand(0, 1000000);
                        $accounts = mt_rand(0, 1000000);
                        $sasCash = 0;
                        $cashBuffer = 0;
                        $billingCash = 0;
                    }

                    $total = $securities + $money_market + $accounts;
                    $portfolioValue->setTotalCashInMoneyMarket($money_market);
                    $portfolioValue->setTotalInSecurities($securities);
                    $portfolioValue->setTotalCashInAccounts($accounts);
                    $portfolioValue->setTotalValue($total);
                    $portfolioValue->setSasCash($sasCash);
                    $portfolioValue->setCashBuffer($cashBuffer);
                    $portfolioValue->setBillingCash($billingCash);
                    $portfolioValue->setDate($pdate);
                    $portfolioValue->setModelDeviation(4);
                    $portfolioValue->setRequiredCash(mt_rand(1000, 300000));
                    $portfolioValue->setInvestableCash(mt_rand(1000, 30000));

                    $manager->persist($portfolioValue);
                }
            }
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 20;
    }
}
