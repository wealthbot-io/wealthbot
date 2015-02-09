<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 25.02.14
 * Time: 13:34
 */

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Holiday;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class HolidayTest extends \PHPUnit_Framework_TestCase
{
    /** @var Holiday */
    private $holiday;

    public function setUp()
    {
        $data = array(
            'date' => new \DateTime('02/23/1014'),
            'type' => Holiday::TYPE_WEEKEND
        );

        $this->holiday = $this->getMock('Model\WealthbotRebalancer\Holiday', null);
        $this->holiday->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        $date = new \DateTime('02/23/1014');

        $this->assertEquals($date, $this->holiday->getDate());
        $this->assertEquals(Holiday::TYPE_WEEKEND, $this->holiday->getType());
    }

    public function testGetDate()
    {
        $date = new \DateTime('02/23/1014');

        $this->assertEquals($date, $this->holiday->getDate());
    }

    public function testSetDate()
    {
        $date = new \DateTime('02/22/2014');
        $this->holiday->setDate($date);

        $this->assertEquals($date, $this->holiday->getDate());
    }

    public function testGetType()
    {
        $this->assertEquals(Holiday::TYPE_WEEKEND, $this->holiday->getType());
    }

    public function testSetType()
    {
        $this->holiday->setType(Holiday::TYPE_MARKET_HOLIDAY);
        $this->assertEquals(Holiday::TYPE_MARKET_HOLIDAY, $this->holiday->getType());
    }


}
 