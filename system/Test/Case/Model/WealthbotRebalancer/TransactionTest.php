<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Lot;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\Transaction;
use Model\WealthbotRebalancer\TransactionType;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\Transaction */
    private $transaction;

    public function setUp()
    {
        $data = array(
            'id' => 1,
            'transactionType' => array(
                'id' => 1,
                'name' => 'buy',
            ),
            'isGain' => false,
            'grossAmount' => 10000,
            'account' => array(
                'id' => 3
            ),
            'security' => array(
                'id' => 4
            ),
            'lot' => array(
                'id' => 5
            )
        );

        $this->transaction = $this->getMockBuilder('Model\WealthbotRebalancer\Transaction')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->transaction->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $transactionType = $this->transaction->getTransactionType();

        $this->assertEquals(1, $this->transaction->getId());
        $this->assertEquals(1, $transactionType->getId());
        $this->assertEquals('buy', $transactionType->getName());
        $this->assertFalse($this->transaction->getIsGain());
        $this->assertEquals(10000, $this->transaction->getGrossAmount());
        $this->assertEquals(3, $this->transaction->getAccount()->getId());
        $this->assertEquals(4, $this->transaction->getSecurity()->getId());
        $this->assertEquals(5, $this->transaction->getLot()->getId());
    }

    public function testGetTransactionType()
    {
        $txType = $this->transaction->getTransactionType();
        $this->assertEquals(1, $txType->getId());
    }

    public function testSetTransactionType()
    {
        $txType = new TransactionType();
        $txType->setId(5);
        $txType->setName('sell');

        $this->transaction->setTransactionType($txType);

        $this->assertEquals(5, $this->transaction->getTransactionType()->getId());
        $this->assertEquals('sell', $this->transaction->getTransactionType()->getName());
    }

    public function testIsTypeSell()
    {
        $this->assertFalse($this->transaction->isTypeSell());

        $transactionType = new TransactionType();
        $transactionType->setName(Transaction::TYPE_SELL);

        $this->transaction->setTransactionType($transactionType);

        $this->assertTrue($this->transaction->isTypeSell());
    }

    public function testIsTypeBuy()
    {
        $this->assertTrue($this->transaction->isTypeBuy());

        $transactionType = new TransactionType();
        $transactionType->setName(Transaction::TYPE_SELL);

        $this->transaction->setTransactionType($transactionType);

        $this->assertFalse($this->transaction->isTypeBuy());
    }

    public function testGetIsGain()
    {
        $this->assertFalse($this->transaction->getIsGain());
    }

    public function testSetIsGain()
    {
        $this->transaction->setIsGain(true);
        $this->assertTrue($this->transaction->getIsGain());
    }

    public function testGetGrossAmount()
    {
        $this->assertEquals(10000, $this->transaction->getGrossAmount());
    }

    public function testSetGrossAmount()
    {
        $this->transaction->setGrossAmount(5000);
        $this->assertEquals(5000, $this->transaction->getGrossAmount());
    }

    public function testGetAccount()
    {
        $this->assertEquals(3, $this->transaction->getAccount()->getId());
    }

    public function testSetAccount()
    {
        $account = new Account();
        $account->setId(13);

        $this->transaction->setAccount($account);

        $this->assertEquals(13, $this->transaction->getAccount()->getId());
    }

    public function testGetSecurity()
    {
        $this->assertEquals(4, $this->transaction->getSecurity()->getId());
    }

    public function testSetSecurity()
    {
        $security = new Security();
        $security->setId(14);

        $this->transaction->setSecurity($security);

        $this->assertEquals(14, $this->transaction->getSecurity()->getId());
    }

    public function testGetLot()
    {
        $this->assertEquals(5, $this->transaction->getLot()->getId());
    }

    public function testSetLot()
    {
        $lot = new Lot();
        $lot->setId(15);

        $this->transaction->setLot($lot);

        $this->assertEquals(15, $this->transaction->getLot()->getId());
    }

 }
