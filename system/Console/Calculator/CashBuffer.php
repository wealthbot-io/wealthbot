<?php
namespace Console;

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Database\Database;
use \Database\Connection;

use \Model\Rebalancer\Account;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class CashBufferCalculator {

	private $cashBuffer;

	public function __construct() {
		$this->connection = new Connection();
		$this->Account = new Account();
	}

	public function getTotalAccountValue() {

	}
}