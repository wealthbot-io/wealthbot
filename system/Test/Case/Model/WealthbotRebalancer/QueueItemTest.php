<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\QueueItem;
use Model\WealthbotRebalancer\Subclass;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class QueueItemTest extends \PHPUnit_Framework_TestCase
{
    /** @var  QueueItem */
    private $queueItem;

    public function setUp()
    {
        $data = array(
            'id' => 43,
            'rebalancer_action_id' => 6,
            'lot' => array('id' => 13),
            'security' => array('id' => 1212),
            'account' => array('id' => 3),
            'quantity' => 50,
            'status' => QueueItem::STATUS_BUY,
            'amount' => 11.1,
            'subclass' => array('id' => 1313)
        );

        $this->queueItem = $this->getMockBuilder('Model\WealthbotRebalancer\QueueItem')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->queueItem->loadFromArray($data);
    }

    public function testGetSubclass()
    {
        $this->assertEquals(1313, $this->queueItem->getSubclass()->getId());
    }

    public function testSetSubclass()
    {
        $newSubclass = new Subclass();
        $newSubclass->setId(1414);

        $this->queueItem->setSubclass($newSubclass);

        $this->assertEquals(1414, $this->queueItem->getSubclass()->getId());
    }

    public function testGetRebalancerActionId()
    {
        $this->assertEquals(6, $this->queueItem->getRebalancerActionId());
    }

    public function testSetRebalancerActionId()
    {
        $this->queueItem->setRebalancerActionId(8);
        $this->assertEquals(8, $this->queueItem->getRebalancerActionId());
    }

    public function testGetLot()
    {
        $this->assertEquals(13, $this->queueItem->getLot()->getId());
    }

    public function testSetLotId()
    {
        $lot = $this->getMock('Model\WealthbotRebalancer\Lot', null);
        $lot->setId(14);

        $this->queueItem->setLot($lot);
        $this->assertEquals(14, $this->queueItem->getLot()->getId());
    }

    public function testGetSecurity()
    {
        $this->assertEquals(1212, $this->queueItem->getSecurity()->getId());
    }

    public function testSetSecurity()
    {
        $security = $this->getMock('Model\WealthbotRebalancer\Security', null);
        $security->setId(4545);


        $this->queueItem->setSecurity($security);
        $this->assertEquals(4545, $this->queueItem->getSecurity()->getId());
    }

    public function testGetQuantity()
    {
        $this->assertEquals(50, $this->queueItem->getQuantity());
    }

    public function testSetQuantity()
    {
        $this->queueItem->setQuantity(70);
        $this->assertEquals(70, $this->queueItem->getQuantity());
    }

    public function testGetAmount()
    {
        $this->assertEquals(11.1, $this->queueItem->getAmount());
    }

    public function testSetAmount()
    {
        $this->queueItem->setAmount(21.1);

        $this->assertEquals(21.1, $this->queueItem->getAmount());
    }

    public function testGetAccount()
    {
        $this->assertEquals(3, $this->queueItem->getAccount()->getId());
    }

    public function testSetAccount()
    {
        $account = new Account();
        $account->setId(96);

        $this->queueItem->setAccount($account);

        $this->assertEquals(96, $this->queueItem->getAccount()->getId());
    }

    public function testGetStatus()
    {
        $this->assertEquals(QueueItem::STATUS_BUY, $this->queueItem->getStatus());
    }

    public function testSetStatus()
    {
        $this->queueItem->setStatus(QueueItem::STATUS_SELL);
        $this->assertEquals(QueueItem::STATUS_SELL, $this->queueItem->getStatus());
    }

    public function testIsStatusSell()
    {
        $this->assertFalse($this->queueItem->isStatusSell());

        $this->queueItem->setStatus(QueueItem::STATUS_SELL);
        $this->assertTrue($this->queueItem->isStatusSell());
    }

    public function testIsStatusBuy()
    {
        $this->assertTrue($this->queueItem->isStatusBuy());

        $this->queueItem->setStatus(QueueItem::STATUS_SELL);
        $this->assertFalse($this->queueItem->isStatusBuy());
    }
}
