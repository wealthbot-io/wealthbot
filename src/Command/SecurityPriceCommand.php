<?php

namespace App\Command;

use App\Entity\CeModelEntity;
use App\Entity\ClientAccount;
use App\Entity\ClientPortfolio;
use App\Entity\ClientPortfolioValue;
use App\Entity\ClientQuestionnaireAnswer;
use App\Entity\Lot;
use App\Entity\Position;
use App\Entity\RiskAnswer;
use App\Entity\Security;
use App\Entity\SecurityPrice;
use App\Entity\SystemAccount;
use App\Entity\Transaction;
use App\Entity\TransactionType;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Scheb\YahooFinanceApi\ApiClientFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SecurityPriceCommand extends ContainerAwareCommand
{


    private $prices;


    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('wealthbot:security:price')
            ->setDescription('Wealthbot Asset Prices History')
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

        $em->flush();

        return $securities;
    }

}
