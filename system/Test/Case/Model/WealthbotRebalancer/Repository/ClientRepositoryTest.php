<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Database\WealthbotMySqlConnection;
use Model\WealthbotRebalancer\ArrayCollection;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Repository\BaseRepository;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Model\WealthbotRebalancer\Repository\RebalancerActionRepository;
use Model\WealthbotRebalancer\Ria;
use Test\Suit\ExtendedTestCase;

class ClientRepositoryTest extends ExtendedTestCase
{
    /** @var ClientRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new ClientRepository();
    }

    public function testFindClientsByRia()
    {
        $data = array(
            array(
                'email' => 'client@example.com'
            )
        );

        $sql = "SELECT * FROM " . BaseRepository::TABLE_USER . " WHERE email = :email";

        $connection = WealthbotMySqlConnection::getInstance();
        $pdo = $connection->getPdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array('email' => 'ria@example.com'));
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        $ria = new Ria();
        $ria->setId($result['id']);

        $clients = $this->repository->findClientsByRia($ria);

        $this->assertCount(count($data), $clients);
        foreach ($data as $item) {
            $client = $clients->current();
            $this->assertEquals($item['email'], $client->getEmail());
            $clients->next();
        }
    }

    public function testFindClientByEmail()
    {
        $client = $this->repository->findClientByEmail('client@example.com');
        $this->assertEquals('client@example.com', $client->getEmail());
    }

    public function testGetClientRebalancerAction()
    {
        $rebalancerActionRepo = new RebalancerActionRepository();

        $rebalancerActions = $rebalancerActionRepo->findAll();
        $rebalancerAction = $rebalancerActions->first();

        /** @var Client $client */
        $client = $this->repository->getClientByRebalancerAction($rebalancerAction);

        $this->assertEquals('liu@wealthbot.io', $client->getEmail());
    }

    public function testLoadStopTlhValue()
    {
        $clientRepo = new ClientRepository();

        $clientMiles = $clientRepo->findClientByEmail('johnny@wealthbot.io');
        $this->assertNull($clientMiles->getStopTlhValue());

        $this->repository->loadStopTlhValue($clientMiles);

        $this->assertEquals(4.2, $clientMiles->getStopTlhValue());

        $clientBoshen = $clientRepo->findClientByEmail('liu@wealthbot.io');
        $this->repository->loadStopTlhValue($clientBoshen);

        $this->assertEquals(5.6, $clientBoshen->getStopTlhValue());
    }
}
