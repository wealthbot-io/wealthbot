<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Test\Suit\ExtendedTestCase;

class PortfolioRepositoryTest extends ExtendedTestCase
{
    /** @var PortfolioRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new PortfolioRepository();
    }

    public function testFindPortfolioByClient()
    {
        $clientRepo = new ClientRepository();

        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');

        $portfolio = $this->repository->findPortfolioByClient($client);
        $this->assertNotNull($portfolio->getId());
        $this->assertNotNull($client->getPortfolio()->getId());

        $notExistClient = new Client();
        $notExistClient->setId(0);

        $portfolio = $this->repository->findPortfolioByClient($notExistClient);
        $this->assertNull($notExistClient->getPortfolio());
        $this->assertNull($portfolio);
    }

    public function testLoadPortfolioValues()
    {
        $clientRepo = new ClientRepository();

        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');
        $portfolio = $this->repository->findPortfolioByClient($client);

        $this->repository->loadPortfolioValues($client);

        $this->assertEquals('2451038.0', $portfolio->getTotalValue());
        $this->assertEquals('699098.0', $portfolio->getTotalInSecurities());
        $this->assertEquals('992397.0', $portfolio->getTotalCashInAccounts());
        $this->assertEquals('759543.0', $portfolio->getTotalCashInMoneyMarket());
        $this->assertEquals('0.0', $portfolio->getSasCash());
        $this->assertEquals('0.0', $portfolio->getCashBuffer());
        $this->assertEquals('0.0', $portfolio->getBillingCash());

        $notExistClient = new Client();
        $notExistPortfolio = new Portfolio();
        $notExistPortfolio->setId(0);
        $notExistClient->setPortfolio($notExistPortfolio);

        $this->repository->loadPortfolioValues($notExistClient);

        $this->assertNull($notExistPortfolio->getTotalValue());
        $this->assertNull($notExistPortfolio->getTotalInSecurities());
        $this->assertNull($notExistPortfolio->getTotalCashInAccounts());
        $this->assertNull($notExistPortfolio->getTotalCashInMoneyMarket());
        $this->assertNull($notExistPortfolio->getSasCash());
        $this->assertNull($notExistPortfolio->getCashBuffer());
        $this->assertNull($notExistPortfolio->getBillingCash());

    }
}
