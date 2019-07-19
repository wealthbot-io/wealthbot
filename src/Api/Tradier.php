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
        $this->apiGateway = "https://api.tradier.com/v1/";
        $this->security = $security;
        $this->setApiKey();
        $this->sandbox = $sandbox;
    }

    /**
     * @throws \Exception
     */
    private function setApiKey(){
        $this->ria = $this->security->getUser()->getRia();
        $this->apiKey = $this->security->getUser() ?  $this->security->getUser()->getRiaCompanyInformation()->getCustodianKey() : "";
        $this->apiSecret = $this->security->getUser() ?  $this->security->getUser()->getRiaCompanyInformation()->getCustodianSecret() : "";
    }


    private function getHeaders(){

        $headers = [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ];

        return $headers;
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
            'headers' => $this->getHeaders()
        ]);
    }

    public function getProfile(){
       $data = $this->createRequest('GET','user/profile', []);
       dump($data);
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