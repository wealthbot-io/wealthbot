<?php
/*
 * This file will be responsibe for reading raw data from mongoDB and insert it
 * into normalized mysql DB.
 *
 * Usage: php normalizer.php <type> <date>
 * Allowed file types: TRN, SEC, CBL, PRI, POS, TRD
 * Allowed date format: yyyy-mm-dd
 */
namespace Console;

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Database\Database;
use \Database\Connection;
use \Lib\Ioc;

class Normalizer {

	protected $connection;

	/**
	 * Mapping from file type to document name
	 */
	protected $docMap = array(
		'TRN' => 'transactions',
		'SEC' => 'securities',
		'CBL' => 'unrealized_gains',
        'CBP' => 'unrealized_gains',
		'PRI' => 'prices',
		'POS' => 'positions',
		'TRD' => 'demographics',
        'TWR' => 'twr_calculator',
        'TXT' => 'realized'
	);

	public static function help()
    {
		echo "\n******************************************\n
			 Usage: php normalizer.php <type> <date>\n
			 Allowed file types: TRN, SEC, CBL, CBP, PRI, POS, TRD, TWR, TXT\n
			 Allowed date format: yyyy-mm-dd\n
			 ******************************************\n\n";
	}

	public function __construct($argv)
    {
        // TODO: deprecated (remove later)
		$this->connection = new Connection();

        // Register connection instance
        Ioc::instance('connection', new Connection);

		$allowedTypes = array_keys($this->docMap);

		if (!isset($argv[1]) || !in_array($argv[1], $allowedTypes)) {
			self::help();
			throw new \Exception('Invalid data type: ' . @$argv[1] . ' (Allowed types: ' . implode(', ', $allowedTypes) . ')');
		}

		$date_format = 'Y-m-d';
		$date = trim(@$argv[2]);

		if (!isset($argv[2]) || !strtotime($date) || date($date_format, strtotime($date)) !== $date ) {
			self::help();
			throw new \Exception('Invalid date: ' . $date . ' (Date format must be yyyy-mm-dd)');
		}

		$this->dataType = $argv[1];
		$this->dataDateTime = strtotime($date);
		$this->dataDate = date($date_format, $this->dataDateTime);
	}

	/**
	 * Main Exec fucniton
	 * @throws Exception
	 */
	public function exec()
    {
		echo "START: " . date("Y-m-d H:i:s") . " \n";
		switch($this->dataType) {
			case 'TRN':
				$this->updateTransactions();
				break;
			case 'SEC':
				$this->updateSecurities();
				break;
			case 'POS':
				$this->updatePositions();
				break;
			case 'TXT':
				$this->updateRealized();
				break;
			case 'CBL':
				$this->updateUnrealized();
				break;
			case 'CBP':
				$this->updateUnrealized();
				break;
			case 'TWR':
				$this->updateTwrCalculator();
				break;
			default:
				self::help();
				throw new Exception("No executible function found. Most likely it's incorrect data Type.\n");
		}
		echo "DONE: " . date("Y-m-d H:i:s") . " \n";

        // Send all bugs
        $bugTracker = Ioc::resolve('bugTracker');
        $bugTracker->send();
	}

	protected function updateSecurities()
    {
		$security = new \Pas\Security();
        $security->run($this->dataDate, $this->dataDate);
	}

	protected function updateRealized()
    {
		$realized = new \Pas\Realized();
        $realized->run($this->dataDate, $this->dataDate);
	}

	protected function updateUnrealized()
    {
		$unrealized = new \Pas\Unrealized();
        $unrealized->run($this->dataDate, $this->dataDate);
	}

	protected function updatePositions()
    {
		$position = new \Pas\Position();
        $position->run($this->dataDate, $this->dataDate);
	}

	protected function updateTransactions()
    {
		$transaction = new \Pas\Transaction();
        $transaction->run($this->dataDate, $this->dataDate);
	}

    protected function updateTwrCalculator()
    {
        $twrCalculator = new \Pas\TwrCalculator();
        $twrCalculator->run($this->dataDate);
    }
}

if (php_sapi_name() == "cli") {
	//In cli-mode
	if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {

        // Register log
        Ioc::register('log', function($filename = 'normalizer.log') {
            $log = new \Monolog\Logger('filesys');
            $log->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . DS.'..'.DS.'..'.DS.'app'.DS.'logs'.DS.$filename, \Monolog\Logger::WARNING));
            return $log;
        });

        // Register bug tracker
        Ioc::instance('bugTracker', new \Bug\Tracker);

		//Let's normalize raw data
		$execClass = new Normalizer($argv);
		$execClass->exec();
	} else {
		// Most like it's called from test, so ignore autoexec ...
	}
} else {
	// Not in cli-mode
}
