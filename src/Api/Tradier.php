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

    private $apiSandboxGateway;

    private $sandbox;

    private $apiKey;

    private $apiSecret;

    private $container;

    private $ria;

    /**
     * AmeritradeManager constructor.
     * @param EntityManager $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, Security $security, bool $sandbox = true)
    {

        $this->container = $container;
        $this->httpClient = HttpClient::create();
        $this->em = $entityManager;
        $this->apiSandboxGateway = "https://sandbox.tradier.com/v1/";
        $this->apiGateway = "https://tradier.com/v1/";
        $this->security = $security;
        $this->setApiKey();
        $this->sandbox = true;
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

        $base64 = $this->apiKey;

        $headers = [
            'Content-Type' => 'application/json',
            'Authorization: Bearer' . $base64
        ];

        return $headers;
    }

    /**
     * @param bool $sandbox
     * @return string
     */
    public function getEndpoint(){

        return ($this->sandbox)? $this->apiSandboxGateway : $this->apiGateway;
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