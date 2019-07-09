<?php

namespace App\Command;

use App\Entity\CeModelEntity;
use App\Entity\ClientPortfolio;
use App\Entity\SecurityPrice;
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
            ->setHelp('');
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $securities = $this->updateSecurities($em);
        $this->prices = $this->processPrices($em, $securities);
        $portfolios = $em->getRepository("App\\Entity\\ClientPortfolio")->findAll();
        foreach ($portfolios as $portfolio){
            $output->writeln('processing portfolio id:' . $portfolio->getId());
            $this->processPortfolio($portfolio,$em);
        };
        $output->writeln('Success!');
    }



    /**
     * @see Command
     */
    protected function updateSecurities($output)
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
                if (count($quotes) > 0) {
                    $price = new SecurityPrice();
                    $price->setSecurity($security);
                    $price->setSecurityId($security->getId());
                    $price->setDatetime($quotes[0]->getDividendDate());
                    $price->setIsCurrent(true);
                    $price->setPrice($quotes[0]->getAsk());
                    $price->setIsPosted(true);
                    $price->setSource($quotes[0]->getQuoteSourceName());
                    $em->persist($price);
                    $output->writeln("Security item [{$security->getSymbol()}] has been updated.");
                }
            } catch (\Exception $e){
                $output->writeln("Security item [{$security->getSymbol()}] rejected.");
            }
        };

        $em->flush(); */
        return $securities;
    }

    protected function processPrices($em, $securities){
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
     * @param ClientPortfolio $portfolio
     * @param $em
     */
    protected function processPortfolio($portfolio, $em){

        $accounts = $portfolio->getClient()->getClientAccounts();
        foreach($accounts as $account){
            $total = $account->getValueSum() + $account->getContributionsSum() - $account->getDistributionsSum();

            $modelValues[] = $portfolio->getPortfolio()->getModelEntities()->map(

                function($entity) use ($total) {
                    return
                        [
                            'model_id' => $entity->getModel()->getId(),
                       /** @var $entity \App\Entity\CeModelEntity */
                      'security_id'=> $entity->getSecurityAssignment()->getSecurity()->getId(), 'percent' => $entity->getPercent() ];
            });

        };
    }

}
