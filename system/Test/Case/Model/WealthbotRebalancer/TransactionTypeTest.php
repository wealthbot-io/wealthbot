<?php

namespace Test\Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class TransactionType extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\TransactionType */
    private $transactionType;

    public function setUp()
    {
        $data = array(
            'id' => 10,
            'name' => 'sell'
        );

        $this->transactionType = $this->getMockBuilder('Model\WealthbotRebalancer\TransactionType')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->transactionType->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $this->assertEquals(10, $this->transactionType->getId());
        $this->assertEquals('sell', $this->transactionType->getName());
    }

    public function testGetName()
    {
        $this->assertEquals('sell', $this->transactionType->getName());
    }

    public function testSetName()
    {
        $this->transactionType->setName('buy');
        $this->assertEquals('buy', $this->transactionType->getName());
    }

 }
