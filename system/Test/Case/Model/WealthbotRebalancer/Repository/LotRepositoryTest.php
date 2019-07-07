<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Lot;
use Model\WealthbotRebalancer\LotCollection;
use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\Repository\AccountRepository;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\LotRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Model\WealthbotRebalancer\Repository\SecurityRepository;
use Model\WealthbotRebalancer\Repository\SubclassRepository;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\Subclass;
use Test\Suit\ExtendedTestCase;

class LotRepositoryTest extends ExtendedTestCase
{
    /** @var LotRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new LotRepository();
    }

    public function testFindOrderedLots()
    {
        $securityRepo = new SecurityRepository();
        $accountRepo = new AccountRepository();

        $security = $securityRepo->findOneBySymbol('RWX');
        $account = $accountRepo->findOneByAccountNumber('744888385');

        $lots = $this->repository->findOrderedLots($security, $account);

        $this->assertCount(1, $lots);
    }

    public function testFindLotsBySubclass()
    {
        $subclassRepo = new SubclassRepository();
        $clientRepo = new ClientRepository();
        $portfolioRepo = new PortfolioRepository();
        $securityRepo = new SecurityRepository();
        $accountRepo = new AccountRepository();

        $security = $securityRepo->findOneBySymbol('RWX');
        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');
        $portfolioRepo->findPortfolioByClient($client);

        $subclass = $subclassRepo->findByNameForPortfolio('Short', $client->getPortfolio());
        $subclass->setSecurity($security);

        $lots = $this->repository->findLotsBySubclass($client->getPortfolio(), $subclass);

        $this->assertCount(2, $lots);

        /** @var Lot $lot */
        $lot = $lots->first();

        $this->assertEquals(390.49, $lot->getAmount());
        $this->assertEquals(10, $lot->getQuantity());
        $this->assertTrue($lot->isOpen());
        $this->assertFalse($lot->getIsMuni());

        $lot = $lots->next();

        $this->assertEquals(2156.54, $lot->getAmount());
        $this->assertEquals(65, $lot->getQuantity());
        $this->assertTrue($lot->isOpen());
        $this->assertFalse($lot->getIsMuni());
        //---------------------------------------------------------------------------------//

        $account = $accountRepo->findOneByAccountNumber('744888386');
        $lots = $this->repository->findLotsBySubclass($client->getPortfolio(), $subclass, $account);

        $this->assertCount(1, $lots);

        $lot = $lots->first();

        $this->assertEquals(2156.54, $lot->getAmount());
        $this->assertEquals(65, $lot->getQuantity());
        $this->assertTrue($lot->isOpen());
        $this->assertFalse($lot->getIsMuni());

        //---------------------------------------------------------------------------------//

        $security = $securityRepo->findOneBySymbol('VTI');
        $subclass->setSecurity($security);

        $lots = $this->repository->findLotsBySubclass($client->getPortfolio(), $subclass);

        $this->assertCount(1, $lots);

        $lot = $lots->first();
        $this->assertEquals(1000, $lot->getAmount());
        $this->assertEquals(100, $lot->getQuantity());
        $this->assertTrue($lot->isInitial());
        $this->assertTrue($lot->getIsMuni());

    }

    public function testGetInitialLot()
    {
        $initialLot = $this->repository->findOneBy(array('status' => Lot::LOT_INITIAL), array('id' => 'DESC'));
        $this->assertEquals($initialLot, $this->repository->getInitialLot($initialLot));

        $lot = $this->repository->findOneBy(array('status' => Lot::LOT_CLOSED), array('id' => 'DESC'));
        $initialLot = $this->repository->getInitialLot($lot);

        $this->assertEquals($lot->getInitialLotId(), $initialLot->getId());
    }

//    public function testFindLastLotByAccountAndSecurity()
//    {
//        $accountRepo = new AccountRepository();
//        $securityRepo = new SecurityRepository();
//        $clientRepo = new ClientRepository();
//        $portfolioRepo = new PortfolioRepository();
//
//        $client = $clientRepo->findClientByEmail('miles@wealthbot.io');
//        $portfolioRepo->findPortfolioByClient($client);
//
//        $security = $securityRepo->findOneBySymbol('RWX');
//        $account = $accountRepo->findOneByAccountNumber('744888386');
//
//        $account->setClient($client);
//
//        $lot = $this->repository->findLastLotByAccountAndSecurity($account, $security);
//
//        $this->assertEquals(65, $lot->getQuantity());
//        $this->assertEquals(2156.54, $lot->getAmount());
//        $this->assertEquals(Lot::LOT_IS_OPEN, $lot->getStatus());
//        $this->assertEquals(0, $lot->getRealizedGainOrLoss());
//    }

    public function testFindLotsByAccountAndSecurity()
    {
        $accountRepo = new AccountRepository();
        $securityRepo = new SecurityRepository();
        $clientRepo = new ClientRepository();
        $portfolioRepo = new PortfolioRepository();

        $security = $securityRepo->findOneBySymbol('RWX');
        $account = $accountRepo->findOneByAccountNumber('744888386');

        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');
        $portfolioRepo->findPortfolioByClient($client);

        $account->setClient($client);

        $lots = $this->repository->findLotsByAccountAndSecurity($account, $security);

        $this->assertCount(1, $lots);

        /** @var Lot $lot */
        $lot = $lots->first();

        $this->assertEquals(2156.54, $lot->getAmount());
        $this->assertEquals(65, $lot->getQuantity());
        $this->assertTrue($lot->isOpen());
        $this->assertFalse($lot->getIsMuni());

        $security = $securityRepo->findOneBySymbol('BND');
        $lots = $this->repository->findLotsByAccountAndSecurity($account, $security);

        $this->assertTrue($lots->isEmpty());

        $account = $accountRepo->findOneByAccountNumber('214888609');
        $security = $securityRepo->findOneBySymbol('VTI');
        $account->setClient($client);

        $lots = $this->repository->findLotsByAccountAndSecurity($account, $security);

        $this->assertCount(1, $lots);

        $lot = $lots->first();
        $this->assertEquals(1000, $lot->getAmount());
        $this->assertEquals(100, $lot->getQuantity());
        $this->assertTrue($lot->isInitial());
        $this->assertTrue($lot->getIsMuni());
    }

    public function testClientGainsOrLossesSumForYear()
    {
        $clientRepo = new ClientRepository();
        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');



        $this->assertEquals(-36.22, $this->repository->getClientLossesSumForYear($client, '2013'));
        $this->assertEquals(-161.41, $this->repository->getClientLossesSumForYear($client, '2014'));
    }

    public function testLotOrderForMuni()
    {
        $positions = array(
            array(
                'client_system_account_id' => 222,
                'muni_id' => 78,
                'security_id' => 22222
            ),

            array(
                'client_system_account_id' => 333,
                'muni_id' => null,
                'security_id' => 33333
            )
        );

        /** @var LotRepository $mockRepository */
        $mockRepository = $this->getMockBuilder('Model\WealthbotRebalancer\Repository\LotRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('getPositionsByPortfolio', 'getLastPositionLots'))
            ->getMock();

        $mockRepository->expects($this->any())
            ->method('getPositionsByPortfolio')
            ->will($this->returnValue($positions));

        $mockRepository->expects($this->any())
            ->method('getLastPositionLots')
            ->will($this->returnCallback(function(Portfolio $portfolio, $securityId, $clientSystemAccountId, $isMuni = false) {
                $lotCollection = new LotCollection();

                $lot = new Lot();
                $lot->setIsMuni($isMuni);

                $lotCollection->add($lot);

                return $lotCollection;
            }));

        $mockRepository->expects($this->any())
            ->method('findLotsByAccountAndSecurity')
            ->will($this->returnCallback(function(Portfolio $portfolio, $securityId, $clientSystemAccountId, $isMuni = false) {
                $lotCollection = new LotCollection();

                $lot = new Lot();
                $lot->setIsMuni($isMuni);

                $lotCollection->add($lot);

                return $lotCollection;
            }));


        $lotCollection = $mockRepository->findLotsBySubclass(new Portfolio(), new Subclass(), new Account());

        $lot1 = $lotCollection->first();
        $this->assertFalse($lot1->getIsMuni());

        $lot2 = $lotCollection->next();
        $this->assertTrue($lot2->getIsMuni());

        $lot3 = $lotCollection->next();
        $this->assertFalse($lot3->getIsMuni());

        //-------------------------------------------------------------------------------------------------------------/
        $account = new Account();
        $client = new Client();
        $client->setPortfolio(new Portfolio());
        $account->setClient($client);

        $lotCollection = $mockRepository->findLotsByAccountAndSecurity($account, new Security());

        $lot1 = $lotCollection->first();
        $this->assertFalse($lot1->getIsMuni());

        $lot2 = $lotCollection->next();
        $this->assertTrue($lot2->getIsMuni());

        $lot3 = $lotCollection->next();
        $this->assertFalse($lot3->getIsMuni());
    }
}
 