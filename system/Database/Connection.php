<?php
namespace Database;

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Connection {
	/**
	 * @var MongoClient
	 */
	protected $mongo;

	/**
	 * @var MongoDB
	 */
	protected $mongoDB;

	/**
	 * @var Mysqlidb
	 */
	protected $mySqlDB;

	/**
	 * Supported data type
	 * @var string
	 */
	protected $dataType;

	/**
	 * Date having format "yyyy-mm-dd"
	 * @var string
	 */
	protected $dataDate;

	/**
	 * @var int
	 */
	protected $dataDateTime;

	public function __construct() {
		$this->mySqlDB = $this->getMySqlDB();
		$this->mongoDB = $this->getMongoDB();
	}

	/**
	 *
	 * Note: problem connecting mongoDB will throw MongoConnectionException
	 * @return MongoDB
	 * @throws MongoConnectionException
	 */
	public function getMongoDB($force = false) 
    {
		if (\Config::$DEBUG) {
			error_reporting( E_ALL );
			// print every log message possible
			if (\Config::$DEBUG == 10) {
				\MongoLog::setLevel(\MongoLog::ALL); // all log levels
				\MongoLog::setModule(\MongoLog::ALL); // all parts of the driver
			}
		}

		if (is_null($this->mongo) || is_null($this->mongoDB) || $force) {
			$this->mongo = new \MongoClient(); // connect
			$this->mongoDB = $this->mongo->selectDB(\Config::$MONGODB_DATABASE);
		}
        
		return $this->mongoDB;
	}

	/**
	 *
	 * @param type $force
	 * @return type
	 */
    public function getMySqlDB($force = false) {
        if (is_null($this->mySqlDB) || $force) {
            $username = \Config::$MYSQL_USERNAME;
            $password = \Config::$MYSQL_PASSWORD;
            $database = \Config::$MYSQL_DATABASE;

            if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
                $username = \Config::$MYSQL_USERNAME_TEST;
                $password = \Config::$MYSQL_PASSWORD_TEST;
                $database = \Config::$MYSQL_DATABASE_TEST;
            }

            $this->mySqlDB = new Database(
                \Config::$MYSQL_HOST,
                $username,
                $password,
                $database,
                \Config::$DEBUG);

            if (!$this->mySqlDB) {
                throw new Exception(
                    'Can not connect to Mysql mySqlDB using mysqli driver.');
            }
        }
        if (\Config::$DEBUG) {
            // TEMP HACK:
            $q = 'SET foreign_key_checks = 0;';
            $this->mySqlDB->q($q);
        }

        return $this->mySqlDB;
    }
}