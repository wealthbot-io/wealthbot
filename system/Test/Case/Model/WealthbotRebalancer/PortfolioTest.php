<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\SecurityCollection;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class PortfolioTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\Portfolio */
    private $portfolio;

    public function setUp()
    {
        $data = array(
            'id' => 10,
            'securities' => array(
                array('id' => 3),
                array('id' => 4)
            ),
            'total_value' => 11.1,
            'total_in_securities' => 12.2,
            'total_cash_inAccounts' => 13.3,
            'total_cash_in_money_market' => 14.4,
            'sas_cash' => 15.5,
            'cash_buffer' => 16.6,
            'billing_cash' => 17.7
        );

        $this->portfolio = $this->getMockBuilder('Model\WealthbotRebalancer\Portfolio')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->portfolio->loadFromArray($data);
    }

    public function testGetSecurities()
    {
        $securities = $this->portfolio->getSecurities();

        $this->assertCount(2, $securities);
        $this->assertEquals(3, $securities->get(3)->getId());
        $this->assertEquals(4, $securities->get(4)->getId());
    }

    public function testSetSecurities()
    {
        $securityCollection = new SecurityCollection();

        $security = new Security();
        $security->setId(96);

        $securityCollection->add($security);
        $this->portfolio->setSecurities($securityCollection);

        $securities = $this->portfolio->getSecurities();
        $this->assertCount(1, $securities);
        $this->assertEquals(96, $securities->get(96)->getId());
    }

    public function testGetTotalValue()
    {
        $this->assertEquals(11.1, $this->portfolio->getTotalValue());
    }

    public function testSetTotalValue()
    {
        $this->portfolio->setTotalValue(21.1);

        $this->assertEquals(21.1, $this->portfolio->getTotalValue());
    }

    public function testGetTotalInSecurities()
    {
        $this->assertEquals(12.2, $this->portfolio->getTotalInSecurities());
    }

    public function testSetTotalInSecurities()
    {
        $this->portfolio->setTotalInSecurities(22.2);

        $this->assertEquals(22.2, $this->portfolio->getTotalInSecurities());
    }

    public function testGetTotalCashInAccounts()
    {
        $this->assertEquals(13.3, $this->portfolio->getTotalCashInAccounts());
    }

    public function testSetTotalCashInAccounts()
    {
        $this->portfolio->setTotalCashInAccounts(23.3);

        $this->assertEquals(23.3, $this->portfolio->getTotalCashInAccounts());
    }

    public function testGetTotalCashInMoneyMarket()
    {
        $this->assertEquals(14.4, $this->portfolio->getTotalCashInMoneyMarket());
    }

    public function testSetTotalCashInMoneyMarket()
    {
        $this->portfolio->setTotalCashInMoneyMarket(24.4);

        $this->assertEquals(24.4, $this->portfolio->getTotalCashInMoneyMarket());
    }

    public function testGetSasCash()
    {
        $this->assertEquals(15.5, $this->portfolio->getSasCash());
    }

    public function testSetSasCash()
    {
        $this->portfolio->setSasCash(25.5);

        $this->assertEquals(25.5, $this->portfolio->getSasCash());
    }

    public function testGetCashBuffer()
    {
        $this->assertEquals(16.6, $this->portfolio->getCashBuffer());
    }

    public function testSetCashBuffer()
    {
        $this->portfolio->setCashBuffer(26.6);

        $this->assertEquals(26.6, $this->portfolio->getCashBuffer());
    }

    public function testGetBillingCash()
    {
        $this->assertEquals(17.7, $this->portfolio->getBillingCash());
    }

    public function testSetBillingCash()
    {
        $this->portfolio->setBillingCash(27.7);

        $this->assertEquals(27.7, $this->portfolio->getBillingCash());
    }
}