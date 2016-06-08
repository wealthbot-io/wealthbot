<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Repository\AccountRepository;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Model\WealthbotRebalancer\Repository\SecurityRepository;
use Model\WealthbotRebalancer\Security;
use Test\Suit\ExtendedTestCase;

class SecurityRepositoryTest extends ExtendedTestCase
{
    /** @var SecurityRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new SecurityRepository();
    }

    public function testFindOneBySymbol()
    {
        $security = $this->repository->findOneBySymbol('VTI');

        $this->assertNotNull($security->getId());
        $this->assertEquals('VTI', $security->getSymbol());
        $this->assertEquals('Vanguard Total Stock Market ETF', $security->getName());
    }

    public function testFindSecuritiesByAccount()
    {
        $clientRepo = new ClientRepository();
        $client = $clientRepo->findOneBy(array('email' =>'johnny@wealthbot.io'));

        $accountRepo = new AccountRepository();
        $account = $accountRepo->findOneByAccountNumber('744888385');
        $account->setClient($client);

        $securities = $this->repository->findSecuritiesByAccount($account);

        $this->assertCount(1, $securities);

        /** @var Security $security */
        $security = $securities->first();

        $this->assertEquals('SPDR Dow Jones Intl Real Estate', $security->getName());
        $this->assertEquals('RWX', $security->getSymbol());
        $this->assertEquals(0, $security->getIsPreferredBuy());
        $this->assertEquals(33.41, $security->getPrice());
        $this->assertEquals(10, $security->getQty());
    }

    public function testFindSecuritiesByPortfolio()
    {
        $clientRepo = new ClientRepository();
        $client = $clientRepo->findOneBy(array('email' => 'johnny@wealthbot.io'));

        $portfolioRepo = new PortfolioRepository();
        $portfolio = $portfolioRepo->findPortfolioByClient($client);

        $securities = $this->repository->findSecuritiesByPortfolio($portfolio);

        $this->assertCount(13, $securities);

        foreach ($this->securitiesdData as $securityData) {
            /** @var Security $security */
            $security = $securities->current();

            $this->assertEquals($securityData['name'], $security->getName());
            $this->assertEquals($securityData['symbol'], $security->getSymbol());
            $this->assertEquals($securityData['price'], $security->getPrice());

            $securities->next();
        }

    }

    private $securitiesdData = array(
        array(
            'name' => 'iShares S&P 500 Index',
            'symbol' => 'IVV',
            'price' => 72.6
        ),
        array(
            'name' => 'Vanguard Value ETF',
            'symbol' => 'VTV',
            'price' => 30.12
        ),
        array(
            'name' => 'iShares S&P SmallCap 600 Index Fund',
            'symbol' => 'IJR',
            'price' => 39.77
        ),
        array(
            'name' => 'iShares S&P SmallCap 600 Value Index',
            'symbol' => 'IJS',
            'price' => 62.73
        ),
        array(
            'name' => 'Vanguard Europe Pacific ETF',
            'symbol' => 'VEA',
            'price' => 41.22
        ),
        array(
            'name' => 'iShares MSCI EAFE Value Index',
            'symbol' => 'EFV',
            'price' => 46.02
        ),
        array(
            'name' => 'Vanguard FTSE All-Wld ex-US SmCp Idx ETF',
            'symbol' => 'VSS',
            'price' => 73.04
        ),
        array(
            'name' => 'iShares MSCI EAFE Small Cap Index',
            'symbol' => 'SCZ',
            'price' => 32.04
        ),
        array(
            'name' => 'Vanguard Emerging Markets Stock ETF',
            'symbol' => 'VWO',
            'price' => 50.18
        ),
        array(
            'name' => 'PowerShares DB Commodity Index Tracking',
            'symbol' => 'DBC',
            'price' => 64.82
        ),
        array(
            'name' => 'Vanguard REIT Index ETF',
            'symbol' => 'VNQ',
            'price' => 65.3
        ),
        array(
            'name' => 'SPDR Dow Jones Intl Real Estate',
            'symbol' => 'RWX',
            'price' => 33.41
        ),
        array(
            'name' => 'Vanguard Total Bond Market ETF',
            'symbol' => 'BND',
            'price' => 132.8
        )
    );

}
