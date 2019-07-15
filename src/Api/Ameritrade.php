<?php
namespace App\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Core\Security;

class Ameritrade {

    private $httpClient;

    private $em;

    private $apiGateway;

    private $apiKey;

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
        $this->apiGateway = "https://api.tdameritrade.com/v1/";
        $this->security = $security;
        $this->setApiKey();
    }

    /**
     * @throws \Exception
     */
    public function setApiKey(){
        $this->ria = $this->security->getUser()->getRia();
        $this->apiKey = $this->security->getUser() ?  $this->security->getUser()->getRiaCompanyInformation()->getAmeritradeKey() : "";
    }

    public function getAccessToken(){
        $body = [
            'grant_type' => 'authorization_code',
            'client_secret' => $this->apiKey,
            'client_id' => 'wealthbotio',
            'redirect_uri' => 'https://127.0.0.1:8000/ameriatrade',
            'access_type' => 'online',
            'response_type' => 'code'
        ];

        dump($body);

        $data = $this->httpClient->request('POST',$this->apiGateway.'oauth2/token',[
            "body" =>  $body
        ]);


        dump($data);
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