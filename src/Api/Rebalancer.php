<?php

namespace App\Api;
use App\Entity\ClientPortfolio;
use App\Entity\ClientPortfolioValue;
use App\Entity\Job;
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


    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    protected  $httpClient;

    /**
     * @var EntityManagerInterface
     */
    protected  $em;

    /**
     * @var string
     */
    protected  $apiGateway;

    /**
     * @var string
     */
    protected  $apiSandboxGateway;

    /**
     * @var bool
     */
    protected  $sandbox;

    /**
     * @var
     */
    protected  $apiKey;

    /**
     * @var
     */
    protected  $apiSecret;

    /**
     * @var
     */
    protected  $ria;

    /**
     * @var array
     */
    protected  $prices;


    /**
     * Rebalancer constructor.
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     * @param \Symfony\Component\Security\Core\Security $security
     * @param bool $sandbox
     * @throws \Exception
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container,\Symfony\Component\Security\Core\Security $security)
    {
        $this->container = $container;
        $this->httpClient = HttpClient::create();
        $this->em = $entityManager;
        $this->apiSandboxGateway = "https://sandbox.tradier.com/v1/";
        $this->apiGateway = "https://api.tradier.com/v1/";
        $this->security = $security;
        $this->sandbox = (bool) $this->container->getParameter('tradier_sandbox');
        $this->setApiKey();
    }

    /**
     * @throws \Exception
     */
    public function rebalance()
    {

        $securities = $this->em->getRepository("App\\Entity\\Security")->findAll();
        $this->prices = $this->processPrices($securities);
        $actions = $this->em->getRepository('App\\Entity\\RebalancerAction')->findAll();

        foreach($actions as $action){
            /** @var Job $job */
            $job = $action->getJob();

            $clientAccount = $this->em->getRepository('App\\Entity\\ClientAccount')->findOneByClient($action->getClientPortfolioValue()->getClientPortfolio()->getClientId());


            if($job->getRebalanceType() === Job::REBALANCE_TYPE_REQUIRED_CASH){
                $this->processClientPortfolio($action->getClientPortfolioValue(),$clientAccount, Job::REBALANCE_TYPE_REQUIRED_CASH);
            } else if($job->getRebalanceType() === Job::REBALANCE_TYPE_FULL_AND_TLH){
                $this->processClientPortfolio($action->getClientPortfolioValue(),$clientAccount, Job::REBALANCE_TYPE_FULL_AND_TLH);
            };
            $job->setFinishedAt(new \DateTime('now'));
            $job->setIsError(false);
            $job->setNameRebalancer('local');
            $job->setUser($this->ria);
            $this->em->persist($job);
        }
        $this->em->flush();
    }


    /**
     * @param $account
     * @param $em
     * @return mixed
     */
    protected function processClientPortfolio(ClientPortfolioValue $clientPortfolioValue, $clientAccount, $type)
    {
                /** @var \App\Entity\\ClientPortfolio $clientPortfolio */
                $clientPortfolio = $clientPortfolioValue->getClientPortfolio();

                if($type==Job::REBALANCE_TYPE_REQUIRED_CASH){
                    $value = $clientPortfolioValue->getInvestableCash();
                } else {
                    $value = $clientPortfolioValue->getTotalValue();
                }

                $data =  [
                    'risk_rating' => $clientPortfolio->getPortfolio()->getRiskRating(),
                    'portfolio' => $clientPortfolio->getId(),
                    'values' => $clientPortfolio->getPortfolio()->getModelEntities()->map(
                        function ($entity) use ($clientPortfolio, $value) {
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
                        })];


                $this->buyOrSell($data, $clientAccount);
                $this->updatePortfolioValues($clientPortfolio, $clientPortfolioValue->getTotalValue());
    }


    /**
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
}