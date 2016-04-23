<?php

namespace Model\Pas\Repository;

class RebalancerQueueRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_REBALANCER_QUEUE,
            'model_name' => 'Model\Pas\RebalancerQueue'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }
}