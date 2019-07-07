<?php

namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Ria;
use Model\WealthbotRebalancer\RiaCompanyInformation;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RiaCompanyInformationRepository extends BaseRepository
{

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_RIA_COMPANY_INFORMATION,
            'model_name' => 'Model\WealthbotRebalancer\RiaCompanyInformation'
        );
    }

    public function findOneByRia(Ria $ria)
    {
        $sql = "SELECT rci.id, rci.is_transaction_fees as useTransactionFees,
                        rci.transaction_amount as transactionMinAmount,
                        rci.transaction_amount_percent as transactionMinAmountPercent
                  FROM {$this->table} rci
                  WHERE rci.ria_user_id = :riaId";

        $params = array('riaId' => $ria->getId());

        $result = $this->db->queryOne($sql, $params);
        /** @var RiaCompanyInformation $riaCompanyInformation */
        $riaCompanyInformation = $this->bindObject($result);
        $riaCompanyInformation->setRia($ria);
        return $riaCompanyInformation;
    }

    public function txMin(\Model\WealthbotRebalancer\Ria $ria, $type = null, $val = null) {
        if (!is_null($val)) {
            $this->txMin = $val;
        }

        if (!isset($this->txMin) || is_null($this->txMin)) {
            $res = $this->__getRiaData($ria->getId());
            $this->txMin = !is_null($type) ? $res[0]['transaction_amount_'. $type] : $res[0]['transaction_amount'];
        }

        return $this->txMin;
    }

    private function __getRiaData($id) {
        $sql = 'SELECT * FROM `'. self::TABLE_RIA_COMPANY_INFORMATION. '` WHERE `ria_user_id` = :ria_id';
        $params = array('ria_id' => $id);
        $res = $this->db->query($sql, $params);

        return $res;
    }
}