<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Lot;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class LotTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\Lot */
    private $lot;

    public function setUp()
    {
        $data = array(
            'id' => 10,
            'age' => 50,
            'amount' => 3000,
            'quantity' => 40,
            'date' => '2013-02-02',
            'initial' => array('id' => 1),
            'initial_lot_id' => 1,
            'position_id' => 2,
            'is_muni' => false,
            'status' => Lot::LOT_IS_OPEN,
            'was_closed' => false,
            'realized_gain_or_loss' => 11.1,
            'cost_basis' => 45.2
        );

        $this->lot = $this->getMockBuilder('Model\WealthbotRebalancer\Lot')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->lot->loadFromArray($data);
    }

    public function testGetAmount()
    {
        $this->assertEquals(3000, $this->lot->getAmount());
    }

    public function testSetAmount()
    {
        $this->lot->setAmount(1000);
        $this->assertEquals(1000, $this->lot->getAmount());
    }

    public function testGetQuantity()
    {
        $this->assertEquals(40, $this->lot->getQuantity());
    }

    public function testSetQuantity()
    {
        $this->lot->setQuantity(80);
        $this->assertEquals(80, $this->lot->getQuantity());
    }

    public function testGetDate()
    {
        $this->assertEquals('2013-02-02', $this->lot->getDate()->format('Y-m-d'));
    }

    public function testSetDate()
    {
        $this->lot->setDate(new \DateTime('2014-01-01'));
        $this->assertEquals('2014-01-01', $this->lot->getDate()->format('Y-m-d'));
    }

    public function testGetStatus()
    {
        $this->assertEquals(Lot::LOT_IS_OPEN, $this->lot->getStatus());
    }

    public function testSetStatus()
    {
        $this->lot->setStatus(Lot::LOT_CLOSED);

        $this->assertEquals(Lot::LOT_CLOSED, $this->lot->getStatus());
    }

    public function testIsInitial()
    {
        $this->assertFalse($this->lot->isInitial());

        $this->lot->setStatus(Lot::LOT_INITIAL);
        $this->assertTrue($this->lot->isInitial());
    }

    public function testIsOpen()
    {
        $this->assertTrue($this->lot->isOpen());

        $this->lot->setStatus(Lot::LOT_CLOSED);
        $this->assertFalse($this->lot->isOpen());
    }

    public function testIsClosed()
    {
        $this->assertFalse($this->lot->isClosed());

        $this->lot->setStatus(Lot::LOT_CLOSED);
        $this->assertTrue($this->lot->isClosed());
    }

    public function testGetAge()
    {
        $this->assertEquals(50, $this->lot->getAge());
    }

    public function testSetAge()
    {
        $this->lot->setAge(100);
        $this->assertEquals(100, $this->lot->getAge());
    }

    public function testCalcPrice()
    {
        $this->lot->setQuantity(0);
        $this->assertNull($this->lot->calcPrice());

        $this->lot->setQuantity(2);
        $this->assertEquals(1500, $this->lot->calcPrice());
    }

    public function testSell()
    {
        $this->lot->setQuantity(2);
        $this->assertFalse($this->lot->sell(3));

        $this->lot->setQuantity(10);
        $this->assertTrue($this->lot->sell(5));
        $this->assertEquals(5, $this->lot->getQuantity());
        $this->assertEquals(1500, $this->lot->getAmount());
    }

    public function testGetWasClosed()
    {
        $this->assertFalse($this->lot->getWasClosed());
    }

    public function testSetWasClosed()
    {
        $this->lot->setWasClosed(true);

        $this->assertTrue($this->lot->getWasClosed());
    }

    public function testGetInitial()
    {
        $this->assertEquals(1, $this->lot->getInitial()->getId());
    }

    public function testSetInitial()
    {
        $initial = $this->getMock('Model\WealthbotRebalancer\Lot', null);
        $initial->setId(5);

        $this->lot->setInitial($initial);
        $this->assertEquals(5, $this->lot->getInitial()->getId());
    }

    public function testGetInitialLotId()
    {
        $this->assertEquals(1, $this->lot->getInitialLotId());
    }


    public function testGetRealizedGainOrLoss()
    {
        $this->assertEquals(11.1, $this->lot->getRealizedGainOrLoss());
    }

    public function testSetRealizedGainOrLoss()
    {
        $this->lot->setRealizedGainOrLoss(21.1);

        $this->assertEquals(21.1, $this->lot->getRealizedGainOrLoss());
    }

    public function testGetPositionId()
    {
        $this->assertEquals(2, $this->lot->getPositionId());
    }

    public function testSetPositionId()
    {
        $this->lot->setPositionId(5);
        $this->assertEquals(5, $this->lot->getPositionId());
    }

    public function testGetIsMuni()
    {
        $this->assertFalse($this->lot->getIsMuni());
    }

    public function testSetIsMuni()
    {
        $this->lot->setIsMuni(true);
        $this->assertTrue($this->lot->getIsMuni());

        $this->lot->setIsMuni(0);
        $this->assertFalse($this->lot->getIsMuni());
    }

    public function testGetCostBasis()
    {
        $this->assertEquals(45.2, $this->lot->getCostBasis());
    }

    public function testSetCostBasis()
    {
        $this->lot->setCostBasis(78.6);

        $this->assertEquals(78.6, $this->lot->getCostBasis());
    }

    public function testInterval()
    {
        $today = new \DateTime();
        $diff = $today->diff($this->lot->getDate());

        $this->assertEquals($diff->format('%a'), $this->lot->interval());
        $this->assertEquals(5, $this->lot->interval(new \DateTime('2013-02-07')));
    }

    public function testIsShortTerm()
    {
        $now = new \DateTime();
        $this->lot->getInitial()->setDate($now->modify('-1 month'));
        $this->assertTrue($this->lot->isShortTerm());

        $now = new \DateTime();
        $this->lot->getInitial()->setDate($now->modify('-10 year -1 month'));
        $this->assertFalse($this->lot->isShortTerm());

        $this->lot->setStatus(Lot::LOT_INITIAL);

        $now = new \DateTime();
        $this->lot->setDate($now->modify('-1 month'));
        $this->assertTrue($this->lot->isShortTerm());

        $now = new \DateTime();
        $this->lot->setDate($now->modify('-1 year -1 month'));
        $this->assertFalse($this->lot->isShortTerm());

    }
}