<?php
namespace System\Console;

require_once(__DIR__ . '/../../AutoLoader.php');
System\AutoLoader::registerAutoloader();

use System\Database\Database;
use System\Database\Connection;

use System\Model\Rebalancer\Account;

class CashBufferCalculator {

	private $cashBuffer;

	public function __construct() {
		$this->connection = new Connection();
		$this->Account = new Account();
	}

	public function getTotalAccountValue() {

	}
}