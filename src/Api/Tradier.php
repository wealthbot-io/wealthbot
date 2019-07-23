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
     * Tradier constructor.
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     * @param Security $security
     * @param bool $sandbox
     * @throws \Exception
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container, Security $security, bool $sandbox = true)
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

        if($this->security->getUser()->hasRole('ROLE_RIA')){
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