<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\TradeData;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class TradeDataTest extends \PHPUnit_Framework_TestCase
{
    /** @var TradeData */
    private $tradeData;

    private $vspsData = array(
        array(
            'purchase' => 'VSP',
            'purchase_date' => '02132013',
            'quantity' => 23
        ),
        array(
            'purchase' => 'VSP',
            'purchase_date' => '02162013',
            'quantity' => 20
        )
    );

    public function setUp()
    {
        $data = array(
            'id' => 5,
            'jobId' => 35,
            'accountId' => 788,
            'securityId' => 1000,
            'accountNumber' => '916985328',
            'securityType' => TradeData::SECURITY_TYPE_MUTUAL_FUND,
            'action' => TradeData::ACTION_SELL,
            'quantityType' => TradeData::QUANTITY_TYPE_SHARES,
            'quantity' => 43,
            'symbol' => 'RWX',
            'vsps' => $this->vspsData
        );

        $this->tradeData = $this->getMockBuilder('Model\WealthbotRebalancer\TradeData')
            ->setMethods(null)
            ->getMock();

        $this->tradeData->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $this->assertEquals(5, $this->tradeData->getId());
        $this->assertEquals(35, $this->tradeData->getJobId());
        $this->assertEquals(788, $this->tradeData->getAccountId());
        $this->assertEquals(1000, $this->tradeData->getSecurityId());
        $this->assertEquals('916985328', $this->tradeData->getAccountNumber());
        $this->assertEquals(TradeData::ACCOUNT_TYPE_CASH_ACCOUNT, $this->tradeData->getAccountType());
        $this->assertEquals(TradeData::SECURITY_TYPE_MUTUAL_FUND, $this->tradeData->getSecurityType());
        $this->assertEquals(TradeData::ACTION_SELL, $this->tradeData->getAction());
        $this->assertEquals(TradeData::QUANTITY_TYPE_SHARES, $this->tradeData->getQuantityType());
        $this->assertEquals(43, $this->tradeData->getQuantity());
        $this->assertEquals('RWX', $this->tradeData->getSymbol());
        $this->assertEquals('', $this->tradeData->getExchangeSwapSymbol());
        $this->assertEquals(TradeData::ORDER_TYPE_MARKET_ORDER, $this->tradeData->getOrderType());
        $this->assertEquals('', $this->tradeData->getLimitPrice());
        $this->assertEquals('', $this->tradeData->getStopPrice());
        $this->assertEquals(TradeData::TIME_IN_FORCE_GOOD_TILL_END_OF_DAY, $this->tradeData->getTimeInForce());
        $this->assertEquals('', $this->tradeData->getIsDoNotReduce());
        $this->assertEquals('', $this->tradeData->getIsAllOrNone());
        $this->assertFalse($this->tradeData->getIsReinvestDividends());
        $this->assertTrue($this->tradeData->getIsIncludeTransactionFee());
        $this->assertFalse($this->tradeData->getIsReinvestCapGains());
        $this->assertEquals(TradeData::TAX_LOT_ID_METHOD_SPECIFIC_LOT, $this->tradeData->getTaxLotIdMethod());

        $vsps = $this->tradeData->getVsps();

        $this->assertCount(2, $vsps);
        $this->assertEquals($this->vspsData, $vsps);
    }

    public function testToArrayForTradeFile()
    {
        $expectedData = array(
            'A' => '916985328',
            'B' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
            'C' => TradeData::SECURITY_TYPE_MUTUAL_FUND,
            'D' => Tradedata::ACTION_SELL,
            'E' => TradeData::QUANTITY_TYPE_SHARES,
            'F' => 43,
            'G' => 'RWX',
            'H' => '',
            'I' => TradeData::ORDER_TYPE_MARKET_ORDER,
            'J' => '',
            'K' => '',
            'L' => TradeData::TIME_IN_FORCE_GOOD_TILL_END_OF_DAY,
            'M' => '',
            'N' => '',
            'O' => 'N',
            'P' => 'Y',
            'Q' => 'N',
            'R' => TradeData::TAX_LOT_ID_METHOD_SPECIFIC_LOT
        );

        $this->assertEquals($expectedData, $this->tradeData->toArrayForTradeFile());

        $this->tradeData->setQuantityType(TradeData::QUANTITY_TYPE_ALL_SHARES);

        $expectedData = array(
            'A' => '916985328',
            'B' => TradeData::ACCOUNT_TYPE_CASH_ACCOUNT,
            'C' => TradeData::SECURITY_TYPE_MUTUAL_FUND,
            'D' => Tradedata::ACTION_SELL,
            'E' => TradeData::QUANTITY_TYPE_ALL_SHARES,
            'F' => '',
            'G' => 'RWX',
            'H' => '',
            'I' => TradeData::ORDER_TYPE_MARKET_ORDER,
            'J' => '',
            'K' => '',
            'L' => TradeData::TIME_IN_FORCE_GOOD_TILL_END_OF_DAY,
            'M' => '',
            'N' => '',
            'O' => 'N',
            'P' => 'Y',
            'Q' => 'N',
            'R' => TradeData::TAX_LOT_ID_METHOD_SPECIFIC_LOT
        );

        $this->assertEquals($expectedData, $this->tradeData->toArrayForTradeFile());

    }

    public function testGetJobId()
    {
        $this->assertEquals(35, $this->tradeData->getJobId());
    }

    public function testSetJobId()
    {
        $this->tradeData->setJobId(46);

        $this->assertEquals(46, $this->tradeData->getJobId());
    }

    public function testGetAccountId()
    {
        $this->assertEquals(788, $this->tradeData->getAccountId());
    }

    public function testSetAccountId()
    {
        $this->tradeData->setAccountId(12);

        $this->assertEquals(12, $this->tradeData->getAccountId());
    }

    public function testGetSecurityId()
    {
        $this->assertEquals(1000, $this->tradeData->getSecurityId());
    }

    public function testSetSecurityId()
    {
        $this->tradeData->setSecurityId(2000);

        $this->assertEquals(2000, $this->tradeData->getSecurityId());
    }

    public function testGetAccountNumber()
    {
        $this->assertEquals('916985328', $this->tradeData->getAccountNumber());
    }

    public function testSetAccountNumber()
    {
        $this->tradeData->setAccountNumber('111111111');

        $this->assertEquals('111111111', $this->tradeData->getAccountNumber());
    }

    public function testGetAccountType()
    {
        $this->assertEquals(TradeData::ACCOUNT_TYPE_CASH_ACCOUNT, $this->tradeData->getAccountType());
    }

    public function testSetAccountType()
    {
        $this->tradeData->setAccountType(6);

        $this->assertEquals(6, $this->tradeData->getAccountType());
    }

    public function testGetSecurityType()
    {
        $this->assertEquals(TradeData::SECURITY_TYPE_MUTUAL_FUND, $this->tradeData->getSecurityType());
    }

    public function testSetSecurityType()
    {
        $this->tradeData->setSecurityType(TradeData::SECURITY_TYPE_EQUITY);
        $this->assertEquals(TradeData::SECURITY_TYPE_EQUITY, $this->tradeData->getSecurityType());

        $this->tradeData->setSecurityType('mf');
        $this->assertEquals(TradeData::SECURITY_TYPE_MUTUAL_FUND, $this->tradeData->getSecurityType());

        $this->tradeData->setSecurityType('eq');
        $this->assertEquals(TradeData::SECURITY_TYPE_EQUITY, $this->tradeData->getSecurityType());

        $this->setExpectedException('Exception', 'Security Type must be E or M');
        $this->tradeData->setSecurityType('aaa');
    }

    public function testGetAction()
    {
        $this->assertEquals(TradeData::ACTION_SELL, $this->tradeData->getAction());
    }

    public function testSetAction()
    {
        $this->tradeData->setAction(TradeData::ACTION_BUY);
        $this->assertEquals(TradeData::ACTION_BUY, $this->tradeData->getAction());

        $this->tradeData->setAction('sell');
        $this->assertEquals(TradeData::ACTION_SELL, $this->tradeData->getAction());

        $this->tradeData->setAction('buY');
        $this->assertEquals(TradeData::ACTION_BUY, $this->tradeData->getAction());

        $this->setExpectedException('Exception', 'Action must be B or S');
        $this->tradeData->setAction('ccc');
    }

    public function testGetQuantityType()
    {
        $this->assertEquals(TradeData::QUANTITY_TYPE_SHARES, $this->tradeData->getQuantityType());
    }

    public function testSetQuantityType()
    {
        $this->tradeData->setQuantityType(TradeData::QUANTITY_TYPE_ALL_SHARES);
        $this->assertEquals(TradeData::QUANTITY_TYPE_ALL_SHARES, $this->tradeData->getQuantityType());

        $this->tradeData->setQuantityType('s');
        $this->assertEquals(TradeData::QUANTITY_TYPE_SHARES, $this->tradeData->getQuantityType());

        $this->tradeData->setQuantityType('as');
        $this->assertEquals(TradeData::QUANTITY_TYPE_ALL_SHARES, $this->tradeData->getQuantityType());

        $this->setExpectedException('Exception', 'Quantity Type must be S or AS');
        $this->tradeData->setQuantityType('bbb');
    }

    public function testGetQuantity()
    {
        $this->assertEquals(43, $this->tradeData->getQuantity());
    }

    public function testSetQuantity()
    {
        $this->tradeData->setQuantity(78);

        $this->assertEquals(78, $this->tradeData->getQuantity());
    }

    public function testGetSymbol()
    {
        $this->assertEquals('RWX', $this->tradeData->getSymbol());
    }

    public function testSetSymbol()
    {
        $this->tradeData->setSymbol('VTV');

        $this->assertEquals('VTV', $this->tradeData->getSymbol());
    }

    public function testGetExchangeSwapSymbol()
    {
        $this->assertEquals('', $this->tradeData->getExchangeSwapSymbol());
    }

    public function testSetExchangeSwapSymbol()
    {
        $this->tradeData->setExchangeSwapSymbol('VTI');

        $this->assertEquals('VTI', $this->tradeData->getExchangeSwapSymbol());
    }

    public function testGetOrderType()
    {
        $this->assertEquals(TradeData::ORDER_TYPE_MARKET_ORDER, $this->tradeData->getOrderType());
    }

    public function testSetOrderType()
    {
        $this->tradeData->setOrderType(TradeData::ORDER_TYPE_LIMIT_ORDER);

        $this->assertEquals(TradeData::ORDER_TYPE_LIMIT_ORDER, $this->tradeData->getOrderType());
    }

    public function testGetLimitPrice()
    {
        $this->assertEquals('', $this->tradeData->getLimitPrice());
    }

    public function testSetLimitPrice()
    {
        $this->tradeData->setLimitPrice('400');

        $this->assertEquals('400', $this->tradeData->getLimitPrice());
    }

    public function testGetStopPrice()
    {
        $this->assertEquals('', $this->tradeData->getStopPrice());
    }

    public function testSetStopPrice()
    {
        $this->tradeData->setStopPrice('500');

        $this->assertEquals('500', $this->tradeData->getStopPrice());
    }

    public function testGetTimeInForce()
    {
        $this->assertEquals(TradeData::TIME_IN_FORCE_GOOD_TILL_END_OF_DAY, $this->tradeData->getTimeInForce());
    }

    public function testSetTimeInForce()
    {
        $this->tradeData->setTimeInForce(TradeData::TIME_IN_FORCE_GOOD_TILL_DATE);

        $this->assertEquals(TradeData::TIME_IN_FORCE_GOOD_TILL_DATE, $this->tradeData->getTimeInForce());
    }

    public function testGetIsDoNotReduce()
    {
        $this->assertEquals('', $this->tradeData->getIsDoNotReduce());
    }

    public function testSetIsDoNotReduce()
    {
        $this->tradeData->setIsDoNotReduce(true);

        $this->assertTrue($this->tradeData->getIsDoNotReduce());
    }

    public function testGetIsAllOrNone()
    {
        $this->assertEquals('', $this->tradeData->getIsAllOrNone());
    }

    public function testSetIsAllOrNone()
    {
        $this->tradeData->setIsAllOrNone(true);

        $this->assertTrue($this->tradeData->getIsAllOrNone());
    }

    public function testGetIsReinvestDividends()
    {
        $this->assertFalse($this->tradeData->getIsReinvestDividends());
    }

    public function testSetIsReinvestDividends()
    {
        $this->tradeData->setIsReinvestDividends(true);

        $this->assertTrue($this->tradeData->getIsReinvestDividends());
    }

    public function testGetIsIncludeTransactionFee()
    {
        $this->assertTrue($this->tradeData->getIsIncludeTransactionFee());
    }

    public function testSetIsIncludeTransactionFee()
    {
        $this->tradeData->setIsIncludeTransactionFee(false);

        $this->assertFalse($this->tradeData->getIsIncludeTransactionFee());
    }

    public function testGetIsReinvestCapGains()
    {
        $this->assertFalse($this->tradeData->getIsReinvestCapGains());
    }

    public function testSetIsReinvestCapGains()
    {
        $this->tradeData->setIsReinvestCapGains(true);

        $this->assertTrue($this->tradeData->getIsReinvestCapGains());
    }

    public function testGetTaxLotIdMethod()
    {
        $this->assertEquals(TradeData::TAX_LOT_ID_METHOD_SPECIFIC_LOT, $this->tradeData->getTaxLotIdMethod());
    }

    public function testSetTaxLotIdMethod()
    {
        $this->tradeData->setTaxLotIdMethod(TradeData::TAX_LOT_ID_METHOD_FIFO);

        $this->assertEquals(TradeData::TAX_LOT_ID_METHOD_FIFO, $this->tradeData->getTaxLotIdMethod());
    }

    public function testGetVsps()
    {
        $vsps = $this->tradeData->getVsps();

        $this->assertCount(2, $vsps);
        $this->assertEquals($this->vspsData, $vsps);
    }

    public function testSetVsps()
    {
        $vsps = array(
            array(
                'purchase' => 'VSP',
                'purchase_date' => '02132013',
                'quantity' => 70
            ),
        );

        $this->tradeData->setVsps($vsps);

        $this->assertCount(1, $this->tradeData->getVsps());
        $this->assertEquals($vsps, $this->tradeData->getVsps());
    }
}