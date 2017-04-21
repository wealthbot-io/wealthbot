<?php

namespace Database;

require_once(__DIR__ . '/../AutoLoader.php');
require_once(__DIR__ . '/../Config.php');
\AutoLoader::registerAutoloader();

class WealthbotMySqlConnection
{
    private $pdo;

    private static $instance = null;

    private function __construct()
    {
        $host = \Config::$MYSQL_HOST;
        $port = \Config::$MYSQL_PORT;

        $username = \Config::$MYSQL_USERNAME;
        $password = \Config::$MYSQL_PASSWORD;
        $database = \Config::$MYSQL_DATABASE;

        if (isset($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
            $username = \Config::$MYSQL_USERNAME_TEST;
            $password = \Config::$MYSQL_PASSWORD_TEST;
            $database = \Config::$MYSQL_DATABASE_TEST;
        }

        $dns = "mysql:host={$host};port={$port};dbname={$database}";
 
        $this->pdo = new \PDO($dns, $username, $password);
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
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    public function getLastInsertId()
    {
        return $this->getPdo()->lastInsertId(); 
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
        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return  $statement;
    }

    public function q($sql)
    {
        $statement = $this->pdo->query($sql);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }
}