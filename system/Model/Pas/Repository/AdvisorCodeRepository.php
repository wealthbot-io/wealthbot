<?php

namespace Model\Pas\Repository;

class AdvisorCodeRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_ADVISOR_CODE,
            'model_name' => 'Model\Pas\AdvisorCode'
        );
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
}