<?php
namespace App\Manager;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Monolog\Logger;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class AmeritradeManager {

    private $httpClient;

    private $em;

    private $logger;

    private $apiGateway;

    private $apiKey;

    private $container;

    /**
     * AmeritradeManager constructor.
     * @param EntityManager $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(EntityManager $entityManager, ContainerInterface $container)
    {

        $this->container = $container;
        $this->httpClient = HttpClient::create();
        $this->em = $entityManager;
        $this->logger;
        $this->apiGateway = "https://api.tdameritrade.com/v1/";}


    /**
     * @throws \Exception
     */
    public function setApiKey(){
        /** @var User $ria */
        $ria = $this->container->get('security.token_storage')->getToken() ? $this->container->get('security.context')->getToken()->getUser()->getRia():null;
        $this->apiKey = $ria->getRiaCompanyInformation()->getAmeritradeKey();
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