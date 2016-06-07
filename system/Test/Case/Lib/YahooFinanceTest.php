<?php
require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Lib\YahooFinance;

class YahooFinanceTest extends PHPUnit_Framework_TestCase {

	/**
	 * Basic YahooFinance instance
	 *
	 * @var object
	 */
	private $YahooFinance;

	protected function setUp() {
		$this->YahooFinance = new YahooFinance();
	}

	protected function tearDown() {

	}

	public function testInit() {
		$expected = array(0 => 'YHOO', 1 => 'AAPL', 2 => 'GOOG', 3 => 'MSFT');

		$quotes = $this->YahooFinance->init();
		$this->assertEquals($expected, array_keys($quotes));
	}


}