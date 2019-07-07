<?php

namespace Model\Pas\Repository;

class ClientPortfolioRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_CLIENT_PORTFOLIO,
            'model_name' => 'Model\Pas\ClientPortfolio'
        );
    }

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $clientId
     * @return array|null
     */
    public function findOneByClientId($clientId)
    {
        return $this->findOneBy(array('client_id' => $clientId));
    }
}