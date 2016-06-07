<?php

namespace Model\Pas\Repository;

class ClosingMethodRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_CLOSING_METHOD,
            'model_name' => 'Model\Pas\ClosingMethod'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function findOneByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }
}