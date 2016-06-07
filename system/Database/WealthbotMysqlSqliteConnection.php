<?php

namespace Database;

require_once(__DIR__ . '/../AutoLoader.php');
require_once(__DIR__ . '/../Config.php');
\AutoLoader::registerAutoloader();

class WealthbotMysqlSqliteConnection
{
	private $db;

	private static $instance = null;

	 function __construct() {
		$dbhost = \Config::$MYSQL_HOST;
		$dbuser = \Config::$MYSQL_USERNAME;
		$dbpass = \Config::$MYSQL_PASSWORD;
		$dbname = \Config::$MYSQL_DATABASE;

		$dbDriver = 'sqlite';
		if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
			$sqlitedb = \Config::$SQLITE_DATABASE_TEST;
		} else {
			$dbDriver = 'mysql';
		}

		if (getenv('DRONE_TEST') == 'true') {
			$dbDriver = 'sqlite';
			$sqlitedb = \Config::$SQLITE_DATABASE_TEST;
		}

		$options = array(
			\PDO::ATTR_PERSISTENT => true,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		);
		try {
			switch($dbDriver) {
				case 'sqlite':
					$conn = "sqlite:{$sqlitedb}";
					$this->db = new \PDO($conn);
					$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
					break;
				case 'mysql':
					$conn = "mysql:host={$dbhost};dbname={$dbname}";
					$this->db = new \PDO($conn, $dbuser, $dbpass, $options);
					break;
				default:
					echo 'Unsuportted DB Driver! Check the configuration.';
					exit(1);
			}

		} catch(\PDOException $e) {
			echo $e->getMessage(); exit(1);
		}
	}
	private function __clone() { }
	private function __wakeup() { }

	/**
	 * @return self
	 */
	public static function getInstance()
	{
		if (null === self::$instance) {
			$class = __CLASS__;
			self::$instance = new $class;
		}

		return self::$instance;
	}

	function run($sql, $bind=array()) {
		$sql = trim($sql);

		try {

			$result = $this->db->prepare($sql);
			$result->execute($bind);
			return $result;

		} catch (\PDOException $e) {
			echo $e->getMessage(); exit(1);
		}
	}

	function create($table, $data) {
		$fields = $this->filter($table, $data);

		$sql = "INSERT INTO " . $table . " (" . implode($fields, ", ") . ") VALUES (:" . implode($fields, ", :") . ");";

		$bind = array();
		foreach($fields as $field)
			$bind[":$field"] = $data[$field];

		$result = $this->run($sql, $bind);
		return $this->db->lastInsertId();
	}

	function read($table, $where="", $bind=array(), $fields="*") {
		$sql = "SELECT " . $fields . " FROM " . $table;
		if(!empty($where))
			$sql .= " WHERE " . $where;
		$sql .= ";";

		$result = $this->run($sql, $bind);
		$result->setFetchMode(\PDO::FETCH_ASSOC);

		$rows = array();
		while($row = $result->fetch()) {
			$rows[] = $row;
		}

		return $rows;
	}

	function update($table, $data, $where, $bind=array()) {
		$fields = $this->filter($table, $data);
		$fieldSize = sizeof($fields);

		$sql = "UPDATE " . $table . " SET ";
		for($f = 0; $f < $fieldSize; ++$f) {
			if($f > 0)
				$sql .= ", ";
			$sql .= $fields[$f] . " = :update_" . $fields[$f]; 
		}
		$sql .= " WHERE " . $where . ";";

		foreach($fields as $field)
			$bind[":update_$field"] = $data[$field];
		
		$result = $this->run($sql, $bind);
		return $result->rowCount();
	}

	function delete($table, $where, $bind="") {
		$sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
		$result = $this->run($sql, $bind);
		return $result->rowCount();
	}

	private function filter($table, $data) {
		$driver = $this->config['dbdriver'];

		if($driver == 'sqlite') {
			$sql = "PRAGMA table_info('" . $table . "');";
			$key = "name";
		} elseif($driver == 'mysql') {
			$sql = "DESCRIBE " . $table . ";";
			$key = "Field";
		} else {    
			$sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
			$key = "column_name";
		}   

		if(false !== ($list = $this->run($sql))) {
			$fields = array();
			foreach($list as $record)
				$fields[] = $record[$key];
			return array_values(array_intersect($fields, array_keys($data)));
		}

		return array();
	}

	public function query($sql, array $parameters = array(), $fetchStyle = \PDO::FETCH_ASSOC)
	{
		$statement = $this->execute($sql, $parameters);

		return $statement->fetchAll($fetchStyle);
	}

	public function queryOne($sql, array $parameters = array(), $fetchStyle = \PDO::FETCH_ASSOC)
	{
		$statement = $this->execute($sql, $parameters);

		return $statement->fetch($fetchStyle);
	}
	/**
	 * Execute
	 *
	 * @param string $sql
	 * @param array $parameters
	 * @return \PDOStatement
	 */
	public function execute($sql, array $parameters = array())
	{
		$statement = $this->db->prepare($sql);
		$statement->execute($parameters);

		return  $statement;
	}

/**
 * Replaces any parameter placeholders in a query with the value of that
 * parameter. Useful for debugging. Assumes anonymous parameters from 
 * $params are are in the same order as specified in $query
 *
 * @param string $query The sql query with parameter placeholders
 * @param array $params The array of substitution parameters
 * @return string The interpolated query
 */
	public static function interpolateQuery($sql, $params) {
		$keys = array();

		# build a regular expression for each parameter
		foreach ($params as $key => $value) {
			if (is_string($key)) {
				$keys[] = '/:'.$key.'/';
			} else {
				$keys[] = '/[?]/';
			}
		}

		$sql = preg_replace($keys, $params, $sql, 1, $count);

		return $sql;
	}

	/**
	 * @return \PDO
	 */
	public function getPdo()
	{
		return $this->db;
	}
}