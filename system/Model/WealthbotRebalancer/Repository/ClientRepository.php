<?php
namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\RebalancerAction;
use Model\WealthbotRebalancer\Ria;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class ClientRepository extends BaseRepository {

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_USER,
            'model_name' => 'Model\WealthbotRebalancer\Client'
        );
    }

    public function findClientsByRia(Ria $ria) {
        $sql = "SELECT c.*, cp.client_account_managed as accountManaged, cp.estimated_income_tax as taxBracket
                FROM ".$this->table." c
                    LEFT JOIN ".self::TABLE_USER_PROFILE." cp ON c.id = cp.user_id
                WHERE c.roles LIKE :roles AND cp.ria_user_id = :ria_user_id AND c.enabled = 1
        ";

        $parameters = array(
            'ria_user_id' => $ria->getId(),
            'roles' => '%ROLE_CLIENT%'
        );

        $result = $this->db->query($sql, $parameters);

        $collection = $this->bindCollection($result);

        foreach ($collection as $client) {
            $client->setRia($ria);
        }

        return $collection;
    }

    /**
     * Find one client by email
     *
     * @param $email
     * @return Client
     */
    public function findClientByEmail($email) {
        $sql = "SELECT c.*, cp.client_account_managed as accountManaged, cp.estimated_income_tax as taxBracket
                FROM ".$this->table." c
                    LEFT JOIN ".self::TABLE_USER_PROFILE." cp ON c.id = cp.user_id
                WHERE c.roles LIKE :roles AND c.email = :email AND c.enabled = 1
                LIMIT 1
        ";

        $parameters = array(
            'email' => $email,
            'roles' => '%ROLE_CLIENT%'
        );

        $result = $this->db->query($sql, $parameters);

        $collection = $this->bindCollection($result);

        return $collection->first();
    }

    /**
     * @param RebalancerAction $rebalancerAction
     * @return Client
     */
    public function getClientByRebalancerAction(RebalancerAction $rebalancerAction)
    {
        $sql = "SELECT c.*, up.client_account_managed as accountManaged, up.estimated_income_tax as taxBracket FROM ".$this->table." c
                  LEFT JOIN ".self::TABLE_CLIENT_PORTFOLIO." cp ON cp.client_id = c.id
                  LEFT JOIN ".self::TABLE_CLIENT_PORTFOLIO_VALUE." cpv ON cpv.client_portfolio_id = cp.id
                  LEFT JOIN ".self::TABLE_REBALANCER_ACTION." ra ON ra.client_portfolio_value_id = cpv.id
                  LEFT JOIN ".self::TABLE_USER_PROFILE." up ON c.id = up.user_id
                WHERE ra.id = :rebalancerActionId AND cp.is_active = 1 AND c.roles LIKE :roles;
        ";

        $parameters = array(
            'rebalancerActionId' => $rebalancerAction->getId(),
            'roles' => '%ROLE_CLIENT%'
        );

        $result = $this->db->queryOne($sql, $parameters);
        $client = $this->bindObject($result);

        $rebalancerAction->setClient($client);

        return $client;
    }

    /**
     * Load stop THL value for client object
     * If client specific value less then ria global then set ria global value
     *
     * @param Client $client
     */
    public function loadStopTlhValue(Client $client)
    {
        $sql = "SELECT GREATEST(IFNULL(cs.stop_tlh_value, 0), IFNULL(rci.stop_tlh_value, 0)) as stop_tlh_value
                FROM {$this->table} c
                LEFT JOIN " . self::TABLE_USER_PROFILE . " cp ON (cp.user_id = c.id)
                LEFT JOIN " . self::TABLE_RIA_COMPANY_INFORMATION . " rci ON (cp.ria_user_id = rci.ria_user_id)
                LEFT JOIN " . self::TABLE_CLIENT_SETTINGS . " cs ON (cs.client_id = c.id)
                WHERE c.id = :client_id LIMIT 1";

        $pdo = $this->db->getPdo();
        $statement = $pdo->prepare($sql);
        $statement->execute(array('client_id' => $client->getId()));

        $data = $statement->fetch(\PDO::FETCH_ASSOC);
        $client->setStopTlhValue($data['stop_tlh_value']);
    }
}