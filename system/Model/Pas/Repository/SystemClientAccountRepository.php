<?php

namespace Model\Pas\Repository;

class SystemClientAccountRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_SYSTEM_CLIENT_ACCOUNT,
            'model_name' => 'Model\Pas\SystemClientAccount'
        );
    }

    /**
     * @return array|null
     */
    public function findAll()
    {
        return $this->findBy(array('status' => 'active'), null, null, 'account_number');
    }

    /**
     * Get account one by account number
     *
     * @param $accountNumber
     * @return int|bool
     */
    public function findOneByAccountNumber($accountNumber)
    {
        return $this->findOneBy(array('account_number' => $accountNumber));
    }

    /**
     * @return array
     */
    public function getAllGroupByClient()
    {
        $clients = array();

        foreach ($this->findAll() as $account) {
            $clients[$account->getClientId()][] = $account;
        }

        return $clients;
    }
}