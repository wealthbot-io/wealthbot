<?php

namespace App\Command;

use App\Entity\CeModelEntity;
use App\Entity\ClientAccount;
use App\Entity\ClientPortfolio;
use App\Entity\ClientPortfolioValue;
use App\Entity\ClientQuestionnaireAnswer;
use App\Entity\RiskAnswer;
use App\Entity\SecurityPrice;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Scheb\YahooFinanceApi\ApiClientFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebalancerCommand extends ContainerAwareCommand
{


    private $prices;


    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('wealthbot:rebalancer')
            ->setDescription('Wealthbot Rebalancer')
            ->setHelp('This command allows you to rebalance webo...')
        ;
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $securities = $this->updateSecurities($em, $output);
        $this->prices = $this->processPrices($em, $securities);
        $accounts = $em->getRepository("App\\Entity\\ClientAccount")->findAll();

        foreach ($accounts as $account){
            /** @var $account ClientAccount */
            $data[] = $this->processClientAccounts($account, $em);
        }
        foreach($data as $datum){
          foreach($datum as $item){
          $account = $em->getRepository("App\\Entity\\ClientAccount")->find($item['account_id']);

          $newValue = 0;
          foreach($item['values'] as $list){
             $newValue += $list['amount'];
          }
          $account->setValue(number_format($newValue,2, '.', ''));

          $this->buyOrSell($item, $em, $newValue, $output);
          $this->updatePortfolioValues($item, $em, $newValue, $output);
          }

        };

        $em->flush();
        $output->writeln('Success!');
    }
    /**
     * @param $output
     * @return object[]
     */
    protected function updateSecurities($em,$output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);

        // Create a new client from the factory
        $client = ApiClientFactory::createApiClient();

        $securities = $em->getRepository('App\Entity\Security')->findAll();
/*
        foreach($securities as $security){

            try {
                $quotes = $client->getQuotes([$security->getSymbol()]);
                $middle = ($quotes[0]->getRegularMarketDayHigh()+$quotes[0]->getRegularMarketDayLow()) * 0.5;
                if (count($quotes) > 0) {
                    $price = new SecurityPrice();
                    $price->setSecurity($security);
                    $price->setSecurityId($security->getId());
                    $price->setDatetime($quotes[0]->getDividendDate());
                    $price->setIsCurrent(true);
                    $price->setPrice($middle);
                    $price->setIsPosted(true);
                    $price->setSource($quotes[0]->getQuoteSourceName());
                    $em->persist($price);
                    $output->writeln("Security item [{$security->getSymbol()}] has been updated.");
                }
            } catch (\Exception $e){
                $output->writeln("Security item [{$security->getSymbol()}] rejected.");
            }
        };
*/
        $em->flush();

        return $securities;
    }

    /**
     * @param $em
     * @param $securities
     * @return array
     */
    protected function processPrices($em, $securities)
    {
        $prices = [];
        foreach ($securities as $security){

            /** @var $em EntityManager */
            $twoPrices  = $em->getRepository("App\Entity\SecurityPrice")->findBy(
                ['security_id'=>$security->getId()],[
                'datetime' => 'desc'
            ],2,0
            );
            $prices[] = [
                'security_id' => $security->getId(),
                'old_price' => isset($twoPrices[1]) ? $twoPrices[1]->getPrice() : 0,
                'price' => isset($twoPrices[0]) ? $twoPrices[0]->getPrice() : 0
            ];

            foreach($prices as $key => $price){
                if($price == 0){
                    unset($prices[$key]);
                }
            }
        }

        return $prices;
    }


    /**
     * @param $id
     * @return mixed
     */
    protected function getPricesDiff($id){
        foreach($this->prices as $price){
            if($price['security_id'] == $id){
                return $price['price'] / $price['old_price'];
            }
        }
    }


    /**
     * @param ClientAccount $account;
     */
    protected function processClientAccounts($account, $em)
    {
        $total = $account->getValueSum() + $account->getContributionsSum() - $account->getDistributionsSum();
        $data = $account->getClient()->getClientPortfolios()->map(function ($clientPortfolio) use ($total, $account, $em) {
            return [
                'risk_rating' => $clientPortfolio->getPortfolio()->getRiskRating(),
                'portfolio' => $clientPortfolio->getId(),
                'account_id'=>$account->getId(),
                'values' =>  $clientPortfolio->getPortfolio()->getModelEntities()->map(
                function ($entity) use ($total, $account, $clientPortfolio, $em) {

                    $prices_diff = $this->getPricesDiff($entity->getSecurityAssignment()->getSecurity()->getId());
                    $old_amount = ($entity->getPercent() / 100) * $total;
                    $amount = $prices_diff * $old_amount;
                    return
                        [
                            'user_id' => $account->getClient()->getId(),
                            'old_total' => $total,
                            'amount' => $amount,
                            'old_amount' => $old_amount,
                            'prices_diff' => $prices_diff,
                            'model_id' => $entity->getId(),
                            'security_id' => $entity->getSecurityAssignment()->getSecurity()->getId(),
                            'percent' => $entity->getPercent()
                        ];
                })];
        });

        return $data;
    }

    /**
     * @param $values
     */
    protected function updatePortfolioValues($cp, $em,$total, $output)
    {

            /** @var ClientPortfolio $clientPortfolio */
            $clientPortfolio = $em->getRepository('App\\Entity\\ClientPortfolio')->find($cp['portfolio']);
            $portfolioValue = new ClientPortfolioValue();
            $portfolioValue->setClientPortfolio($clientPortfolio);
            $portfolioValue->setTotalCashInMoneyMarket($total);
            $portfolioValue->setTotalInSecurities($total);
            $portfolioValue->setTotalCashInAccounts($total);
            $portfolioValue->setTotalValue($total);
            $portfolioValue->setSasCash(0);
            $portfolioValue->setCashBuffer(0);
            $portfolioValue->setBillingCash(0);
            $portfolioValue->setDate(new \DateTime('now'));
            $portfolioValue->setModelDeviation(4);
            $em->persist($portfolioValue);
            $em->flush();
            $output->writeln('New ClientPortfolioValue Added id: '.$portfolioValue->getId());


            return $portfolioValue;
    }

    protected function buyOrSell($data, $em,$total, $output){


            /** @var ClientPortfolio $portfolio */
            $portfolio = $em->getRepository('App\\Entity\\ClientPortfolio')->find($data['portfolio']);

            $answers = $portfolio->getClient()->getAnswers();

            $point = 0;
            foreach($answers as $answer){
                /** @var ClientQuestionnaireAnswer $answer */
                $point += $answer->getAnswer()->getPoint();

            };

            $point = ($point / 100) + 1;

            foreach($data['values'] as $datum){
                if($point - $datum['prices_diff'] > 0  ){

                    if($datum['prices_diff'] > 1){
                        $this->sell($datum, $em, $output);
                    } else {
                        $this->buy($datum, $em, $output);
                    }
                } else {
                    if($datum['prices_diff'] > 1){
                        $this->buy($datum, $em, $output);
                    } else {
                        $this->sell($datum, $em, $output);
                    }
                }

            }
    }

    protected function sell($info, $em, $output){

        $transaction = new Transaction();
        $security = $em->getRepository("App\\Entity\\Security")->find($info['security_id']);
        $transaction->setSecurity($security);
        //$transaction->setQty();
        /// $transaction->setSecurity();
        /// $transaction->setQty();
       /// $output->writeln('sell...');
    }

    protected function buy($info, $em, $output){
       /// $output->writeln('buy...');
    }

}
