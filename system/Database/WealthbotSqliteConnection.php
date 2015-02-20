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

        $this->db = new \SQLite3($database);
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
        var_dump($sql);
        $statement = $this->db->prepare($sql);
        $statement->execute($parameters);

        return  $statement;
    }

    public function q($sql)
    {
        $results = $this->db->query($sql);

        return $results->fetchArray();
    }
}