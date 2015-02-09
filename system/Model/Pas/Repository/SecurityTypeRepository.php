<?php

namespace Model\Pas\Repository;

class SecurityTypeRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_SECURITY_TYPE,
            'model_name' => 'Model\Pas\SecurityType'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function findOneByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }
}