<?php
namespace Model\Rebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler as StreamHandler;
use \Database\Connection;

class Base {
	/**
	 * Holds logger instance
	 * @var object
	 */
	protected $log;

	/**
	 * @var MongoDB
	 */
	protected $mongoDB;

	/**
	 * @var Mysqlidb
	 */
	protected $mySqlDB;

	/**
	 * Model PK
	 *
	 * @var int
	 */
	protected $id;

	public function __construct() {
		try {
			$this->connection = new Connection();
			$this->db = $this->connection->getMySqlDB();
		} catch (Exception $e) {
			//could not connect
		}

		$this->log = new Logger('filesys');
		$this->log->pushHandler(new StreamHandler(__DIR__ . '/../../logs/normalizer.log', Logger::WARNING));
	}

	public function id($val = null) {
		if (!is_null($val)) {
			$this->id = $val;
		}

		return $this->id;
	}
}