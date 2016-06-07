<?php

namespace Model\Pas\Repository;

class CustodianRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_CUSTODIAN,
            'model_name' => 'Model\Pas\Custodian'
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