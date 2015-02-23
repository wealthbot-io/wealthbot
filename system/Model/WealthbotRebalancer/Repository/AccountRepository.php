<?php
namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\AccountCollection;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\RebalancerAction;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class AccountRepository extends BaseRepository
{
    const STATUS_BILL_APPROVED = 3;


    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_SYSTEM_ACCOUNT,
            'model_name' => 'Model\WealthbotRebalancer\Account'
        );
    }

    /**
     * Find one account by account number
     *
     * @param $accountNumber
     * @return Account
     */
    public function findOneByAccountNumber($accountNumber)
    {
        return $this->findOneBy(array('account_number' => $accountNumber));
    }

    /**
     * Find system client accounts by client
     * With approved bill amount
     *
     * @param Client $client
     * @return AccountCollection
     */
    public function findClientAccounts(Client $client)
    {
        $sql = "SELECT a.*, IFNULL(SUM(bi.feeBilled), 0) as billing_cash
                FROM " . self::TABLE_SYSTEM_ACCOUNT . " a
                LEFT JOIN " . self::TABLE_BILL_ITEM . " bi ON (bi.system_account_id = a.id AND bi.status = :status)
                WHERE a.client_id = :client_id
                GROUP BY a.id";

        $parameters = array(
            'client_id' => $client->getId(),
            'status'    => self::STATUS_BILL_APPROVED
        );

        $result = $this->db->query($sql, $parameters);
        $collection = $this->bindCollection($result);

        foreach ($collection as $item) {
            $item->setClient($client);
        }

        return $collection;
    }

    public function findAccountById($accountId)
    {
        $sql = "SELECT a.*, IFNULL(SUM(bi.feeBilled), 0) as billing_cash
                FROM " . self::TABLE_SYSTEM_ACCOUNT . " a
                LEFT JOIN " . self::TABLE_BILL_ITEM . " bi ON (bi.system_account_id = a.id AND bi.status = :status)
                WHERE a.id = :accountId
                GROUP BY a.id";

        $parameters = array(
            'accountId' => $accountId,
            'status' => self::STATUS_BILL_APPROVED
        );

        $result = $this->db->query($sql, $parameters);

        if (!isset($result[0])) {
            return null;
        }

        $account = new Account();
        $account->loadFromArray($result[0]);

        return $account;
    }

    public function getAccountsByRebalancerAction(RebalancerAction $rebalancerAction)
    {
        $client = $rebalancerAction->getClient();

        if ($client->isHouseholdLevelRebalancer()) {
            $collection = $this->findClientAccounts($client);
        } else {
            $account = $this->findAccountById($rebalancerAction->getAccountId());
            $account->setClient($client);

            $collection = new AccountCollection();
            $collection->add($account);
        }

        return $collection;
    }

    /**
     * Get account data
     *
     * @param Account $account
     * @return array
     */
    public function getAccountValues(Account $account)
    {
        $sql = "SELECT * FROM " . self::TABLE_CLIENT_ACCOUNT_VALUES . " WHERE system_client_account_id = :account_id
                ORDER BY date DESC
                LIMIT 1";

        return $this->db->queryOne($sql, array('account_id' => $account->getId()));
    }

    /**
     * Get account values from db and update account object
     *
     * @param Account $account
     */
    public function loadAccountValues(Account $account)
    {
        $data = $this->getAccountValues($account);

        if ($data) {
            $account->setTotalCash(isset($data['total_cash_in_account']) ? $data['total_cash_in_account'] : 0);
            $account->setSasCash(isset($data['sas_cash']) ? $data['sas_cash'] : 0);
            $account->setBillingCash(isset($data['billing_cash']) ? $data['billing_cash'] : 0);
            $account->setCashBuffer(isset($data['cash_buffer']) ? $data['cash_buffer'] : 0);
        }
    }
}