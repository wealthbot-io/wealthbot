<?php

namespace Test\Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class PositionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Model\WealthbotRebalancer\Position */
    private $position;

    public function setUp()
    {
        $data = array(
            'id' => 10,
            'amount' => 11.1
        );

        $this->position = $this->getMockBuilder('Model\WealthbotRebalancer\Position')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->position->loadFromArray($data);
    }

    public function testGetAmount()
    {
        $this->assertEquals(11.1, $this->position->getAmount());
    }

    public function testSetAmount()
    {
        $this->position->setAmount(21.1);

        $this->assertEquals(21.1, $this->position->getAmount());
    }
}