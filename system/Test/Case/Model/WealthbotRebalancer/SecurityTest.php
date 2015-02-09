<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\AssetClass;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\Subclass;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class SecurityTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Security */
    private $security;

    public function setUp()
    {
        $data = array(
            'id' => 2,
            'name' => 'Vanguard Total Stock Market ETF',
            'symbol' => 'VTI',
            'subclass' => array(
                'id' => 13
            ),
            'assetClass' => array(
                'id' => 15
            ),
            'isPreferredBuy' => true,
            'price' => 20.5,
            'qty' => 10,
            'amount' => 205.3
        );

        $this->security = $this->getMockBuilder('Model\WealthbotRebalancer\Security')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->security->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $this->assertEquals(2, $this->security->getId());
        $this->assertEquals('Vanguard Total Stock Market ETF', $this->security->getName());
        $this->assertEquals('VTI', $this->security->getSymbol());
        $this->assertEquals(13, $this->security->getSubclass()->getId());
        $this->assertEquals(15, $this->security->getAssetClass()->getId());
        $this->assertTrue($this->security->getIsPreferredBuy());
        $this->assertEquals(20.5, $this->security->getPrice());
        $this->assertEquals(10, $this->security->getQty());
        $this->assertEquals(205.3, $this->security->getAmount());
    }

    public function testGetName()
    {
        $this->assertEquals('Vanguard Total Stock Market ETF', $this->security->getName());
    }

    public function testSetName()
    {
        $this->security->setName('Vanguard Value ETF');
        $this->assertEquals('Vanguard Value ETF', $this->security->getName());
    }

    public function testGetSymbol()
    {
        $this->assertEquals('VTI', $this->security->getSymbol());
    }

    public function testSetSymbol()
    {
        $this->security->setSymbol('VTV');
        $this->assertEquals('VTV', $this->security->getSymbol());
    }

    public function testIsTypeCash()
    {
        $this->security->setSymbol(Security::SYMBOL_CASH);
        $this->assertTrue($this->security->isTypeCash());

        $this->security->setSymbol(Security::SYMBOL_IDA12);
        $this->assertTrue($this->security->isTypeCash());

        $this->security->setSymbol('no_cash_type');
        $this->assertFalse($this->security->isTypeCash());
    }

    public function testGetSubclass()
    {
        $this->assertEquals(13, $this->security->getSubclass()->getId());
    }

    public function testSetSubclass()
    {
        $subclass = new Subclass();
        $subclass->setId(14);

        $this->security->setSubclass($subclass);

        $this->assertEquals(14, $this->security->getSubclass()->getId());
    }

    public function testGetAssetClass()
    {
        $this->assertEquals(15, $this->security->getAssetClass()->getId());
    }

    public function testSetAssetClass()
    {
        $assetClass = new AssetClass();
        $assetClass->setId(16);

        $this->security->setAssetClass($assetClass);

        $this->assertEquals(16, $this->security->getAssetClass()->getId());
    }

    public function testGetIsPreferredBuy()
    {
        $this->assertTrue($this->security->getIsPreferredBuy());
    }

    public function testSetIsPreferredBuy()
    {
        $this->security->setIsPreferredBuy(false);
        $this->assertFalse($this->security->getIsPreferredBuy());
    }

    public function testGetPrice()
    {
        $this->assertEquals(20.5, $this->security->getPrice());
    }

    public function testSetPrice()
    {
        $this->security->setPrice(200.7);
        $this->assertEquals(200.7, $this->security->getPrice());
    }

    public function testGetQty()
    {
        $this->assertEquals(10, $this->security->getQty());
    }

    public function testSetQty()
    {
        $this->security->setQty(123);
        $this->assertEquals(123, $this->security->getQty());
    }

    public function testGetAmount()
    {
        $this->assertEquals(205.3, $this->security->getAmount());
    }

    public function testSetAmount()
    {
        $this->security->setAmount(303.5);
        $this->assertEquals(303.5, $this->security->getAmount());
    }

//    public function testCalcAmount()
//    {
//        $this->assertEquals(205, $this->security->calcAmount());
//    }

    public function testBuy()
    {
        $this->security->buy(10, 300.2);
        $this->assertEquals(20, $this->security->getQty());
        $this->assertEquals(505.5, $this->security->getAmount());
    }

    public function testSell()
    {
        $this->security->sell(5, 55);

        $this->assertEquals(5, $this->security->getQty());
        $this->assertEquals(150.3, $this->security->getAmount());
    }

    public function testSellAll()
    {
        $this->security->sellAll();
        $this->assertEquals(0, $this->security->getQty());
        $this->assertEquals(0, $this->security->getAmount());
    }

    public function testIsCanBePurchased()
    {
        $this->assertFalse($this->security->isCanBePurchased(false, false));

        $this->assertFalse($this->security->isCanBePurchased(false, 1));
        $this->assertFalse($this->security->isCanBePurchased(1, false));

        $this->assertFalse($this->security->isCanBePurchased(-1, 1));
        $this->assertFalse($this->security->isCanBePurchased(1, -1));

        $this->assertFalse($this->security->isCanBePurchased(-1, -1));

        $this->assertTrue($this->security->isCanBePurchased(1, 1));
    }

    public function testIsCanBeSold()
    {
        $this->assertFalse($this->security->isCanBeSold(false, false));

        $this->assertFalse($this->security->isCanBeSold(false, 1));
        $this->assertFalse($this->security->isCanBeSold(1, false));

        $this->assertFalse($this->security->isCanBeSold(-1, 1));
        $this->assertFalse($this->security->isCanBeSold(1, -1));

        $this->assertFalse($this->security->isCanBeSold(-1, -1));

        $this->assertFalse($this->security->isCanBeSold(11, 206));
        $this->assertFalse($this->security->isCanBeSold(11, 1));
        $this->assertFalse($this->security->isCanBeSold(1, 206));

        $this->assertTrue($this->security->isCanBeSold(1, 1));
    }
}
