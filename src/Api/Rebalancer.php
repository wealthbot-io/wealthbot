<?php

namespace App\Api;

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
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Scheb\YahooFinanceApi\ApiClientFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

class Rebalancer
{

    private $container;

    private $httpClient;

    private $em;

    private $apiGateway;

    private $apiSandboxGateway;

    private $sandbox;

    private $apiKey;

    private $apiSecret;

    private $ria;

    private $prices;



    public function __construct(EntityManagerInterface $entityManager, \Symfony\Component\DependencyInjection\ContainerInterface $container, \Symfony\Component\Security\Core\Security $security, bool $sandbox = true)
    {
        $this->container = $container;
        $this->httpClient = HttpClient::create();
        $this->em = $entityManager;
        $this->apiSandboxGateway = "https://sandbox.tradier.com/v1/";
        $this->apiGateway = "https://api.tradier.com/v1/";
        $this->security = $security;
        $this->sandbox = $sandbox;
        $this->setApiKey();
    }


    /**
     * @throws \Exception
     */
    private function setApiKey(){
        if($this->security->getUser()->hasRole('ROLE_ADMIN')){
            $this->ria = $this->getDoctrine()->getRepository('App\Entity\User')->findOneByEmail('raiden@wealthbot.io');
        }
        else if($this->security->getUser()->hasRole('ROLE_RIA')){
            $this->ria = $this->security->getUser();
        } else {
            $this->ria = $this->security->getUser()->getRia();
        };
        $this->apiKey = $this->ria ?  $this->ria->getRiaCompanyInformation()->getCustodianKey() : " ";
        $this->apiSecret = $this->ria ?  $this->ria->getRiaCompanyInformation()->getCustodianSecret() : " ";
    }
    /**
     * @param bool $sandbox
     * @return string
     */
    private function getEndpoint(){
        return ($this->sandbox==true)? $this->apiSandboxGateway : $this->apiGateway;
    }


    private function createRequest($method, $path, $body = []){
        return $this->httpClient->request($method, $this->getEndpoint().$path,[
            'headers' =>  [
                'Accept: application/json',
                'Authorization: Bearer '.$this->apiKey,
                'Connection: close'
            ]
        ]);
    }

    public function getQuotes($symbol){
        return $this->createRequest('GET','markets/quotes?symbols='.$symbol)->getContent();
    }

    public function getProfile(){
        return $this->createRequest('GET','user/profile', [])->getContent();
    }

    /**
     * @see Command
     */
    public function start()
    {
        $em = $this->container->get('doctrine')->getManager();
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        $securities = $em->getRepository("App\\Entity\\Security")->findAll();
        $this->prices = $this->processPrices($em, $securities);
        $accounts = $em->getRepository("App\\Entity\\ClientAccount")->findAll();

        foreach ($accounts as $account){
            /** @var $account ClientAccount */
            $data[] = $this->processClientAccounts($account, $em);
        }
        foreach($data as $datum){
          foreach($datum as $item){
              if(isset($item['account_id'])) {
                  $account = $em->getRepository("App\\Entity\\ClientAccount")->find($item['account_id']);

                  $newValue = 0;
                  foreach ($item['values'] as $list) {
                      $newValue += $list['amount'];
                  }
                  $account->setValue(number_format($newValue, 2, '.', ''));
                  $this->buyOrSell($item);
                  $this->updatePortfolioValues($item, $newValue);
              };
          }

        };

        $em->flush();
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
    protected function getPricesDiff($id)
    {
        foreach($this->prices as $price){
            if($price['security_id'] == $id){
                return $price['price'] / $price['old_price'];
            }
        }
    }

    protected function getLatestPriceBySecurityId($id)
    {
        foreach($this->prices as $price){
            if($price['security_id'] == $id){
                return $price['price'];
            }
        }
    }


    /**
     * @param $account
     * @param $em
     * @return mixed
     */
    protected function processClientAccounts($account, $em)
    {
        $total = $account->getValueSum() + $account->getContributionsSum() - $account->getDistributionsSum();
        $data = $account->getClient()->getClientPortfolios()->map(function ($clientPortfolio) use ($total, $account, $em) {

            if($clientPortfolio->isClientAccepted()) {
                /** @var \App\Entity\\ClientPortfolio $clientPortfolio */
                return [
                    'risk_rating' => $clientPortfolio->getPortfolio()->getRiskRating(),
                    'portfolio' => $clientPortfolio->getId(),
                    'account_id' => $account->getId(),
                    'values' => $clientPortfolio->getPortfolio()->getModelEntities()->map(
                        function ($entity) use ($total, $account, $clientPortfolio, $em) {
                            /** @var ClientPortfolio $clientPortfolio */

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
            }
        });

        return $data;
    }


    /**
     * @param $cp
     * @param $em
     * @param $total
     * @return ClientPortfolioValue
     * @throws \Exception
     */
    protected function updatePortfolioValues($cp,$total)
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
            $this->em->persist($portfolioValue);
            $this->em->flush();


            return $portfolioValue;
    }


    /**
     * @param $data
     * @param $em
     * @throws \Exception
     */
    protected function buyOrSell($data) {

        if(isset($data['portfolio'])) {
            /** @var ClientPortfolio $portfolio */
            $portfolio = $this->em->getRepository('App\\Entity\\ClientPortfolio')->find($data['portfolio']);

            $answers = $portfolio->getClient()->getAnswers();

            $point = 0;
            foreach($answers as $answer){
                /** @var ClientQuestionnaireAnswer $answer */
                $point += $answer->getAnswer()->getPoint();

            };

            $point = ($point / 100) + 1;

                foreach ($data['values'] as $datum) {
                    if ($point - $datum['prices_diff'] > 0) {

                        if ($datum['prices_diff'] > 1) {
                            $this->sell($datum, $data['account_id']);
                        } else {
                            $this->buy($datum, $data['account_id']);
                        }
                    } else {
                        if ($datum['prices_diff'] > 1) {
                            $this->buy($datum, $data['account_id']);
                        } else {
                            $this->sell($datum, $data['account_id']);
                        }
                    }

                }
            }

    }

    /**
     * @param $info
     * @param $account_id
     * @param $em
     * @throws \Exception
     */
    protected function sell($info, $account_id){

        /** @var Security $security */
        $security = $this->em->getRepository("App\\Entity\\Security")->find($info['security_id']);
        /** @var ClientAccount $account */
        $account = $this->em->getRepository("App\\Entity\\ClientAccount")->find($account_id);
        /** @var SystemAccount $systemAccount */
        $systemAccount = $account->getSystemAccount();



        $transactionType = new TransactionType();
        $transactionType
            ->setName('SELL')
            ->setActivity('sell')
            ->setReportAs(null)
            ->setDescription('Sell '. $security->getSymbol())
            ->setActivity('sell');


        $this->em->persist($transactionType);



        $lot = new Lot();
        $position = new Position();
        $position->setSecurity($security)
            ->setDate(new \DateTime('now'))
            ->setAmount($info['amount'])
            ->setLots([$lot]);
        $position->setClientSystemAccount($systemAccount);
        $position->setQuantity(1);
        $position->setStatus(Position::POSITION_STATUS_INITIAL);

        $lot->setAmount($info['amount']);
        $lot->setClientSystemAccount($systemAccount);
        $lot->setStatus(Lot::LOT_IS_OPEN);
        $lot->setDate(new \DateTime('now'));
        $lot->setQuantity(1);
        $lot->setSecurity($security);
        $lot->setCostBasisKnown(true);
        $lot->setCostBasis($this->getLatestPriceBySecurityId($security->getId()));
        $lot->setWashSale(false);
        $lot->setPosition($position);

        $this->em->persist($lot);
        $this->em->persist($position);



        $transaction = new Transaction();
        $security = $em->getRepository("App\\Entity\\Security")->find($info['security_id']);
        $transaction->setSecurity($security);
        $transaction->setQty($info['amount']);
        $transaction->setAccount($systemAccount);
        $transaction->setTransactionType($transactionType);
        $transaction->setTxDate(new \DateTime('now'));
        $transaction->setLot($lot);
        $em->persist($transaction);


        $em->flush();


    }

    /**
     * @param $info
     * @param $account_id
     * @param $em
     * @throws \Exception
     */
    protected function buy($info, $account_id){

        /** @var Security $security */
        $security = $this->em->getRepository("App\\Entity\\Security")->find($info['security_id']);
        /** @var ClientAccount $account */
        $account = $this->em->getRepository("App\\Entity\\ClientAccount")->find($account_id);
        /** @var SystemAccount $systemAccount */
        $systemAccount = $account->getSystemAccount();

        $transactionType = new TransactionType();
        $transactionType
            ->setName('BUY')
            ->setActivity('buy')
            ->setReportAs(null)
            ->setDescription('Buy '. $security->getSymbol())
            ->setActivity('buy');

        $this->em->persist($transactionType);


        $lot = new Lot();
        $position = new Position();
        $position->setSecurity($security)
            ->setDate(new \DateTime('now'))
            ->setAmount($info['amount'])
            ->setLots([$lot]);
        $position->setClientSystemAccount($systemAccount);
        $position->setQuantity(1);
        $position->setStatus(Position::POSITION_STATUS_INITIAL);

        $lot->setAmount($info['amount']);
        $lot->setClientSystemAccount($systemAccount);
        $lot->setStatus(Lot::LOT_IS_OPEN);
        $lot->setDate(new \DateTime('now'));
        $lot->setQuantity(1);
        $lot->setSecurity($security);
        $lot->setCostBasisKnown(true);
        $lot->setCostBasis($this->getLatestPriceBySecurityId($security->getId()));
        $lot->setWashSale(false);
        $lot->setPosition($position);

        $this->em->persist($lot);
        $this->em->persist($position);

        $transaction = new Transaction();
        $transaction->setSecurity($security);
        $transaction->setQty($info['amount']);
        $transaction->setAccount($systemAccount);
        $transaction->setTransactionType($transactionType);
        $transaction->setTxDate(new \DateTime('now'));
        $transaction->setLot($lot);
        $this->em->persist($transaction);
        $this->em->flush();
    }

}
