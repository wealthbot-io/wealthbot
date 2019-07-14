<?php
namespace App\Manager;

use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Monolog\Logger;

class AmeritradeManager {

    private $httpClient;

    private $em;

    private $logger;

    private $apiGateway;

    private $apiKey;


    /**
     * AmeritradeManager constructor.
     * @param Client $guzzleHttp
     * @param EntityManager $entityManager
     * @param Logger $logger
     */
    public function __construct(Client $guzzleHttp, EntityManager $entityManager, Logger $logger)
    {

        $this->httpClient = $guzzleHttp;
        $this->em = $entityManager;
        $this->logger;
        $this->apiGateway = "https://api.tdameritrade.com/v1/";
    }

    /**
     * @param $key
     */
    public function setApiKey($key){

        $this->apiKey = $key;
    }

    private function createRequest($data){

    }


    public function createAccount(){
       // PUT //accounts/{accountId}/preferences
    }


    public function getAccounts(){
       //GET //accounts
    }


    public function getAccount(){
       // GET //accounts/{accountId}
    }


    public function placeOrder(){
      //POST  //accounts/{accountId}/orders
    }



    public function getOrder(){
       //GET //accounts/{accountId}/orders/{orderId}
    }



    public function cancelOrder(){
       //DELETE //accounts/{accountId}/orders/{orderId}
    }

}