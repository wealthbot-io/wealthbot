<?php
namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\SecurityCollection;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class SecurityRepository extends BaseRepository {

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_SECURITY,
            'model_name' => 'Model\WealthbotRebalancer\Security'
        );
    }

    /**
     * Find one security by symbol
     *
     * @param $symbol
     * @return Security
     */
    public function findOneBySymbol($symbol)
    {
        return $this->findOneBy(array('symbol' => $symbol));
    }

    public function findSecuritiesByAccount(Account $account)
    {
        $sql = "SELECT s.id, s.name AS name, s.symbol AS symbol, sp.price AS price, pos.quantity AS qty,
                  IFNULL((SELECT 1 FROM ".self::TABLE_CE_MODEL." cem
                            LEFT JOIN ".self::TABLE_CLIENT_PORTFOLIO." cp ON (cp.portfolio_id = cem.id)
                            LEFT JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON sa.model_id = cem.id
                          WHERE sa.security_id = s.id AND cp.is_active = 1 AND cem.owner_id = :client_id
                        ), 0) as isPreferredBuy
                FROM ".self::TABLE_POSITION." pos
                  LEFT JOIN ".self::TABLE_SYSTEM_ACCOUNT." sca ON pos.client_system_account_id = sca.id
                  LEFT JOIN ".self::TABLE_SECURITY." s ON s.id = pos.security_id
                  LEFT JOIN ".self::TABLE_SECURITY_PRICE." sp ON (sp.security_id = s.id  AND sp.is_current = true)
                WHERE sca.id = :account_id AND pos.status = :position_status;
        ";

        $parameters = array(
            'account_id' => $account->getId(),
            'position_status' => Security::POSITION_STATUS_IS_OPEN,
            'client_id' => $account->getClient()->getId()
        );

        $result = $this->db->query($sql, $parameters);

        return $this->bindCollection($result);
    }

    public function findSecuritiesByPortfolio(Portfolio $portfolio)
    {
        $sql = "SELECT s.id as id, s.name as name, s.symbol as symbol, sp.price
                FROM ".self::TABLE_CE_MODEL_ENTITY." ceme
                  LEFT JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON sa.id = ceme.security_assignment_id
                  LEFT JOIN ".self::TABLE_SECURITY." s ON s.id = sa.security_id
                  LEFT JOIN ".self::TABLE_SECURITY_PRICE." sp ON (sp.security_id = s.id AND sp.is_current = true)
                WHERE ceme.model_id = :portfolioId
                ORDER BY s.id ASC";

        $parameters = array(
            'portfolioId' => $portfolio->getId()
        );

        $result = $this->db->query($sql, $parameters);

        $securityCollection = $this->bindCollection($result);

        return $securityCollection;
    }

    protected function bindCollection(array $data)
    {
        $collection = new SecurityCollection();

        foreach ($data as $values) {
            $element = new Security();
            $element->loadFromArray($values);

            $collection->add($element);
        }

        return $collection;
    }
}