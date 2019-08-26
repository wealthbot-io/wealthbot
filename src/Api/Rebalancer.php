<?php

namespace App\Api;

use App\Entity\CeModelEntity;
use App\Entity\ClientAccount;
use App\Entity\ClientPortfolio;
use App\Entity\ClientPortfolioValue;
use App\Entity\Job;
use App\Entity\SecurityPrice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Class Rebalancer
 * @package App\Api
 */
class Rebalancer extends BaseRebalancer implements RebalancerInterface
{
    use Requests;
    use Trade;


    public $apiSandboxGateway = "https://sandbox.tradier.com/v1/";

    public $apiGateway = "https://api.tradier.com/v1/";

    /**
     * Rebalancer constructor.
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     * @param \Symfony\Component\Security\Core\Security $security
     * @param bool $sandbox
     * @throws \Exception
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, \Symfony\Component\Security\Core\Security $security)
    {
        $this->container = $container;
        $this->httpClient = HttpClient::create();
        $this->em = $entityManager;
        $this->security = $security;
        $this->sandbox = (bool) $this->container->getParameter('tradier_sandbox');
        $this->setApiKey();
    }

    /**
     * Main Method
     * @throws \Exception
     */
    public function rebalance()
    {
        $securities = $this->em->getRepository("App\\Entity\\Security")->findAll();
        $this->prices = $this->processPrices($securities);
        $actions = $this->em->getRepository('App\\Entity\\RebalancerAction')->findAll();

        foreach ($actions as $action) {
            /** @var Job $job */
            $job = $action->getJob();

            $clientAccount = $this->em->getRepository('App\\Entity\\ClientAccount')->findOneByClient($action->getClientPortfolioValue()->getClientPortfolio()->getClientId());


            if ($job->getRebalanceType() === Job::REBALANCE_TYPE_REQUIRED_CASH) {
                $this->processClientPortfolio($action->getClientPortfolioValue(), $clientAccount, Job::REBALANCE_TYPE_REQUIRED_CASH);
            } elseif ($job->getRebalanceType() === Job::REBALANCE_TYPE_FULL_AND_TLH) {
                $this->processClientPortfolio($action->getClientPortfolioValue(), $clientAccount, Job::REBALANCE_TYPE_FULL_AND_TLH);
            };
            $job->setFinishedAt(new \DateTime('now'));
            $job->setIsError(false);
            $job->setNameRebalancer('tradier');
            $job->setUser($this->ria);
            $this->em->persist($job);
        }
        $this->em->flush();
    }


    public function initialRebalance(ClientPortfolio $clientPortfolio){

        $securities = $this->em->getRepository("App\\Entity\\Security")->findAll();
        $this->prices = $this->processPrices($securities);
        $actions = $this->em->getRepository('App\\Entity\\RebalancerAction')->findAll();



        $client = $clientPortfolio->getClient();
        $infos[] = $clientPortfolio->getPortfolio()->getModelEntities()->map(function(CeModelEntity $item) use ($clientPortfolio, $client) {

            /** @var ClientAccount $clientAccount */
            $clientAccount = $clientPortfolio->getClient()->getClientAccounts()->first();

            $value = 0;

            foreach($clientPortfolio->getClient()->getClientAccounts() as $account){
                $value += $account->getValueSum();
            };
            $value = $item->getPercent() * ($value / 100);
            $this->buy([
                'account_id' => 'VA' . $clientAccount->getAccountNumber(),
                'client_id' => $client->getId(),
                'symbol' => $item->getSecurityAssignment()->getSecurity()->getSymbol(),
                'security_id' => $item->getSecurityAssignment()->getSecurity()->getId(),
                'amount' => $value
            ], $clientAccount);
        });
    }


    /**
     * Process client portfolio
     * @param ClientPortfolioValue $clientPortfolioValue
     * @param $clientAccount
     * @param $type
     * @throws \Exception
     */
    protected function processClientPortfolio(ClientPortfolioValue $clientPortfolioValue, $clientAccount, $type)
    {
        /** @var \App\Entity\\ClientPortfolio $clientPortfolio */
        $clientPortfolio = $clientPortfolioValue->getClientPortfolio();

        if ($type==Job::REBALANCE_TYPE_REQUIRED_CASH) {
            $value = $clientPortfolioValue->getInvestableCash();
        } else {
            $value = $clientPortfolioValue->getTotalValue();
        }

        $data =  [
                    'risk_rating' => $clientPortfolio->getPortfolio()->getRiskRating(),
                    'portfolio' => $clientPortfolio->getId(),
                    'values' => $clientPortfolio->getPortfolio()->getModelEntities()->map(
                        function ($entity) use ($value) {
                            /** @var ClientPortfolio $clientPortfolio */

                            $prices_diff = $this->getPricesDiff($entity->getSecurityAssignment()->getSecurity()->getId());
                            $old_amount = ($entity->getPercent() / 100) * $value;
                            $amount = $prices_diff * $old_amount;
                            return
                                [
                                    'old_value' => $value,
                                    'amount' => $amount,
                                    'old_amount' => $old_amount,
                                    'prices_diff' => $prices_diff,
                                    'model_id' => $entity->getId(),
                                    'security_id' => $entity->getSecurityAssignment()->getSecurity()->getId(),
                                    'percent' => $entity->getPercent()
                                ];
                        }
                    )];


        $this->buyOrSell($data, $clientAccount);
        $this->updatePortfolioValues($clientPortfolio, $clientPortfolioValue->getTotalValue());
    }


    /**
     * Update Client Portfolio Values
     * @param $cp
     * @param $em
     * @param $total
     * @return ClientPortfolioValue
     * @throws \Exception
     */
    protected function updatePortfolioValues($clientPortfolio, $total)
    {

        /** @var ClientPortfolio $clientPortfolio */
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
        $this->em->persist($portfolioValue);
        $this->em->flush();


        return $portfolioValue;
    }


    /**
     * Update Security Prices Command
     * @return object[]
     */
    public function updateSecurities()
    {
        $securities = $this->em->getRepository('App\Entity\Security')->findAll();
        $symbols = implode(",", array_map(function ($security) {
            return $security->getSymbol();
        }, $securities));
        $quotes = $this->getQuotes($symbols);

        foreach ($quotes->quotes->quote as $quote) {
            if (isset($quote->last)) {
                $security = $this->em->getRepository('App\Entity\Security')->findOneBySymbol($quote->symbol);
                $price = new SecurityPrice();
                $price->setSecurity($security);
                $price->setSecurityId($security->getId());
                $price->setDatetime(new \DateTime('now'));
                $price->setIsCurrent(true);
                $price->setPrice($quote->last);
                $price->setIsPosted(true);
                $price->setSource("tradier");
                $this->em->persist($price);
            }
        };

        $this->em->flush();

        return $securities;
    }
}
