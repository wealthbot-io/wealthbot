<?php
namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\ArrayCollection;
use Model\WealthbotRebalancer\Client;
use Model\WealthbotRebalancer\Lot;
use Model\WealthbotRebalancer\LotCollection;
use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\Position;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\Subclass;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class LotRepository extends BaseRepository {

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_LOT,
            'model_name' => 'Model\WealthbotRebalancer\Lot'
        );
    }

    /**
     * Returns collection of lots for security and account by priority:
     * Short-term gain, Long-term loss, Long-term gain, Short-term gain
     *
     * @param Security $security
     * @param Account $account
     * @return LotCollection
     */
    public function findOrderedLots(Security $security, Account $account)
    {
        $sql = "SELECT a.*, IFNULL(a.realized_gain_loss, 0) AS realized_gain_or_loss FROM (
                  SELECT * FROM {$this->table} WHERE id IN (
                    SELECT max(id) FROM {$this->table} WHERE initial_lot_id IS NOT NULL GROUP BY initial_lot_id)
                  UNION (
                    SELECT l1.* FROM {$this->table} l1 LEFT JOIN {$this->table} l2 ON (l2.initial_lot_id = l1.id)
                    WHERE l1.initial_lot_id IS NULL AND l2.id IS NULL)
                ) a
                WHERE a.client_system_account_id = :account_id AND a.security_id = :security_id
                  AND (a.status = :status_initial OR a.status = :status_open) AND a.was_closed = 0
                ORDER BY a.amount/a.quantity DESC";

        $params = array(
            'account_id' => $account->getId(),
            'security_id' => $security->getId(),
            'status_initial' => Lot::LOT_INITIAL,
            'status_open' => Lot::LOT_IS_OPEN
        );

        $data = $this->db->query($sql, $params);
        foreach ($data as $key => $item) {
            if (isset($item['initial_lot_id'])) {
                $sql = "SELECT * FROM {$this->table} WHERE id = :id";
                $data[$key]['initial'] = $this->db->queryOne($sql, array('id' => $item['initial_lot_id']));
            }
        }

        /** @var LotCollection $lots */
        $lots = $this->bindCollection($data);

        $shortTermLoss = array();
        $longTermLoss = array();
        $longTermGain = array();
        $shortTermGain = array();

        /** @var Lot $lot */
        foreach ($lots as $lot) {
            if ($lot->isShortTerm()) {
                if ($lot->isLoss()) {
                    $shortTermLoss[$lot->getId()] = $lot;
                } else {
                    $shortTermGain[$lot->getId()] = $lot;
                }
            } else {
                if ($lot->isLoss()) {
                    $longTermLoss[$lot->getId()] = $lot;
                } else {
                    $longTermGain[$lot->getId()] = $lot;
                }
            }
        }

        $collection = new LotCollection();

        foreach ($shortTermLoss as $lot) {
            $collection->add($lot);
        }

        foreach ($longTermLoss as $lot) {
            $collection->add($lot);
        }

        foreach ($longTermGain as $lot) {
            $collection->add($lot);
        }

        foreach ($shortTermGain as $lot) {
            $collection->add($lot);
        }

        return $collection;
    }

    /*
     * TODO: We need to get lots only with status='initial' or with status='is_open' also?
     */
    public function findLotsBySubclass(Portfolio $portfolio, Subclass $subclass, Account $account = null)
    {
        $positions = $this->getPositionsByPortfolio($portfolio, $account, $subclass->getSecurity());

        $lotCollection = new LotCollection();

        foreach ($positions as $position) {
            $lots = $this->getLastPositionLots(
                $portfolio,
                $position['security_id'],
                $position['client_system_account_id']
            );

            foreach ($lots as $lot) {
                $lotCollection->add($lot);
            }

            if ($position['muni_id']) {
                $muniLots = $this->getLastPositionLots(
                    $portfolio,
                    $position['muni_id'],
                    $position['client_system_account_id'],
                    true
                );

                foreach ($muniLots as $muniLot) {
                    $lotCollection->add($muniLot);
                }
            }
        }

        return $lotCollection;
    }

    /**
     * Find one lot by account and security
     *
     * @param Account $account
     * @param Security $security
     * @return Lot
     */
    public function findLastLotByAccountAndSecurity(Account $account, Security $security)
    {
        $lots = $this->findLotsByAccountAndSecurity($account, $security);

        return $lots->first();
    }

    /**
     * @param Account $account
     * @param Security $security
     * @return LotCollection
     */
    public function findLotsByAccountAndSecurity(Account $account, Security $security)
    {
        $portfolio = $account->getClient()->getPortfolio();

        $positions = $this->getPositionsByPortfolio($portfolio, $account, $security);

        $lotCollection = new LotCollection();

        foreach ($positions as $position) {
            $lots = $this->getLastPositionLots(
                $portfolio,
                $position['security_id'],
                $position['client_system_account_id']
            );

            foreach ($lots as $lot) {
                $lotCollection->add($lot);
            }

            if ($position['muni_id']) {
                $muniLots = $this->getLastPositionLots(
                    $portfolio,
                    $position['muni_id'],
                    $position['client_system_account_id'],
                    true
                );

                foreach ($muniLots as $muniLot) {
                    $lotCollection->add($muniLot);
                }
            }
        }

        return $lotCollection;
    }

    public function getPositionsByPortfolio(Portfolio $portfolio, Account $account = null, Security $security = null)
    {
        $sql = "SELECT pos.client_system_account_id, sa.security_id as security_id, sm.id as muni_id
                FROM ".self::TABLE_SUBCLASS." subc
                    INNER JOIN ".self::TABLE_CE_MODEL_ENTITY." ceme ON ceme.subclass_id = subc.id
                    INNER JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON sa.id = ceme.security_assignment_id
                    INNER JOIN ".self::TABLE_POSITION." pos ON pos.security_id = sa.security_id
                    LEFT JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sam ON sam.id = ceme.muni_substitution_id
                    LEFT JOIN ".self::TABLE_SECURITY." sm ON sam.security_id = sm.id
                WHERE ceme.model_id = :portfolioId
        ";

        $parameters = array(
            'portfolioId' => $portfolio->getId()
        );

        if (null !== $account) {
            $sql .= " AND pos.client_system_account_id = :accountId";

            $parameters['accountId'] = $account->getId();
        }

        if (null !== $security) {
            $sql .= " AND (pos.security_id = :securityId OR sm.id = :securityId)";

            $parameters['securityId'] = $security->getId();
        }

        $sql .= " GROUP BY pos.client_system_account_id, pos.security_id";

        return $this->db->query($sql, $parameters);
    }

    public function getLastPositionLots(Portfolio $portfolio, $securityId, $clientSystemAccountId, $isMuni = false)
    {
        $securityField = $isMuni ? 'muni_substitution_id' : 'security_assignment_id';

        $sql = "SELECT p.id, p.security_id as security_id, p.status as status, p.client_system_account_id
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

        $lastPosition = $this->db->queryOne($sql, $parameters);

        return $this->findLotsForPosition($lastPosition, $isMuni);
    }

    private function findLotsForPosition(array $position, $isMuni = false)
    {
        $sql = "SELECT l.*, ".($isMuni ? 1 : 0)." as is_muni FROM ".self::TABLE_LOT." l
                  WHERE l.was_closed = false AND
                        l.client_system_account_id = :accountId AND
                        l.security_id = :securityId AND
                        l.position_id = :positionId AND
                        (l.status = :statusInitial OR l.status = :statusOpen)
        ";

        $parameters = array(
            'accountId' => $position['client_system_account_id'],
            'securityId' => $position['security_id'],
            'positionId' => $position['id'],
            'statusInitial' => LOT::LOT_INITIAL,
            'statusOpen' => LOT::LOT_IS_OPEN
        );

        $results = $this->db->query($sql, $parameters);

        $lotCollection = $this->bindCollection($results);

        return $lotCollection;
    }

    /**
     * Find initial lot by lot
     * If lot in argument is initial then returns it
     *
     * @param Lot $lot
     * @return Lot
     */
    public function getInitialLot(Lot $lot)
    {
        if ($lot->isInitial()) {
            return $lot;
        }

        $params = array(
            'id' => $lot->getInitialLotId()
        );

        return $this->findOneBy($params);
    }

    /**
     * Get sum of client losses for year
     *
     * @param Client $client
     * @param string $year
     * @return float
     */
    public function getClientLossesSumForYear(Client $client, $year)
    {
        $sql = "SELECT SUM(l.realized_gain_loss) as losses_sum FROM {$this->table} l
                LEFT JOIN " . self::TABLE_POSITION . " p ON (l.position_id = p.id)
                LEFT JOIN " . self::TABLE_SYSTEM_ACCOUNT . " a ON (p.client_system_account_id = a.id)
                WHERE a.client_id = :client_id
                AND l.date BETWEEN :date_from AND :date_to
                AND l.status = :status_closed AND l.realized_gain_loss < 0";

        $params = array(
            'client_id' => $client->getId(),
            'status_closed' => Lot::LOT_CLOSED,
            'date_from' => $year . "-01-01",
            'date_to' => $year . "-12-31"
        );

        $lossesSum = 0;

        $data = $this->db->queryOne($sql, $params);
        if (isset($data['losses_sum'])) {
            $lossesSum = $data['losses_sum'];
        }

        return $lossesSum;
    }
}