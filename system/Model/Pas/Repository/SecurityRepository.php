<?php

namespace Model\Pas\Repository;

use Model\Pas\Security;

class SecurityRepository extends BaseRepository
{
    /**
     * @return array
     */
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_SECURITY,
            'model_name' => 'Model\Pas\Security'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param string $symbol
     * @return array|null
     */
    public function findOneBySymbol($symbol)
    {
        return $this->findOneBy(array('symbol' => $symbol));
    }

    /**
     * @param Security $model
     * @return mixed
     */
    public function insert(Security $model)
    {
        return $this->fpdo->insertInto($this->table, array(
            'symbol' => $model->getSymbol(),
            'name'   => $model->getName(),
            'security_type_id' => $model->getSecurityTypeId()
        ))->execute();
    }

    /**
     * @param int $id
     * @param Security $model
     * @return mixed
     */
    public function update($id, Security $model)
    {
        return $this->fpdo->update($this->table, array(
            'name' => $model->getName(),
            'security_type_id' => $model->getSecurityTypeId()
        ), $id)->execute();
    }

    /**
     * @param Security $model
     * @return mixed
     */
    public function save(Security $model)
    {
        $result = $this->findOneBySymbol($model->getSymbol());

        return $result ? $this->update($result->getId(), $model) : $this->insert($model);
    }
}