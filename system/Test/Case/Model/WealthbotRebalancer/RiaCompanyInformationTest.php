<?php

namespace Test\Model\WealthbotRebalancer;


use Model\WealthbotRebalancer\Ria;
use Model\WealthbotRebalancer\RiaCompanyInformation;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RiaCompanyInformationTest extends \PHPUnit_Framework_TestCase
{
    /** @var  RiaCompanyInformation */
    private $riaCompanyInformation;

    public function setUp()
    {
        $data = array(
            'ria' => array('id' => 1),
            'use_transaction_fees' => true,
            'transaction_min_amount' => 200,
            'transaction_min_amount_percent' => 5,
        );

        $this->riaCompanyInformation = $this->getMockBuilder('Model\WealthbotRebalancer\RiaCompanyInformation')
            ->setMethods(null)
            ->getMock();

        $this->riaCompanyInformation->loadFromArray($data);
    }

    public function testGetRia()
    {
        $this->assertEquals(1, $this->riaCompanyInformation->getRia()->getId());
    }

    public function testSetRia()
    {
        $ria = new Ria();
        $ria->setId(45);

        $this->riaCompanyInformation->setRia($ria);
        $this->assertEquals(45, $this->riaCompanyInformation->getRia()->getId());
    }

    public function testGetUseTransactionFees()
    {
        $this->assertTrue($this->riaCompanyInformation->getUseTransactionFees());
    }

    public function testGetTransactionMinAmount()
    {
        $this->assertEquals(200, $this->riaCompanyInformation->getTransactionMinAmount());
    }

     public function testGetTransactionMinAmountPercent()
    {
        $this->assertEquals(5, $this->riaCompanyInformation->getTransactionMinAmountPercent());
    }
}