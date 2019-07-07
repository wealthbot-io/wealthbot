<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Repository\AccountRepository;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Model\WealthbotRebalancer\Repository\SecurityRepository;
use Model\WealthbotRebalancer\Repository\SubclassRepository;
use Model\WealthbotRebalancer\Subclass;
use Model\WealthbotRebalancer\SubclassCollection;
use Test\Suit\ExtendedTestCase;

class SubclassRepositoryTest extends ExtendedTestCase
{
    /** @var \Model\WealthbotRebalancer\Portfolio */
    private $portfolio;

    /** @var \Model\WealthbotRebalancer\Repository\SubclassRepository */
    private $repository;

    public function setUp()
    {
        $clientRepo = new ClientRepository();
        $client = $clientRepo->findOneBy(array('email' => 'johnny@wealthbot.io'));

        $portfolioRepo = new PortfolioRepository();
        $this->portfolio = $portfolioRepo->findPortfolioByClient($client);

        $securitiesRepo = new SecurityRepository();
        $portfolioSecurities = $securitiesRepo->findSecuritiesByPortfolio($this->portfolio);

        $this->portfolio->setSecurities($portfolioSecurities);

        $this->repository = new SubclassRepository();
    }

    public function testFindByNameForPortfolio()
    {
        $subclass = $this->repository->findByNameForPortfolio('Short', $this->portfolio);

        $this->assertEquals(6, $subclass->getToleranceBand());
        $this->assertEquals(7, $subclass->getPriority());
        $this->assertNotNull($subclass->getId());

        $subclass = $this->repository->findByNameForPortfolio('not_exist_name', $this->portfolio);

        $this->assertNull($subclass);

    }

    public function testBindAllocations()
    {
        $subclassCollection = $this->repository->bindAllocations($this->portfolio);

        $this->checkCollection($subclassCollection, $this->subclassesData);
    }

    public function testBindAllocationForAccount()
    {
        $accountRepo = new AccountRepository();
        $account = $accountRepo->findOneByAccountNumber('744888386');

        $subclassesData = $this->subclassesData;
        $subclassesData['RWX']['security']['amount'] = 2156.54;
        $subclassesData['RWX']['security']['qty'] = 65;
        $subclassesData['DBC']['muni_security']['qty'] = 0;
        $subclassesData['DBC']['muni_security']['amount'] = 0;

        $subclassCollection = $this->repository->bindAllocations($this->portfolio, $account);

        $this->checkCollection($subclassCollection, $subclassesData);
    }

    private function checkCollection(SubclassCollection $subclassCollection, array $subclassesData)
    {
        $subclass = $subclassCollection->first();

        $this->assertCount(count($subclassesData), $subclassCollection);

        /** @var Subclass $subclass */
        foreach ($subclassesData as $subclassData) {
            $security = $subclass->getSecurity();
            $muniSecurity = $subclass->getMuniSecurity();

            $this->assertEquals($subclassData['security']['symbol'], $security->getSymbol());
            $this->assertEquals($subclassData['target_allocation'], $subclass->getTargetAllocation());
            $this->assertEquals($subclassData['security']['amount'], $security->getAmount());
            $this->assertEquals($subclassData['security']['qty'], $security->getQty());
            $this->assertEquals($subclassData['tolerance_band'], $subclass->getToleranceBand());

            if ($subclassData['muni_security']) {
                $this->assertEquals($subclassData['muni_security']['name'], $muniSecurity->getName());
                $this->assertEquals($subclassData['muni_security']['symbol'], $muniSecurity->getSymbol());
                $this->assertEquals($subclassData['muni_security']['qty'], $muniSecurity->getQty());
                $this->assertEquals($subclassData['muni_security']['amount'], $muniSecurity->getAmount());
            }

            $subclass = $subclassCollection->next();
        }
    }

    private $subclassesData = array(
        'IVV' => array(
            'target_allocation' => 4.8,
            'tolerance_band' => 10,
            'security' => array(
                'symbol' => 'IVV',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => null
        ),
        'VTV' => array(
            'target_allocation' => 7.2,
            'tolerance_band' => 11,
            'security' => array(
                'symbol' => 'VTV',
                'amount' => 0,
                'qty' => 0,
            ),
            'muni_security' => null
        ),
        'IJR' => array(
            'target_allocation' => 4.8,
            'tolerance_band' => 3,
            'security' => array(
                'symbol' => 'IJR',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => array(
                'name' => 'PowerShares DB Commodity Index Tracking',
                'symbol' => 'DBC',
                'amount' => 0,
                'qty' => 0
            )
        ),
        'IJS' => array(
            'target_allocation' => 7.2,
            'tolerance_band' => 2,
            'security' => array(
                'symbol' => 'IJS',
                'amount' => 0,
                'qty' => 0,
            ),
            'muni_security' => array(
                'name' => 'Vanguard Total Bond Market ETF',
                'symbol' => 'BND',
                'amount' => 0,
                'qty' => 0
            )
        ),
        'DBC' => array(
            'target_allocation' => 3,
            'tolerance_band' => 3,
            'security' => array(
                'symbol' => 'DBC',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => array(
                'name' => 'Vanguard Total Stock Market ETF',
                'symbol' => 'VTI',
                'amount' => 1000,
                'qty' => 100
            )
        ),
        'VNQ' => array(
            'target_allocation' => 4.5,
            'tolerance_band' => 11,
            'security' => array(
                'symbol' => 'VNQ',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => null
        ),
        'RWX' => array(
            'target_allocation' => 4.5,
            'tolerance_band' => 10,
            'security' => array(
                'symbol' => 'RWX',
                'amount' => 2547.03,
                'qty' => 75
            ),
            'muni_security' => null
        ),
        'VEA' => array(
            'target_allocation' => 4.5,
            'tolerance_band' => 11,
            'security' => array(
                'symbol' => 'VEA',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => null
        ),
        'EFV' => array(
            'target_allocation' => 5.4,
            'tolerance_band' => 21,
            'security' => array(
                'symbol' => 'EFV',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => null
        ),
        'VSS' => array(
            'target_allocation' => 4.5,
            'tolerance_band' => 11,
            'security' => array(
                'symbol' => 'VSS',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => null
        ),
        'SCZ' => array(
            'target_allocation' => 5.4,
            'tolerance_band' => 3,
            'security' => array(
                'symbol' => 'SCZ',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => null
        ),
        'VWO' => array(
            'target_allocation' => 4.2,
            'tolerance_band' => 5,
            'security' => array(
                'symbol' => 'VWO',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => null
        ),
        'BND' => array(
            'target_allocation' => 40,
            'tolerance_band' => 7,
            'security' => array(
                'symbol' => 'BND',
                'amount' => 0,
                'qty' => 0
            ),
            'muni_security' => null
        )
    );
}

