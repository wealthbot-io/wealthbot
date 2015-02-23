<?php
namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Lot;
use Model\WealthbotRebalancer\Subclass;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class JobRepository extends BaseRepository {

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_JOB,
            'model_name' => 'Model\WealthbotRebalancer\Job'
        );
    }

    public function findAllCurrentRebalancerJob()
    {
        $sql = "SELECT * FROM ".self::TABLE_JOB." j
                  WHERE j.finished_at IS NULL AND j.name = :jobName AND j.started_at < now()
        ";

        $parameters = array(
            'jobName' => Job::JOB_NAME_REBALANCER
        );

        $result = $this->db->query($sql, $parameters);

        return $this->bindCollection($result);
    }


    public function finish(Job $job)
    {
        $sql = "UPDATE ".self::TABLE_JOB." SET finished_at = NOW() WHERE id = :jobId";

        $parameters = array(
            'jobId' => $job->getId()
        );

        $this->db->query($sql, $parameters);
    }
}