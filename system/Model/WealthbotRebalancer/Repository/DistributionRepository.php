<?php

namespace Model\WealthbotRebalancer\Repository;


use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\Distribution;

class DistributionRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_DISTRIBUTION,
            'model_name' => 'Model\WealthbotRebalancer\Distribution'
        );
    }

    /**
     * @param Account $account
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTo
     * @return int
     */
    public function findScheduledDistribution(Account $account, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $sql = "SELECT * FROM " . $this->table . "
                WHERE type = :type AND
                system_client_account_id = :account_id AND
                transfer_date >= :date_from AND transfer_date <= :date_to
                LIMIT 1";

        $parameters = array(
            'type' => Distribution::TYPE_SCHEDULED,
            'account_id' => $account->getId(),
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to' => $dateTo->format('Y-m-d')
        );

        $distributions = $this->db->query($sql, $parameters);
        if (!count($distributions)) {
            return 0;
        }

        return $distributions[0]['amount'];
    }

    /**
     * @param Account $account
     * @return int
     */
    public function findOneTimeDistribution(Account $account)
    {
        $sql = "SELECT * FROM " . $this->table . "
                WHERE type = :type AND
                system_client_account_id = :account_id";

        $parameters = array(
            'type' => Distribution::TYPE_ONE_TIME,
            'account_id' => $account->getId(),
        );

        $distributions = $this->db->query($sql, $parameters);

        $sum = 0;
        foreach ($distributions as $distribution) {
            $sum += $distribution['amount'];
        }

        return $sum;
    }
} 