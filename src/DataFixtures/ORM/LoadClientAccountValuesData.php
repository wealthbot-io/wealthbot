<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 25.10.13
 * Time: 13:58.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\ClientAccountValue;
use App\Entity\SystemAccount;
use App\Model\AbstractCsvFixture;

class LoadClientAccountValuesData extends AbstractCsvFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var SystemAccount $systemAccount */
        $systemAccount = $this->getReference('system-account-744888385');
        $clientPortfolio = $this->getReference('client-portfolio-'.$systemAccount->getClient()->getProfile());

        $accountValueData = $this->getCsvData('client_account_values.csv');

        foreach ($accountValueData as $i => $accountRow) {
            //skip first line with column titles
            if (0 === $i) {
                continue;
            }

            $accountValue = new ClientAccountValue();
            $accountValue->setClientPortfolio($clientPortfolio);
            $accountValue->setSystemClientAccount($systemAccount);
            $accountValue->setSource('tradier');
            $accountValue->setTotalInSecurities($accountRow[1]);
            $accountValue->setTotalCashInAccount($accountRow[2]);
            $accountValue->setTotalCashInMoneyMarket($accountRow[3]);
            $accountValue->setTotalValue($accountRow[0]);
            $accountValue->setDate(new \DateTime($accountRow[4]));
            $accountValue->setModelDeviation($accountRow[5]);
            $accountValue->setRequiredCash(mt_rand(100, 100000));
            $accountValue->setInvestableCash(mt_rand(100, 100000));

            // @TODO Ask Vlad how to fill that values.
            $accountValue->setSasCash(2000);
            $accountValue->setCashBuffer(1500);
            $accountValue->setBillingCash(3750);

            $manager->persist($accountValue);
        }

        $manager->flush();

        $repository = $manager->getRepository('App\Entity\ClientAccount');
        $accounts = $repository->findAll();

        mt_srand(0);
        foreach ($accounts as $account) {
            $client = $account->getClient();
            $clientPortfolios = $client->getClientPortfolios();

            if (!isset($clientPortfolios[0])) {
                continue;
            }

            $clientPortfolio = $clientPortfolios[0];
            $clientSystemAccount = $account->getSystemAccount();

            if ($clientPortfolio && $clientSystemAccount) {
                $exist = $manager->getRepository('App\Entity\ClientAccountValue')->findOneBy(
                    [
                        'client_portfolio_id' => $clientPortfolio->getId(),
                        'system_client_account_id' => $clientSystemAccount->getId(),
                    ]
                );

                if ($exist) {
                    continue;
                }

                $securitiesTotal = mt_rand(0, 1000000);
                $moneyMarket = mt_rand(0, 1000000);
                $accountsTotal = mt_rand(0, 1000000);
                $total = $securitiesTotal + $moneyMarket + $accountsTotal;

                $date = $account->getClient()->getCreated();
                $date->modify('+3 days');

                $accountValue = new ClientAccountValue();
                $accountValue->setClientPortfolio($clientPortfolio);
                $accountValue->setSystemClientAccount($clientSystemAccount);
                $accountValue->setSource('tradier');
                $accountValue->setTotalInSecurities($securitiesTotal);
                $accountValue->setTotalCashInAccount($accountsTotal);
                $accountValue->setTotalCashInMoneyMarket($moneyMarket);
                $accountValue->setTotalValue($total);
                $accountValue->setSasCash(2000);
                $accountValue->setCashBuffer(1500);
                $accountValue->setBillingCash(3750);
                $accountValue->setRequiredCash(mt_rand(100, 100000));
                $accountValue->setInvestableCash(mt_rand(100, 100000));
                $accountValue->setDate($date);
                $accountValue->setModelDeviation(mt_rand(1, 99));

                $manager->persist($accountValue);
                $manager->flush();
            }
        }
    }

    public function getOrder()
    {
        return 21;
    }
}
