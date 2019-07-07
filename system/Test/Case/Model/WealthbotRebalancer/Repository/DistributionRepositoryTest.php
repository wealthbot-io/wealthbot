<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Database\WealthbotMySqlSqliteConnection;
use Manager\BusinessCalendar;
use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Repository\BaseRepository;
use Model\WealthbotRebalancer\Repository\DistributionRepository;
use Test\Suit\ExtendedTestCase;

class DistributionRepositoryTest extends ExtendedTestCase
{
    /** @var \Model\WealthbotRebalancer\Account */
    private $account;

    /** @var \Model\WealthbotRebalancer\Repository\DistributionRepository */
    private $repository;

    /** @var \Manager\BusinessCalendar */
    private $businessCalendar;

    public function setUp()
    {
        $connection = WealthbotMySqlSqliteConnection::getInstance();

        $sql = "SELECT * FROM " . BaseRepository::TABLE_SYSTEM_ACCOUNT . " ORDER BY id ASC LIMIT 1";
        $result = $connection->query($sql);

        $this->account = new Account();
        $this->account->loadFromArray($result[0]);

        $this->repository = new DistributionRepository();
        $this->businessCalendar = new BusinessCalendar();
    }

    public function testFindScheduledDistribution()
    {
        $dateFrom = new \DateTime('2013-12-20');
        $dateTo = $this->businessCalendar->addBusinessDays($dateFrom, 4);
        $distribution = $this->repository->findScheduledDistribution($this->account, $dateFrom, $dateTo);
        $this->assertEquals(0, $distribution);

        $dateFrom = new \DateTime('2013-12-26');
        $dateTo = $this->businessCalendar->addBusinessDays($dateTo, 4);
        $distribution = $this->repository->findScheduledDistribution($this->account, $dateFrom, $dateTo);
        $this->assertEquals(500, $distribution);
    }

    public function testFindOneTimeDistribution()
    {
        $distribution = $this->repository->findOneTimeDistribution($this->account);
        $this->assertEquals(5275, $distribution);
    }
}
 