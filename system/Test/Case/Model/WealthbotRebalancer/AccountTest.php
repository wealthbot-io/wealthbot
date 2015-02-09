<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\SecurityCollection;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class AccountTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\Account */
    private $account;

    public function setUp()
    {
        $data = array(
            'id' => 1,
            'type' => Account::TYPE_PERSONAL_INVESTMENT,
            'status' => Account::STATUS_INIT_REBALANCE,
            'isFirstTime' => true,
            'isActiveEmployer' => true,
            'isReadyToRebalance' => true,
            'scheduledDistribution' => 15000,
            'oneTimeDistribution' => 3450,
            'cashBuffer' => 2300,
            'sasCash' => 3000,
            'billingCash' => 3400,
            'totalCash' => 30000,
            'cashForBuy' => 200,
            'client' => array(
                'id' => 28
            ),
            'securities' => array(
                array('id' => 1),
                array('id' => 2)
            )
        );

        $this->account = $this->getMockBuilder('Model\WealthbotRebalancer\Account')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->account->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $securities = $this->account->getSecurities();

        $this->assertEquals(1, $this->account->getId());
        $this->assertEquals(Account::TYPE_PERSONAL_INVESTMENT, $this->account->getType());
        $this->assertTrue($this->account->isTaxable());
        $this->assertEquals(Account::STATUS_INIT_REBALANCE, $this->account->getStatus());
        $this->assertTrue($this->account->getIsFirstTime());
        $this->assertTrue($this->account->getIsActiveEmployer());
        $this->assertTrue($this->account->getIsReadyToRebalance());
        $this->assertEquals(15000, $this->account->getScheduledDistribution());
        $this->assertEquals(3450, $this->account->getOneTimeDistribution());
        $this->assertEquals(2300, $this->account->getCashBuffer());
        $this->assertEquals(3000, $this->account->getSasCash());
        $this->assertEquals(3400, $this->account->getBillingCash());
        $this->assertEquals(30000, $this->account->getTotalCash());
        $this->assertEquals(28, $this->account->getClient()->getId());

        $this->assertCount(2, $securities);
        $this->assertEquals(1, $securities[1]->getId());
        $this->assertEquals(2, $securities[2]->getId());
    }

    public function testGetType()
    {
        $this->assertEquals(Account::TYPE_PERSONAL_INVESTMENT, $this->account->getType());
        $this->assertTrue($this->account->isTaxable());
    }

    public function testSetType()
    {
        $this->account->setType(Account::TYPE_TRADITIONAL_IRA);
        $this->assertEquals(Account::TYPE_TRADITIONAL_IRA, $this->account->getType());
        $this->assertFalse($this->account->isTaxable());
        $this->assertTrue($this->account->isTraditionalIra());
    }

    public function testIsRothIra()
    {
        $this->assertFalse($this->account->isRothIra());

        $this->account->setType(Account::TYPE_ROTH_IRA);
        $this->assertTrue($this->account->isRothIra());
    }

    public function testIsTraditionalIra()
    {
        $this->assertFalse($this->account->isTraditionalIra());

        $this->account->setType(Account::TYPE_TRADITIONAL_IRA);
        $this->assertTrue($this->account->isTraditionalIra());
    }

    public function testIsTaxable()
    {
        $this->assertTrue($this->account->isTaxable());

        $this->account->setType(Account::TYPE_JOINT_INVESTMENT);
        $this->assertTrue($this->account->isTaxable());

        $this->account->setType(Account::TYPE_ROTH_IRA);
        $this->assertFalse($this->account->isTaxable());
    }

    public function testGetPriority()
    {
        $this->assertEquals(Account::PRIORITY_TAXABLE, $this->account->getPriority());

        $this->account->setType(Account::TYPE_JOINT_INVESTMENT);
        $this->assertEquals(Account::PRIORITY_TAXABLE, $this->account->getPriority());

        $this->account->setType(Account::TYPE_ROTH_IRA);
        $this->assertEquals(Account::PRIORITY_ROTH_IRA, $this->account->getPriority());

        $this->account->setType(Account::TYPE_TRADITIONAL_IRA);
        $this->assertEquals(Account::PRIORITY_TRADITIONAL_IRA, $this->account->getPriority());
    }

    public function testGetPriorityException()
    {
        $this->setExpectedException('Exception', 'Invalid account type: 5.');

        $this->account->setType(5);
        $this->account->getPriority();
    }

    public function testGetStatus()
    {
        $this->assertEquals(Account::STATUS_INIT_REBALANCE, $this->account->getStatus());
    }

    public function testSetStatus()
    {
        $this->account->setStatus(Account::STATUS_REBALANCED);
        $this->assertEquals(Account::STATUS_REBALANCED, $this->account->getStatus());
    }

    public function testGetIsFirstTime()
    {
        $this->assertTrue($this->account->getIsFirstTime());
    }

    public function testSetIsFirstTime()
    {
        $this->account->setIsFirstTime(false);
        $this->assertFalse($this->account->getIsFirstTime());
    }

    public function testGetIsActiveEmployer()
    {
        $this->assertTrue($this->account->getIsActiveEmployer());
    }

    public function testSetIsActiveEmployer()
    {
        $this->account->setIsActiveEmployer(false);
        $this->assertFalse($this->account->getIsActiveEmployer());
    }

    public function testGetIsReadyToRebalance()
    {
        $this->assertTrue($this->account->getIsReadyToRebalance());
    }

    public function testSetIsReadyToRebalance()
    {
        $this->account->setIsReadyToRebalance(true);
        $this->assertTrue($this->account->getIsReadyToRebalance());
    }

    public function testGetSheduledDistribution()
    {
        $this->assertEquals(15000, $this->account->getScheduledDistribution());
    }

    public function testSetSheduledDistribution()
    {
        $this->account->setScheduledDistribution(13200);
        $this->assertEquals(13200, $this->account->getScheduledDistribution());
    }

    public function testGetOneTimeDistribution()
    {
        $this->assertEquals(3450, $this->account->getOneTimeDistribution());
    }

    public function testSetOneTimeDistribution()
    {
        $this->account->setOneTimeDistribution(152);
        $this->assertEquals(152, $this->account->getOneTimeDistribution());
    }

    public function testGetCashBuffer()
    {
        $this->assertEquals(2300, $this->account->getCashBuffer());
    }

    public function testSetCashBuffer()
    {
        $this->account->setCashBuffer(1650);
        $this->assertEquals(1650, $this->account->getCashBuffer());
    }

    public function testGetSasCash()
    {
        $this->assertEquals(3000, $this->account->getSasCash());
    }

    public function testSetSasCash()
    {
        $this->account->setSasCash(6500);
        $this->assertEquals(6500, $this->account->getSasCash());
    }

    public function testGetBillingCash()
    {
        $this->assertEquals(3400, $this->account->getBillingCash());
    }

    public function testSetBillingCash()
    {
        $this->account->setBillingCash(1522);
        $this->assertEquals(1522, $this->account->getBillingCash());
    }

    public function testGetTotalCash()
    {
        $this->assertEquals(30000, $this->account->getTotalCash());
    }

    public function testSetTotalCash()
    {
        $this->account->setTotalCash(8000);
        $this->assertEquals(8000, $this->account->getTotalCash());
    }

    public function testGetClient()
    {
        $this->assertEquals(28, $this->account->getClient()->getId());
    }

    public function testSetClient()
    {
        $newClient = new Client();
        $newClient->setId(29);

        $this->account->setClient($newClient);
        $this->assertEquals(29, $this->account->getClient()->getId());
    }

    public function testGetCashForBuy()
    {
        $this->assertEquals(200, $this->account->getCashForBuy());
    }

    public function testSetCashForBuy()
    {
        $this->account->setCashForBuy(300);
        $this->assertEquals(300, $this->account->getCashForBuy());
    }

    public function testUpdateCashForBuy()
    {
        $this->account->updateCashForBuy();
        $this->assertEquals(2850, $this->account->getCashForBuy());
    }

    public function testGetSecurities()
    {
        $securities = $this->account->getSecurities();

        $this->assertCount(2 , $securities);
        $this->assertEquals(1, $securities[1]->getId());
        $this->assertEquals(2, $securities[2]->getId());
    }

    public function testSetSecurities()
    {
        $security1 = new Security();
        $security1->setId(12);

        $securityCollection = new SecurityCollection();
        $securityCollection->add($security1);

        $this->account->setSecurities($securityCollection);

        $this->assertCount(1, $securityCollection);
        $this->assertEquals(12, $securityCollection[12]->getId());
    }

    public function testCalculateDistribution()
    {
        $this->assertEquals(18450, $this->account->calculateDistribution());
    }

    public function testCalculateCashNeeds()
    {
        $this->assertEquals(27150, $this->account->calculateCashNeeds());
    }

    public function testGetTotalCashNeeds()
    {
        $this->assertEquals(0, $this->account->getTotalCashNeeds());

        $this->account->setTotalCash(7000);
        $this->assertEquals(20150, $this->account->getTotalCashNeeds());
    }

    public function testCalculateInvestmentCash()
    {
        $this->assertEquals(2850, $this->account->calculateInvestmentCash());
    }

    public function testSellAllSecurities()
    {
        $security1 = new Security();
        $security1->setId(15);
        $security1->setQty(200);

        $security2 = new Security();
        $security2->setId(20);
        $security2->setQty(400);

        $securityCollection = new SecurityCollection();
        $securityCollection->add($security1);
        $securityCollection->add($security2);

        $this->account->setSecurities($securityCollection);

        $this->account->sellAllSecurities();

        $qty = 0;
        foreach ($this->account->getSecurities() as $security) {
            $qty += $security->getQty();
        }

        $this->assertEquals(0, $qty);
    }


    public function testHasPreferredBuySecurity()
    {
        $account = $this->account;
        $securities = $account->getSecurities();

        $securities[1]->setIsPreferredBuy(true);
        $securities[2]->setIsPreferredBuy(true);
        $this->assertTrue($account->hasPreferredBuySecurities());

        $securities[1]->setIsPreferredBuy(false);
        $securities[2]->setIsPreferredBuy(true);
        $this->assertTrue($account->hasPreferredBuySecurities());

        $securities[1]->setIsPreferredBuy(false);
        $securities[2]->setIsPreferredBuy(false);
        $this->assertFalse($account->hasPreferredBuySecurities());
    }

    public function testFindNoPreferredBuySecurities()
    {
        $account = $this->account;
        $securities = $account->getSecurities();

        $securities[1]->setIsPreferredBuy(true);
        $securities[2]->setIsPreferredBuy(true);

        $securityCollection = $account->findNoPreferredBuySecurities();
        $this->assertEquals(0, $securityCollection->count());

        $securities[1]->setIsPreferredBuy(false);
        $securities[2]->setIsPreferredBuy(true);

        $securityCollection = $account->findNoPreferredBuySecurities();
        $this->assertEquals(1, $securityCollection->count());

        $securities[1]->setIsPreferredBuy(false);
        $securities[2]->setIsPreferredBuy(false);

        $securityCollection = $account->findNoPreferredBuySecurities();
        $this->assertEquals(2, $securityCollection->count());
    }

    public function testIsAllSecuritiesEqualCash()
    {
        $this->assertFalse($this->account->isAllSecuritiesEqualCash());

        $security = $this->account->getSecurities()->first();
        $security->setSymbol(Security::SYMBOL_IDA12);

        $this->assertFalse($this->account->isAllSecuritiesEqualCash());

        foreach ($this->account->getSecurities() as $security) {
            $security->setSymbol(Security::SYMBOL_IDA12);
        }

        $this->assertTrue($this->account->isAllSecuritiesEqualCash());

        $this->account->getSecurities()->clear();
        $this->assertFalse($this->account->isAllSecuritiesEqualCash());
    }


}
 