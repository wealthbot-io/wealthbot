<?php

namespace Database;

require_once(__DIR__ . '/../AutoLoader.php');
require_once(__DIR__ . '/../Config.php');
\AutoLoader::registerAutoloader();

class WealthbotSqliteConnection
{
    private $db;

    private static $instance = null;

    private function __construct()
    {
        $database = \Config::$SQLITE_DATABASE;

        if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
            $database = \Config::$SQLITE_DATABASE_TEST;
        }

        $this->db = new \PDO($database);
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

    /**
     * @return db
     */
    public function getDb()
    {
        return $this->db;
    }

    public function getLastInsertId()
    {
        return $this->getDb()->lastInsertId();
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
        $sql = $this->interpolateQuery($sql, $parameters);
        return $this->db->query($sql);
    }

    public function q($sql)
    {
        $results = $this->db->query($sql);

        return $results->fetchArray();
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->db;
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
}