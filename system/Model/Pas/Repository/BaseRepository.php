<?php

namespace Model\Pas\Repository;

use Database\WealthbotMysqlSqliteConnection;
use Database\Builder\Fluent\FluentPDO;

abstract class BaseRepository
{
    const TABLE_LOT = 'lots';
    const TABLE_POSITION = 'positions';
    const TABLE_SECURITY = 'securities';
    const TABLE_SECURITY_TYPE = 'security_types';
    const TABLE_TRANSACTION = 'transactions';
    const TABLE_TRANSACTION_TYPE = 'transaction_types';
    const TABLE_ACCOUNT_TWR_VALUE = 'account_twr_values';
    const TABLE_ACCOUNT_TWR_PERIOD = 'account_twr_periods';
    const TABLE_CLIENT_PORTFOLIO = 'client_portfolio';
    const TABLE_CLIENT_PORTFOLIO_VALUE = 'client_portfolio_values';
    const TABLE_CLIENT_ACCOUNT_VALUE = 'client_account_values';
    const TABLE_CLOSING_METHOD = 'closing_methods';
    const TABLE_SYSTEM_CLIENT_ACCOUNT = 'system_client_accounts';
    const TABLE_GAIN_LOSS = 'gain_loss';
    const TABLE_REBALANCER_QUEUE = 'rebalancer_queue';
    const TABLE_PORTFOLIO_TWR_VALUE = 'portfolio_twr_values';
    const TABLE_PORTFOLIO_TWR_PERIOD = 'portfolio_twr_periods';
    const TABLE_ADVISOR_CODE = 'advisor_codes';
    const TABLE_CUSTODIAN = 'custodians';
    const TABLE_BILL_ITEM = 'bill_item';

    protected $db;

    protected $testMode;
    
    /**
     * @var \Database\Builder\Fluent\FluentPDO
     */
    protected $fpdo;

    /**
     * @var string
     */
    protected $table;

    public function __construct()
    {
        $this->db = WealthbotMysqlSqliteConnection::getInstance();
        $this->fpdo = new FluentPDO($this->db->getPdo());
        //$this->fpdo->debug = true;

        $options = $this->getOptions();
        $this->table = $options['table_name'];
    }

    /**
     * @param $id
     * @return array|null
     */
    public function find($id)
    {
        return $this->findOneBy(array('id' => $id));
    }

    /**
     * @param array $criteria
     * @param array $orderBy
     * @return array|null
     */
    public function findOneBy(array $criteria, $orderBy = array())
    {
        $result = $this->findBy($criteria, 1, $orderBy);

        return $result->first();
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->findBy();
    }

    /**
     * @param array $criteria
     * @param null $limit
     * @param array $orderBy
     * @param array $groupBy
     * @return array
     */
    public function findBy(array $criteria = array(), $limit = null, $orderBy = array(), $groupBy = null)
    {
        $query = $this->fpdo->from($this->table);

        if (!empty($criteria)) {
            $query->where($criteria);
        }

        if (!empty($orderBy)) {
            $query->orderBy($orderBy);
        }

        if ($limit) {
            $query->limit($limit);
        }

        if ($groupBy) {
            $query->groupBy($groupBy);
        }

        $results = $query->fetchAll();

        return $this->bindCollection($results);
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
            $collectionClass = 'Model\Pas\ArrayCollection';
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
     * @return mixed
     */
    protected function bind(array $data)
    {
        $options = $this->getOptions();
        $class = $options['model_name'];

        $element = new $class();
        return $element->loadFromArray($data);
    }

    public function commit()
    {
        $this->db->getPdo()->commit();
    }

    public function rollback()
    {
        $this->db->getPdo()->rollback();
    }

    public function beginTransaction()
    {
        $this->db->getPdo()->beginTransaction();
    }

    public function getBuilder()
    {
        return $this->fpdo;
    }

    public function setDebug($flag = true)
    {
        $this->fpdo->debug = $flag;
    }
}