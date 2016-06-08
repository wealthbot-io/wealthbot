<?php
namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\SecurityCollection;
use Model\WealthbotRebalancer\Subclass;
use Model\WealthbotRebalancer\SubclassCollection;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class SubclassRepository extends BaseRepository {

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_SUBCLASS,
            'model_name' => 'Model\WealthbotRebalancer\Subclass'
        );
    }

    /**
     * @param $name
     * @param Portfolio $portfolio
     * @return Subclass
     */
    public function findByNameForPortfolio($name, Portfolio $portfolio)
    {
        $sql = "SELECT s.*, sat.name as account_type FROM ".self::TABLE_SUBCLASS." s
                  LEFT JOIN ".self::TABLE_SUBCLASS_ACCOUNT_TYPE." sat ON (s.account_type_id = sat.id)
                  INNER JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON (sa.subclass_id = s.id)
                  INNER JOIN ".self::TABLE_CE_MODEL." cem ON (cem.parent_id = sa.model_id AND cem.id = :portfolioId)
                  WHERE s.name = :subclassName
                  LIMIT 1
        ";

        $parameters = array(
            'subclassName' => $name,
            'portfolioId' => $portfolio->getId()
        );

        $results = $this->db->queryOne($sql, $parameters);

        if (false === $results) {
            return null;
        }

        return $this->bindObject($results);
    }

    public function bindAllocations(Portfolio $portfolio, Account $account = null)
    {
        $target = $this->getTargetAllocations($portfolio);
        $current = $this->getCurrentAllocations($portfolio, $account);

        $subclassCollection = $this->bindSubclassCollection($target, $portfolio->getSecurities());

        foreach ($current as $values)
        {
            $fund = isset($values['security']) ? $values['security'] : $values['muni'];

            /** @var Subclass $subclass */
            $subclass = $subclassCollection->get($fund['subclass_id']);

            $security = $subclass->getSecurity();
            if (isset($values['security']) && $security) {
                $security->setAmount($security->getAmount() + $values['security']['amount']);
                $security->setQty($security->getQty() + $values['security']['qty']);
            }

            $muni = $subclass->getMuniSecurity();
            if (isset($values['muni']) && $muni) {
                $muni->setAmount($muni->getAmount() + $values['muni']['amount']);
                $muni->setQty($muni->getQty() + $values['muni']['qty']);
            }
        }

        return $subclassCollection;
    }

    public function getTargetAllocations(Portfolio $portfolio)
    {
        $sql = "SELECT subc.id, subc.tolerance_band, subc.priority, s.id as security_id,
                  subc.asset_class_id as asset_class_id, ceme.percent AS target_allocation,
                  sm.id as muni_substitution_id, sm.name as muni_name, sm.symbol as muni_symbol,
                  sat.name as account_type
                FROM ".self::TABLE_CE_MODEL_ENTITY." ceme
                  LEFT JOIN ".self::TABLE_SUBCLASS." subc ON subc.id = ceme.subclass_id
                  LEFT JOIN ".self::TABLE_SUBCLASS_ACCOUNT_TYPE." sat ON (subc.account_type_id = sat.id)
                  LEFT JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON sa.id = ceme.security_assignment_id
                  LEFT JOIN ".self::TABLE_SECURITY." s ON s.id = sa.security_id
                  LEFT JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sam ON sam.id = ceme.muni_substitution_id
                  LEFT JOIN ".self::TABLE_SECURITY." sm ON sam.security_id = sm.id
                WHERE ceme.model_id = :portfolioId;
        ";

        $parameters = array(
            'portfolioId' => $portfolio->getId()
        );

        return $this->db->query($sql, $parameters);
    }

    public function getCurrentAllocations(Portfolio $portfolio, Account $account = null)
    {
        $sql = "SELECT pos.client_system_account_id, sa.security_id as security_id, sm.id as muni_id
                FROM ".self::TABLE_SUBCLASS." subc
                    INNER JOIN ".self::TABLE_CE_MODEL_ENTITY." ceme ON ceme.subclass_id = subc.id
                    INNER JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON sa.id = ceme.security_assignment_id
                    INNER JOIN ".self::TABLE_POSITION." pos ON pos.security_id = sa.security_id
                    LEFT JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sam ON sam.id = ceme.muni_substitution_id
                    LEFT JOIN ".self::TABLE_SECURITY." sm ON sam.security_id = sm.id
                WHERE ceme.model_id = :portfolioId";

        $parameters = array(
            'portfolioId' => $portfolio->getId()
        );

        if (null !== $account) {
            $sql .= " AND pos.client_system_account_id = :accountId";

            $parameters['accountId'] = $account->getId();
        }

        $sql .= " GROUP BY pos.client_system_account_id, pos.security_id";

        $positions = $this->db->query($sql, $parameters);

        $lastPositions = array();
        foreach ($positions as $position) {
            $lastPosition = $this->getLastPosition(
                $portfolio,
                $position['security_id'],
                $position['client_system_account_id']
            );
            if (!empty($lastPosition) &&
                ($lastPosition['status'] == Security::POSITION_STATUS_INITIAL ||
                $lastPosition['status'] == Security::POSITION_STATUS_IS_OPEN)) {

                $lastPositions[]['security'] = $lastPosition;
            }

            if ($position['muni_id']) {
                $lastMuniPosition = $this->getLastPosition(
                    $portfolio,
                    $position['muni_id'],
                    $position['client_system_account_id'],
                    true
                );
                if (!empty($lastMuniPosition) &&
                    ($lastMuniPosition['status'] == Security::POSITION_STATUS_INITIAL ||
                    $lastMuniPosition['status'] == Security::POSITION_STATUS_IS_OPEN)) {

                    $lastPositions[]['muni'] = $lastMuniPosition;
                }
            }
        }

        return $lastPositions;
    }

    public function getLastPosition(Portfolio $portfolio, $securityId, $clientSystemAccountId, $isMuni = false)
    {
        $securityField = $isMuni ? 'muni_substitution_id' : 'security_assignment_id';

        $sql = "SELECT p.quantity as qty, p.amount as amount, p.security_id as security_id,
                       subc.id as subclass_id, subc.asset_class_id as asset_class_id, p.status as status
                FROM ".self::TABLE_CE_MODEL_ENTITY." ceme
                  INNER JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON sa.id = ceme.".$securityField."
                  INNER JOIN ".self::TABLE_POSITION." p ON p.security_id = sa.security_id
                  INNER JOIN ".self::TABLE_SUBCLASS." subc ON subc.id = sa.subclass_id
                WHERE p.security_id = :security_id AND p.client_system_account_id = :client_system_account_id AND ceme.model_id = :portfolio_id
                ORDER BY p.date DESC
                LIMIT 1";

        $parameters = array(
            'security_id' => $securityId,
            'client_system_account_id' => $clientSystemAccountId,
            'portfolio_id' => $portfolio->getId()
        );

        return $this->db->queryOne($sql, $parameters);
    }

    protected function bindSubclassCollection(array $data, SecurityCollection $securityCollection)
    {
        $subclassCollection = new SubclassCollection();

        foreach ($data as $values) {
            $subclass = new Subclass();
            $subclass->loadFromArray($values);

            /** @var Security $security */
            $security = $securityCollection->get($values['security_id']);
            $security->setSubclass($subclass);
            $subclass->setSecurity($security);

            if (isset($values['muni_substitution_id']) && $values['muni_substitution_id']) {

                $sql = "SELECT sp.price FROM ".self::TABLE_SECURITY_PRICE." sp
                        WHERE sp.is_current = true AND sp.security_id = :security_id
                          ORDER BY sp.datetime DESC
                          LIMIT 1
                        ";

                $parameters = array(
                    'security_id' => $values['muni_substitution_id']
                );

                $securityPriceData = $this->db->query($sql, $parameters);

                $muni = new Security();
                $muni->setId($values['muni_substitution_id']);
                $muni->setSubclass($subclass);
                $muni->setIsPreferredBuy(true);
                $muni->setName($values['muni_name']);
                $muni->setSymbol($values['muni_symbol']);
                $muni->setPrice($securityPriceData[0]['price']);

                $subclass->setMuniSecurity($muni);
            }

            $subclassCollection->add($subclass);
        }

        return $subclassCollection;
    }

}