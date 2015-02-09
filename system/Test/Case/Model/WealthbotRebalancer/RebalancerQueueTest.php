<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\QueueItem;
use Model\WealthbotRebalancer\RebalancerQueue;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RebalancerQueueTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\RebalancerQueue */
    private $rebalancerQueue;

    public function setUp()
    {
        $this->rebalancerQueue = $this->getMockBuilder('Model\WealthbotRebalancer\RebalancerQueue')
            ->setMethods(null)
            ->getMock();
    }

    public function testExistsInQueue()
    {
        $dataSell = array(
            'rebalancer_action_id' => 1,
            'account' => array('id' => 1),
            'security' => array('id' => 1),
            'status' => QueueItem::STATUS_SELL,
            'lot' => array('id' => 1)
        );

        $queueItemSell = new QueueItem();
        $queueItemSell->loadFromArray($dataSell);

        $this->assertNull($this->rebalancerQueue->existsInQueue($queueItemSell));

        $this->rebalancerQueue->add($queueItemSell);
        $this->assertEquals($queueItemSell, $this->rebalancerQueue->existsInQueue($queueItemSell));

        $dataBuy = array(
            'rebalancer_action_id' => 1,
            'account' => array('id' => 1),
            'security' => array('id' => 1),
            'status' => QueueItem::STATUS_BUY
        );

        $queueItemBuy = new QueueItem();
        $queueItemBuy->loadFromArray($dataBuy);

        $this->assertNull($this->rebalancerQueue->existsInQueue($queueItemBuy));

        $this->rebalancerQueue->add($queueItemBuy);
        $this->assertEquals($queueItemBuy, $this->rebalancerQueue->existsInQueue($queueItemBuy));

        $dataBuy = array(
            'rebalancer_action_id' => 2,
            'account' => array('id' => 1),
            'security' => array('id' => 1),
            'status' => QueueItem::STATUS_BUY
        );

        $queueItemBuy = new QueueItem();
        $queueItemBuy->loadFromArray($dataBuy);
        $this->assertNull($this->rebalancerQueue->existsInQueue($queueItemBuy));

        $dataBuy = array(
            'rebalancer_action_id' => 1,
            'account' => array('id' => 1),
            'security' => array('id' => 2),
            'status' => QueueItem::STATUS_BUY
        );

        $queueItemBuy = new QueueItem();
        $queueItemBuy->loadFromArray($dataBuy);
        $this->assertNull($this->rebalancerQueue->existsInQueue($queueItemBuy));

        $dataBuy = array(
            'rebalancer_action_id' => 1,
            'account' => array('id' => 2),
            'security' => array('id' => 1),
            'status' => QueueItem::STATUS_BUY
        );

        $queueItemBuy = new QueueItem();
        $queueItemBuy->loadFromArray($dataBuy);
        $this->assertNull($this->rebalancerQueue->existsInQueue($queueItemBuy));

    }

   public function testAddItem()
   {
       $dataSell = array(
           'job_id' => 1,
           'account' => array('id' => 1),
           'security' => array('id' => 1),
           'status' => QueueItem::STATUS_SELL,
           'lot' => array('id' => 1),
           'quantity' => 10,
           'amount' => 1000
       );

       /** @var QueueItem $queueItem */
       $queueItem = new QueueItem();
       $queueItem->loadFromArray($dataSell);

       $this->rebalancerQueue->addItem($queueItem);
       $this->assertCount(1, $this->rebalancerQueue);
       $this->assertEquals(10, $this->rebalancerQueue->first()->getQuantity());
       $this->assertEquals(1000, $this->rebalancerQueue->first()->getAmount());

       $this->rebalancerQueue->addItem($queueItem);
       $this->assertCount(1, $this->rebalancerQueue);
       $this->assertEquals(20, $this->rebalancerQueue->first()->getQuantity());
       $this->assertEquals(2000, $this->rebalancerQueue->first()->getAmount());

       $dataSell2 = array(
           'job_id' => 1,
           'account' => array('id' => 1),
           'security' => array('id' => 1),
           'status' => QueueItem::STATUS_SELL,
           'lot' => array('id' => 3),
           'quantity' => 15,
           'amount' => 150
       );

       /** @var QueueItem $queueItem */
       $queueItem = new QueueItem();
       $queueItem->loadFromArray($dataSell2);

       $this->rebalancerQueue->addItem($queueItem);
       $this->assertCount(2, $this->rebalancerQueue);
       $this->assertEquals(15, $this->rebalancerQueue->last()->getQuantity());
       $this->assertEquals(150, $this->rebalancerQueue->last()->getAmount());
   }
}