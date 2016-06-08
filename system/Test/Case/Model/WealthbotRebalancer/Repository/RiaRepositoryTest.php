<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Repository\ClientRepository;
use Model\WealthbotRebalancer\Repository\RiaRepository;
use Model\WealthbotRebalancer\Ria;
use Test\Suit\ExtendedTestCase;

class RiaRepositoryTest extends ExtendedTestCase
{
    /** @var RiaRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = new RiaRepository();
    }

    public function testFindOneByClient()
    {
        $clientRepo = new ClientRepository();
        $client = $clientRepo->findClientByEmail('johnny@wealthbot.io');

        $ria = $this->repository->findOneByClient($client);

        $this->assertEquals('raiden@wealthbot.io', $ria->getEmail());
        $this->assertEquals(true, $ria->getIsTlhEnabled());
        $this->assertEquals(0.1, $ria->getClientTaxBracket());
        $this->assertEquals(50000, $ria->getMinRelationshipValue());
        $this->assertEquals(100, $ria->getMinTlh());
        $this->assertEquals(0.1, $ria->getMinTlhPercent());
        $this->assertTrue($ria->getIsUseMunicipalBond());
        $this->assertTrue($ria->getuseTransactionFees());
    }


}
