<?php

namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Ria;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RiaRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_USER,
            'model_name' => 'Model\WealthbotRebalancer\Ria'
        );
    }

    /**
     * Find one ria by client
     *
     * @param Client $client
     * @return Ria
     */
    public function findOneByClient(Client $client)
    {
        $sql = "SELECT r.*, rci.is_tax_loss_harvesting as isTlhEnabled, rci.tax_loss_harvesting_minimum as minTlh,
                       rci.tax_loss_harvesting_minimum_percent as minTlhPercent, rci.tax_loss_harvesting as minRelationshipValue,
                       rci.tax_loss_harvesting_percent as clientTaxBracket, rci.use_municipal_bond as is_use_municipal_bond,
                       rci.is_transaction_fees as useTransactionFees, rci.transaction_amount as transactionAmount,
                       rci.transaction_amount_percent as transactionAmountPercent
                FROM {$this->table} r
                LEFT JOIN " . self::TABLE_USER_PROFILE . " cp ON (cp.ria_user_id = r.id)
                LEFT JOIN " . self::TABLE_RIA_COMPANY_INFORMATION . " rci ON (rci.ria_user_id = r.id)
                WHERE cp.user_id = :client_id";

        $params = array('client_id' => $client->getId());
        $result = $this->db->query($sql, $params);
        $collection = $this->bindCollection($result);

        return $collection->first();
    }
}