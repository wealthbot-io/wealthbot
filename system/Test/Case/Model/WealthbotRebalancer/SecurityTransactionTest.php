<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\SecurityTransaction;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class SecurityTransactionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  SecurityTransaction */
    private $securityTransaction;

    public function setUp()
    {
        $data = array(
            'id' => 2,
            'redemption_penalty_interval' => 12.5,
            'redemption_fee' => 11.1,
            'minimum_buy' => 2000,
            'minimum_sell' => 1000,
            'init_min_buy_amount' => 200
        );

        $this->securityTransaction = $this->getMockBuilder('Model\WealthbotRebalancer\SecurityTransaction')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->securityTransaction->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $this->assertEquals(2, $this->securityTransaction->getId());
        $this->assertEquals(12.5, $this->securityTransaction->getRedemptionPenaltyInterval());
    }

    public function testGetRedemptionPenaltyInterval()
    {
        $this->assertEquals(12.5, $this->securityTransaction->getRedemptionPenaltyInterval());
    }

    public function testSetRedemptionPenaltyInterval()
    {
        $this->securityTransaction->setRedemptionPenaltyInterval(45.6);

        $this->assertEquals(45.6, $this->securityTransaction->getRedemptionPenaltyInterval());
    }

    public function testGetRedemptionFee()
    {
        $this->assertEquals(11.1, $this->securityTransaction->getRedemptionFee());
    }

    public function testSetRedemptionFee()
    {
        $this->securityTransaction->setRedemptionFee(21.1);

        $this->assertEquals(21.1, $this->securityTransaction->getRedemptionFee());
    }

    public function testIsRedemptionFeeSpecified()
    {
        $this->assertTrue($this->securityTransaction->isRedemptionFeeSpecified());

        $this->securityTransaction->setRedemptionFee(-21.1);
        $this->assertFalse($this->securityTransaction->isRedemptionFeeSpecified());

        $this->securityTransaction->setRedemptionFee(0);
        $this->assertFalse($this->securityTransaction->isRedemptionFeeSpecified());
    }

    public function testMinimumBuy()
    {
        $this->assertEquals(2000, $this->securityTransaction->getMinimumBuy());
    }

    public function testMinimumSell()
    {
        $this->assertEquals(1000, $this->securityTransaction->getMinimumSell());
    }

    public function testSetInitMinBuyAmount()
    {
        $this->assertEquals(200, $this->securityTransaction->getInitMinBuyAmount());
        $this->securityTransaction->setInitMinBuyAmount(5000);
        $this->assertEquals(5000, $this->securityTransaction->getInitMinBuyAmount());
    }
}