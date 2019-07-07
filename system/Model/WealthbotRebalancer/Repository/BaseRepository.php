<?php
namespace Model\WealthbotRebalancer\Repository;

use Database\WealthbotMysqlSqliteConnection;
use Model\WealthbotRebalancer\ArrayCollection;
use Model\WealthbotRebalancer\Base;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

abstract class BaseRepository {

    const TABLE_USER = 'users';
    const TABLE_SECURITY = 'securities';
    const TABLE_SECURITY_ASSIGNMENT = 'securities_assignments';
    const TABLE_SECURITY_PRICE = 'security_prices';
    const TABLE_SECURITY_TYPE = 'security_types';
    const TABLE_SECURITY_TRANSACTION = 'security_transaction';
    const TABLE_CLIENT_PORTFOLIO = 'client_portfolio';
    const TABLE_CLIENT_PORTFOLIO_VALUE = 'client_portfolio_values';
    const TABLE_ASSET_CLASS = 'asset_classes';
    const TABLE_SUBCLASS = 'subclasses';
    const TABLE_SUBCLASS_ACCOUNT_TYPE = 'subclass_account_types';
    const TABLE_USER_PROFILE = 'user_profiles';
    const TABLE_CLIENT_ACCOUNT = 'client_accounts';
    const TABLE_CLIENT_ACCOUNT_VALUES = 'client_account_values';
    const TABLE_SYSTEM_ACCOUNT = 'system_client_accounts';
    const TABLE_POSITION = 'positions';
    const TABLE_HOLIDAY = 'holidays';
    const TABLE_DISTRIBUTION = 'distributions';
    const TABLE_LOT = 'lots';
    const TABLE_REBALANCER_QUEUE = 'rebalancer_queue';
    const TABLE_BILL_ITEM = 'bill_item';
    const TABLE_CE_MODEL = 'ce_models';
    const TABLE_CE_MODEL_ENTITY = 'ce_model_entities';
    const TABLE_JOB = 'jobs';
    const TABLE_RIA_COMPANY_INFORMATION = 'ria_company_information';
    const TABLE_CLIENT_SETTINGS = 'client_settings';
    const TABLE_REBALANCER_ACTION = 'rebalancer_actions';

    protected static $availableTables = array();

    protected $table;

    protected $db;

    public function __construct()
    {
        $this->db = WealthbotMysqlSqliteConnection::getInstance();

        $this->initAvailableTables();

        $this->validateOptions();

        $options = $this->getOptions();
        $this->table = $options['table_name'];
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->findOneBy(array('id' => $id));
    }

    /**
     * @return ArrayCollection
     */
    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table}";

        $results = $this->db->query($sql);

        return $this->bindCollection($results);
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @return mixed
     */
    public function findOneBy(array $criteria, $orderBy = array())
    {
        $result = $this->findBy($criteria, 1, $orderBy);

        return $result->first();
    }

    /**
     * @return mixed
     */
    public function findFirst()
    {
        $result = $this->findAll();

        return $result->first();
    }

    /**
     * @param array $criteria
     * @param null $limit
     * @param array $orderBy
     * @return ArrayCollection
     */
    public function findBy(array $criteria, $limit = null, $orderBy = array())
    {
        $sql = "SELECT * FROM {$this->table}";

        $isFirstCondition = true;
        foreach ($criteria as $field => $value) {
            if ($isFirstCondition) {
                $sql .= " WHERE";
                $isFirstCondition = false;
            } else {
                $sql .= " AND";
            }

            $sql .= " ".$field." = :".$field;
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY";

            $isFirstOrder = true;
            foreach ($orderBy as $orderField => $sort)
            {
                if (!$isFirstOrder) {
                    $sql .= ",";
                }

                $sql .= " ".$orderField. " ".$sort;

                $isFirstOrder = false;
            }
        }

        if ($limit) {
            $sql .= " LIMIT ".$limit;
        }

        $results = $this->db->query($sql, $criteria);

        $collection = $this->bindCollection($results);

        return $collection;
    }

    public function getLastInsertId()
    {
        return $this->db->getLastInsertId();
    }

    /**
     * @param array $data
     * @return ArrayCollection
     */
    protected function bindCollection(array $data)
    {
        $options = $this->getOptions();
        $class = $options['model_name'];

        if (class_exists($class.'Collection')) {
            $collectionClass = $class.'Collection';
        } else {
            $collectionClass = 'Model\WealthbotRebalancer\ArrayCollection';
        }

        $collection = new $collectionClass();

        foreach ($data as $values) {
            $element = new $class();
            $element->loadFromArray($values);

            $collection->add($element);
        }

        return $collection;
    }

    /**
     * @param array $data
     * @return Base
     */
    protected function bindObject(array $data)
    {
        $options = $this->getOptions();
        $class = $options['model_name'];

        $element = new $class();
        $element->loadFromArray($data);

        return $element;
    }

    private function initAvailableTables()
    {
        $reflection = new \ReflectionClass(__CLASS__);
        foreach ($reflection->getConstants() as $name => $value ) {
            if ('TABLE_' === substr($name, 0, 6)) {
                self::$availableTables[$value] = $value;
            }
        }
    }

    private function validateOptions()
    {
        $options = $this->getOptions();

        if (!isset($options['table_name'])) {
            throw new \Exception('"table_name" - required');
        }

        if (!in_array($options['table_name'], self::$availableTables)) {
            throw new \Exception('Table "'.$options['table_name'].'" does not exist');
        }

        if (!isset($options['model_name'])) {
            throw new \Exception('"model_name" - required');
        }

        if (!class_exists($options['model_name'])) {
            throw new \Exception('Class "'.$options['model_name'].'" does not exist');
        }

        $class = new $options['model_name'];

        if (!($class instanceof Base)) {
            throw new \Exception('Class "'.$options['model_name'].' must be instanceof "Model\WealthbotRebalancer\Base"');
        }
    }

    abstract protected function getOptions();
}
