<?php
namespace App\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Security;

class Tradier {

    private $httpClient;

    private $em;

    private $apiGateway;

    private $apiKey;

    private $apiSecret;

    private $container;

    private $ria;

    /**
     * AmeritradeManager constructor.
     * @param EntityManager $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, Security $security)
    {

        $this->container = $container;
        $this->httpClient = HttpClient::create();
        $this->em = $entityManager;
        $this->apiGateway = "https://api.tradier.com/v2/";
        $this->security = $security;
        $this->setApiKey();
    }

    /**
     * @throws \Exception
     */
    public function setApiKey(){
        $this->ria = $this->security->getUser()->getRia();
        $this->apiKey = $this->security->getUser() ?  $this->security->getUser()->getRiaCompanyInformation()->getCustodianKey() : "";
        $this->apiSecret = $this->security->getUser() ?  $this->security->getUser()->getRiaCompanyInformation()->getCustodianSecret() : "";
    }

    public function getHeaders(){

        $base64 = $this->apiKey . ":". $this->apiSecret;
        $base64 = base64_encode($base64);

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization: Basic' . $base64
        ];

        return $headers;
    }

    /**
     * @return string
     */
    public function addApiKey(){
        return '?apiKey=' . $this->apiKey;
    }

    private function createRequest($data){

    }


    public function createAccount(){
       // PUT //accounts/{accountId}/preferences
    }


    public function getAccounts(){
        return $this->httpClient->request('GET', $this->apiGateway.'accounts'.$this->addApiKey())->getContent();
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