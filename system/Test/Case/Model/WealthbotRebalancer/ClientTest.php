<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 13.02.14
 * Time: 18:57
 */

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\AccountCollection;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Ria;
use Model\WealthbotRebalancer\Portfolio;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\Client */
    private $client;

    public function setUp()
    {
        $data = array(
            'id' => 2,
            'email' => 'test@example.com',
            'accountManaged' => Client::ACCOUNT_MANAGED_ACCOUNT,
            'taxBracket' => 0.10,
            'stopTlhValue' => 30000,
            'ria' => array(
                'id' => 5,
                'isActive' => true,
            ),
            'accounts' => array(
                array(
                    'id' => 12
                ),
                array(
                    'id' => 13
                )
            ),
            'portfolio' => array(
                'id' => 88
            ),
            'job' => array(
                'id' => 78
            )
        );

        $this->client = $this->getMockBuilder('Model\WealthbotRebalancer\Client')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->client->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $ria = $this->client->getRia();
        $accounts = $this->client->getAccounts();
        $portfolio = $this->client->getPortfolio();

        $this->assertEquals(2, $this->client->getId());
        $this->assertEquals(Client::ACCOUNT_MANAGED_ACCOUNT, $this->client->getAccountManaged());
        $this->assertEquals(5, $ria->getId());
        $this->assertTrue($ria->getIsActive());
        $this->assertCount(2, $accounts);
        $this->assertEquals(12, $accounts[12]->getId());
        $this->assertEquals(13, $accounts[13]->getId());
        $this->assertEquals(88, $portfolio->getId());
        $this->assertEquals(0.10, $this->client->getTaxBracket());
        $this->assertEquals(30000, $this->client->getStopTlhValue());
    }

    public function testGetAccountManaged()
    {
        $this->assertEquals(Client::ACCOUNT_MANAGED_ACCOUNT, $this->client->getAccountManaged());
    }

    public function testSetAccountManaged()
    {
        $this->client->setAccountManaged(Client::ACCOUNT_MANAGED_HOUSEHOLD);
        $this->assertEquals(Client::ACCOUNT_MANAGED_HOUSEHOLD, $this->client->getAccountManaged());
    }

    public function testGetRia()
    {
        $ria = $this->client->getRia();

        $this->assertEquals(5, $ria->getId());
        $this->assertTrue($ria->getIsActive());
    }

    public function testSetRia()
    {
        $ria = new Ria();
        $ria->setId(9);
        $ria->setIsActive(false);

        $this->client->setRia($ria);

        $this->assertEquals(9, $this->client->getRia()->getId());
        $this->assertFalse($this->client->getRia()->getIsActive());
    }

    public function testGetAccounts()
    {
        $accounts = $this->client->getAccounts();

        $this->assertCount(2, $accounts);
        $this->assertEquals(12, $accounts[12]->getId());
        $this->assertEquals(13, $accounts[13]->getId());
    }

    public function testSetAccounts()
    {
        $account = new Account();
        $account->setId(10);
        $account->setIsFirstTime(true);
        $account->setOneTimeDistribution(1000);

        $accountCollection = new AccountCollection();
        $accountCollection->add($account);

        $this->client->setAccounts($accountCollection);

        $accounts = $this->client->getAccounts();

        $this->assertCount(1, $accounts);
        $this->assertEquals(10, $accounts[10]->getId());
        $this->assertTrue($accounts[10]->getIsFirstTime());
        $this->assertEquals(1000, $accounts[10]->getOneTimeDistribution());
    }

    public function testAddAccount()
    {
        $newAccount = new Account();
        $newAccount->setId(456);

        $this->client->addAccount($newAccount);

        $clientAccounts = $this->client->getAccounts();

        $this->assertCount(3, $clientAccounts);
        $this->assertEquals(12, $clientAccounts->get(12)->getId());
        $this->assertEquals(13, $clientAccounts->get(13)->getId());
        $this->assertEquals(456, $clientAccounts->get(456)->getId());
    }

    public function testGetPortfolio()
    {
        $portfolio = $this->client->getPortfolio();

        $this->assertEquals(88, $portfolio->getId());
    }

    public function testSetPortfolio()
    {
        $portfolio = new Portfolio();
        $portfolio->setId(90);

        $this->client->setPortfolio($portfolio);

        $this->assertEquals(90, $this->client->getPortfolio()->getId());
    }

    public function testGetJob()
    {
        $job = $this->client->getJob();

        $this->assertEquals(78, $job->getId());
    }

    public function testSetJob()
    {
        $newJob = new Job();
        $newJob->setId(1);

        $this->client->setJob($newJob);

        $job = $this->client->getJob();

        $this->assertEquals(1, $job->getId());
    }

    public function testGetEmail()
    {
        $this->assertEquals('test@example.com', $this->client->getEmail());
    }

    public function testSetEmail()
    {
        $this->client->setEmail('client@client.com');
        $this->assertEquals('client@client.com', $this->client->getEmail());
    }

    public function testGetTaxBracket()
    {
        $this->assertEquals(0.10, $this->client->getTaxBracket());
    }

    public function testSetTaxBracket()
    {
        $this->client->setTaxBracket(0.17);
        $this->assertEquals(0.17, $this->client->getTaxBracket());
    }

    public function testGetStopTlhValue()
    {
        $this->assertEquals(30000, $this->client->getStopTlhValue());
    }

    public function testSetStopTlhValue()
    {
        $this->client->setStopTlhValue(25000);
        $this->assertEquals(25000, $this->client->getStopTlhValue());
    }

    public function testCanUseTlh()
    {
        $riaData = array(
            'id' => 12,
            'is_tlh_enabled' => true,
            'client_tax_bracket' => 0.09,
            'min_relationship_value' => 28000
        );

        $portfolioData = array(
            'id' => 1,
            'total_value' => 28500
        );

        $ria = $this->getMock('Model\WealthbotRebalancer\Ria', null);
        $ria->loadFromArray($riaData);
        $this->client->setRia($ria);

        $portfolio = $this->getMock('Model\WealthbotRebalancer\Portfolio', null);
        $portfolio->loadFromArray($portfolioData);
        $this->client->setPortfolio($portfolio);

        $this->assertTrue($this->client->canUseTlh());

        $portfolio->setTotalValue(27000);
        $this->assertFalse($this->client->canUseTlh());

        $portfolio->setTotalValue(28500);
        $ria->setClientTaxBracket(0.13);
        $this->assertFalse($this->client->canUseTlh());

        $ria->setClientTaxBracket(0.09);
        $ria->setMinRelationshipValue(30000);
        $this->assertFalse($this->client->canUseTlh());
    }

    public function testIsHouseholdLevelRebalance()
    {
        $this->assertFalse($this->client->isHouseholdLevelRebalancer());

        $this->client->setAccountManaged(Client::ACCOUNT_MANAGED_HOUSEHOLD);

        $this->assertTrue($this->client->isHouseholdLevelRebalancer());
    }

    public function testIsAccountLevelRebalance()
    {
        $this->assertTrue($this->client->isAccountLevelRebalancer());

        $this->client->setAccountManaged(Client::ACCOUNT_MANAGED_HOUSEHOLD);

        $this->assertFalse($this->client->isAccountLevelRebalancer());
    }

}
