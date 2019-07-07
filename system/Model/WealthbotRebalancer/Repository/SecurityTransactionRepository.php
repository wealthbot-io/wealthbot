<?php

namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\SecurityTransaction;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class SecurityTransactionRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_SECURITY_TRANSACTION,
            'model_name' => 'Model\WealthbotRebalancer\SecurityTransaction'
        );
    }

    /**
     * @param Portfolio $portfolio
     * @param Security $security
     * @return SecurityTransaction
     */
    public function findOneByPortfolioAndSecurity(Portfolio $portfolio, Security $security)
    {
        $sql = "
            SELECT st.* FROM {$this->table} st
              LEFT JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON st.security_assignment_id =  sa.id
              INNER JOIN ce_models cem ON (cem.parent_id = sa.model_id AND cem.id = :model_id)
            WHERE sa.security_id = :security_id
        ";

        $params = array(
            'security_id' => $security->getId(),
            'model_id'    => $portfolio->getId()
        );


        $result = $this->db->queryOne($sql, $params);
        if (!$result) {
            return null;
        }

        return $this->bindObject($result);
    }
} 