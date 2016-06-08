<?php

namespace Test\Model\Pas;

use Pas\TwrCalculator\Functions as TwrFunctions;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

require(__DIR__ . '/../../../Fixture/TwrValuesFixture.php');

class TwrCalculatorFunctionsTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $class = new \TwrValuesFixture();
        $this->fixture = $class->getList();
    }

    public function testCalculateTodayTwr()
    {
        $todayTwr = TwrFunctions::calculateTodayTwr(2, 4, 8);
        $this->assertEquals(-1.25, $todayTwr);
    }

    public function testCalculateActualTwr()
    {
        $actualTwr = TwrFunctions::calculateActualTwr($this->fixture);
        $this->assertEquals(1644.9402268886, $actualTwr);
    }

    public function testCalculateAnnualizedTwr()
    {
        $actualTwr = TwrFunctions::calculateActualTwr($this->fixture);
        $annualizedTwr = TwrFunctions::calculateAnnualizedTwr($actualTwr, 30);
        $this->assertEquals(163800.0, $annualizedTwr);
    }
} 