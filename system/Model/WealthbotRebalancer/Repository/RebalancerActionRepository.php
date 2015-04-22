<?php
namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\ArrayCollection;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Portfolio;
use Model\WealthbotRebalancer\RebalancerAction;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RebalancerActionRepository extends BaseRepository {

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_REBALANCER_ACTION,
            'model_name' => 'Model\WealthbotRebalancer\RebalancerAction'
        );
    }

    public function bindForJob(Job $job)
    {
        $sql = "SELECT ra.id as id, cav.system_client_account_id as accountId, cpv.client_portfolio_id as portfolioId
                FROM {$this->table} ra
                    LEFT JOIN ".self::TABLE_CLIENT_ACCOUNT_VALUES." cav ON cav.id = ra.client_account_value_id
                    LEFT JOIN ".self::TABLE_CLIENT_PORTFOLIO_VALUE." cpv ON cpv.id = ra.client_portfolio_value_id
                WHERE ra.job_id = :jobId
        ";

        $parameters = array(
            'jobId' => $job->getId()
        );

        $results = $this->db->query($sql, $parameters);

        $collection = $this->bindCollection($results);

        foreach ($collection as $item) {
            $item->setJob($job);
        }

        return $collection;
    }

    public function findByPortfolioAndJob(Portfolio $portfolio, Job $job)
    {
        $sql = "SELECT ra.*, j.rebalance_type as status, cp.portfolio_id FROM ".$this->table." ra
                    LEFT JOIN ".self::TABLE_CLIENT_PORTFOLIO_VALUE." cpv ON cpv.id = ra.client_portfolio_value_id
                    LEFT JOIN ".self::TABLE_CLIENT_PORTFOLIO." cp ON cp.id = cpv.client_portfolio_id
                    LEFT JOIN ".self::TABLE_JOB." j ON j.id = ra.job_id
                WHERE cp.portfolio_id = :portfolioId AND ra.job_id = :jobId
        ";

        $parameters = array(
            'portfolioId' => $portfolio->getId(),
            'jobId' => $job->getId()
        );

        $results = $this->db->query($sql, $parameters);
        $collection = $this->bindCollection($results);

        foreach ($collection as $item) {
            $item->setJob($job);
        }

        return $collection;
    }

    public function saveStatus(RebalancerAction $rebalancerAction)
    {
        $sql = "UPDATE ".self::TABLE_REBALANCER_ACTION." SET status = :status
                    WHERE id = :id";

        $parameters = array(
            'id' => $rebalancerAction->getId(),
            'status' => $rebalancerAction->getStatus()
        );

        $this->db->query($sql, $parameters);
    }
}