<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Database\WealthbotMySqlSqliteConnection;
use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\AccountCollection;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\RebalancerAction;
use Model\WealthbotRebalancer\Repository\AccountRepository;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\RebalancerActionRepository;
use Test\Suit\ExtendedTestCase;

class AccountRepositoryTest extends ExtendedTestCase
{
    /** @var AccountRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new AccountRepository();
    }


    public function testFindClientAccounts()
    {
        $clientRepo = new ClientRepository();
        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');

        $accounts = $this->repository->findClientAccounts($client);

        $this->assertCount(4, $accounts);
        foreach ($accounts as $account) {
            $this->assertEquals(0, $account->getBillingCash());
        }
    }

    public function testLoadAccountValues()
    {
        $account = $this->repository->findOneByAccountNumber('744888385');

        $this->repository->loadAccountValues($account);
        $this->assertEquals('12628,80', $account->getTotalCash());
        $this->assertEquals(2000, $account->getSasCash());
        $this->assertEquals(3750, $account->getBillingCash());
        $this->assertEquals(1500, $account->getCashBuffer());
    }

    public function testFindOneByAccountNumber()
    {
        $account = $this->repository->findOneByAccountNumber('744888385');
        $this->assertEquals(Account::STATUS_ACTIVE, $account->getStatus());
    }

    public function testGetAccountByRebalancerActionHousehold()
    {
        /** @var AccountRepository $repository */
        $repository = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\AccountRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findClientAccounts', 'findAccountById'))
            ->getMock();

        $client = new Client();
        $client->setId(45);
        $client->setAccountManaged(Client::ACCOUNT_MANAGED_HOUSEHOLD);

        $rebalancerAction = new RebalancerAction();
        $rebalancerAction->setClient($client);

        $repository->expects($this->once())->method('findClientAccounts');

        $repository->getAccountsByRebalancerAction($rebalancerAction);
    }

    public function testGetAccountByRebalancerActionAccount()
    {
        /** @var AccountRepository $repository */
        $repository = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\AccountRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findClientAccounts', 'findAccountById'))
            ->getMock();


        $client = new Client();
        $client->setId(45);
        $client->setAccountManaged(Client::ACCOUNT_MANAGED_ACCOUNT);

        $rebalancerAction = new RebalancerAction();
        $rebalancerAction->setClient($client);

        $mockAccount = $this->getMock('Model\WealthbotRebalancer\Account');

        $repository->expects($this->once())->method('findAccountById')->will($this->returnValue($mockAccount));

        $repository->getAccountsByRebalancerAction($rebalancerAction);
    }

    public function testFindAccountById()
    {
        $clientRepo = new ClientRepository();
        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');

        $accounts = $this->repository->findClientAccounts($client);

        foreach ($accounts as $account) {
            $foundedAccount = $this->repository->findAccountById($account->getId());

            $this->assertEquals($account->getId(), $foundedAccount->getId());
        }

        $this->assertNull($this->repository->findAccountById('incorrect_id'));
    }

    public function testGetAccountsByRebalancerActionData()
    {
        $rebalancerActionRepo = new RebalancerActionRepository();

        $rebalancerActions = $rebalancerActionRepo->findAll();
        /** @var RebalancerAction $rebalancerAction */
        $rebalancerAction = $rebalancerActions->first();

        $sql = "SELECT u.*, up.client_account_managed as account_managed from rebalancer_actions ra
                  LEFT JOIN client_portfolio_values cpv ON cpv.id = ra.client_portfolio_value_id
                  LEFT JOIN client_portfolio cp ON cp.id = cpv.client_portfolio_id
                  LEFT JOIN users u ON u.id = cp.client_id
                  LEFT JOIN user_profiles up ON up.user_id = u.id
                  WHERE ra.id = :rebalancerActionId
        ";

        $parameters = array(
            'rebalancerActionId' => $rebalancerAction->getId()
        );

        $connection = WealthbotMySqlSqliteConnection::getInstance();
        $pdo = $connection->getPdo();
        $statement = $pdo->prepare($sql);
        $statement->execute($parameters);
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        $client = new Client();
        $client->setId($result['id']);

        $accounts = $this->repository->findClientAccounts($client);

        $rebalancerAction->setClient($client);

        $client->setAccountManaged(Client::ACCOUNT_MANAGED_HOUSEHOLD);
        $collection = $this->repository->getAccountsByRebalancerAction($rebalancerAction);

        $this->assertCount(4, $collection);
        $rebalancerAction->setAccountId($accounts->first()->getId());

        $client->setAccountManaged(Client::ACCOUNT_MANAGED_ACCOUNT);
        $collection = $this->repository->getAccountsByRebalancerAction($rebalancerAction);

        $account = $collection->first();

        $this->assertCount(1, $collection);
        $this->assertEquals($rebalancerAction->getAccountId(), $account->getId());
        $this->assertEquals($client->getId(), $account->getClient()->getId());
    }


//    public function getAccountsByRebalancerAction(RebalancerAction $rebalancerAction)
//    {
//        $client = $rebalancerAction->getClient();
//
//        if ($client->isHouseholdLevelRebalancer()) {
//            $collection = $this->findClientAccounts($client);
//        } else {
//            $account = $this->findAccountById($rebalancerAction->getAccountId());
//            $account->setClient($client);
//
//            $collection = new AccountCollection();
//            $collection->add($account);
//        }
//
//        return $collection;
//    }
}
