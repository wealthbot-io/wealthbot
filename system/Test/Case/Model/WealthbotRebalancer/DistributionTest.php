<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 26.02.14
 * Time: 18:32
 */

namespace Test\Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Distribution;

class DistributionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Model\WealthbotRebalancer\Distribution */
    private $distribution;

    public function setUp()
    {
        $data = array(
            'id' => 1,
            'type' => Distribution::TYPE_SCHEDULED,
            'transferMethod' => Distribution::TRANSFER_METHOD_BANK_TRANSFER,
            'amount' => 1000,
            'frequency' => Distribution::FREQUENCY_QUARTERLY,
            'transfer_date' => '2014-03-01',
            'created_at' => '2014-02-26',
            'updated_at' => '2014-02-26'
        );

        $this->distribution = $this->getMock('Model\WealthbotRebalancer\Distribution', null);
        $this->distribution->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $this->assertEquals(1, $this->distribution->getId());
        $this->assertEquals(Distribution::TYPE_SCHEDULED, $this->distribution->getType());
        $this->assertEquals(Distribution::TRANSFER_METHOD_BANK_TRANSFER, $this->distribution->getTransferMethod());
        $this->assertEquals(1000, $this->distribution->getAmount());
        $this->assertEquals(Distribution::FREQUENCY_QUARTERLY, $this->distribution->getFrequency());
        $this->assertEquals('2014-03-01', $this->distribution->getTransferDate()->format('Y-m-d'));
        $this->assertEquals('2014-02-26', $this->distribution->getCreatedAt()->format('Y-m-d'));
        $this->assertEquals('2014-02-26', $this->distribution->getUpdatedAt()->format('Y-m-d'));
    }

    public function testSetType()
    {
        $this->distribution->setType(Distribution::TYPE_ONE_TIME);
        $this->assertEquals(Distribution::TYPE_ONE_TIME, $this->distribution->getType());
    }

    public function testSetTransferMethod()
    {
        $this->distribution->setTransferMethod(Distribution::TRANSFER_METHOD_WIRE_TRANSFER);
        $this->assertEquals(Distribution::TRANSFER_METHOD_WIRE_TRANSFER, $this->distribution->getTransferMethod());
    }

    public function testSetAmount()
    {
        $this->distribution->setAmount(2300);
        $this->assertEquals(2300, $this->distribution->getAmount());
    }

    public function testSetFrequency()
    {
        $this->distribution->setFrequency(Distribution::FREQUENCY_EVERY_OTHER_WEEK);
        $this->assertEquals(Distribution::FREQUENCY_EVERY_OTHER_WEEK, $this->distribution->getFrequency());
    }

    public function testSetTransferDate()
    {
        $this->distribution->setTransferDate(new \DateTime('2014-04-20'));
        $this->assertEquals('2014-04-20', $this->distribution->getTransferDate()->format('Y-m-d'));
    }

    public function testSetCreatedAt()
    {
        $this->distribution->setCreatedAt(new \DateTime('2014-03-03'));
        $this->assertEquals('2014-03-03', $this->distribution->getCreatedAt()->format('Y-m-d'));
    }

    public function testSetUpdatedAt()
    {
        $this->distribution->setUpdatedAt(new \DateTime('2014-03-07'));
        $this->assertEquals('2014-03-07', $this->distribution->getUpdatedAt()->format('Y-m-d'));
    }
}
 