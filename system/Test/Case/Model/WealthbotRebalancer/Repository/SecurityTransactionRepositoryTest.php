<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\PortfolioRepository;
use Model\WealthbotRebalancer\Repository\SecurityRepository;
use Model\WealthbotRebalancer\Repository\SecurityTransactionRepository;
use Test\Suit\ExtendedTestCase;

class SecurityTransactionRepositoryTest extends ExtendedTestCase
{
    /** @var SecurityTransactionRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new SecurityTransactionRepository();
    }

    public function testFindOneByPortfolioAndSecurity()
    {
        $clientRepo = new ClientRepository();
        $client = $clientRepo->findOneBy(array('email' => 'johnny@wealthbot.io'));

        $portfolioRepository = new PortfolioRepository();
        $portfolio = $portfolioRepository->findPortfolioByClient($client);

        $securityRepo = new SecurityRepository();
        $security = $securityRepo->findOneBySymbol('VTV');

        $securityTransaction = $this->repository->findOneByPortfolioAndSecurity($portfolio, $security);
        $this->assertEquals(66, $securityTransaction->getRedemptionPenaltyInterval());
        $this->assertEquals(77, $securityTransaction->getRedemptionFee());

        $portfolio->setId(0);
        $security->setId(0);
        $securityTransaction = $this->repository->findOneByPortfolioAndSecurity($portfolio, $security);
        $this->assertNull($securityTransaction);
    }
}
