<?php
namespace Model\WealthbotRebalancer\Repository;

use Model\WealthbotRebalancer\Account;
use Model\WealthbotRebalancer\ArrayCollection;
use Model\WealthbotRebalancer\Job;
use Model\WealthbotRebalancer\Lot;
use Model\WealthbotRebalancer\QueueItem;
use Model\WealthbotRebalancer\RebalancerQueue;
use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\TradeData;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class RebalancerQueueRepository extends BaseRepository {

    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_REBALANCER_QUEUE,
            'model_name' => 'Model\WealthbotRebalancer\QueueItem'
        );
    }

    public function save(QueueItem $item)
    {
        if (null === $item->getId()) {
            $sql = "INSERT INTO ".self::TABLE_REBALANCER_QUEUE." (lot_id, security_id, rebalancer_action_id, quantity, status, system_client_account_id, amount, subclass_id)
                      VALUES (:lot_id, :security_id, :rebalancer_action_id, :quantity, :status, :account_id, :amount, :subclass_id);
            ";

            $parameters = array(
                'lot_id' => $item->getLot() ? $item->getLot()->getId() : null,
                'security_id' => $item->getSecurity()->getId(),
                'rebalancer_action_id' => $item->getRebalancerActionId(),
                'quantity' => $item->getQuantity(),
                'status' => $item->getStatus(),
                'account_id' => $item->getAccount()->getId(),
                'amount' => $item->getAmount(),
                'subclass_id' => $item->getSubclass()->getId()
            );
        } else {
            $sql = "UPDATE ".self::TABLE_REBALANCER_QUEUE." SET quantity = :quantity, amount = :amount
                      WHERE id = :id;
            ";

            $parameters = array(
                'id' => $item->getId(),
                'quantity' => $item->getQuantity(),
                'amount' => $item->getAmount()
            );
        }

        $this->db->query($sql, $parameters);
    }

    public function delete(QueueItem $item)
    {
        $sql = "DELETE FROM ".self::TABLE_REBALANCER_QUEUE." WHERE id = :id";

        $this->db->query($sql, array('id' => $item->getId()));
    }

    public function findById($id)
    {

        $sql = "SELECT rq.*, ra.job_id FROM ".self::TABLE_REBALANCER_QUEUE." rq
                  LEFT JOIN ".self::TABLE_REBALANCER_ACTION." ra On ra.id = rq.rebalancer_action_id
                  WHERE rq.id = :id
                  ";

        $result = $this->db->queryOne($sql, array('id' => $id));

        if (empty($result)) {
            return null;
        }

        /** @var QueueItem $item */
        $item = $this->bindObject($result);

        if (isset($result['security_id'])) {
            $security = new Security();
            $security->setId($result['security_id']);

            $item->setSecurity($security);
        }

        if (isset($result['system_client_account_id'])) {
            $account = new Account();
            $account->setId($result['system_client_account_id']);

            $item->setAccount($account);
        }

        if (isset($result['lot_id'])) {
            $lot = new Lot();
            $lot->setId($result['lot_id']);

            $item->setLot($lot);
        }

        return $item;
    }

    public function getTradeDataCollectionForJob(Job $job)
    {
        $sql = "SELECT
                  rq.id as id,
                  s.id as security_id,
                  sca.id as account_id,
                  sca.account_number as account_number,
                  st.name as security_type,
                  rq.status as action,
                  p.quantity as position_quantity,
                  s.symbol as symbol,
                  SUM(rq.quantity) as quantity,
                  SUM(rq.amount) as amount,
                  IF(p.quantity = SUM(rq.quantity) OR (rq.status = '".QueueItem::STATUS_SELL."' AND rq.lot_id IS NULL), 'AS', 'S') as quantity_type
                FROM ".$this->table." rq
                  LEFT JOIN ".self::TABLE_SECURITY." s on s.id = rq.security_id
                  LEFT JOIN ".self::TABLE_SYSTEM_ACCOUNT." sca ON sca.id = rq.system_client_account_id
                  LEFT JOIN ".self::TABLE_SECURITY_TYPE." st ON st.id = s.security_type_id
                  LEFT JOIN ".self::TABLE_LOT." l ON l.id = rq.lot_id
                  LEFT JOIN ".self::TABLE_POSITION." p ON p.id = l.position_id
                  LEFT JOIN ".self::TABLE_REBALANCER_ACTION." ra On ra.id = rq.rebalancer_action_id
                WHERE ra.job_id = :jobId
                GROUP BY sca.id, s.id
                ORDER BY rq.id"
        ;

        $paramaters = array(
            'jobId' => $job->getId()
        );

        $results = $this->db->query($sql, $paramaters);

        $tradeDataCollection = new ArrayCollection();

        foreach ($results as $result) {
            $tradeData = new TradeData();

            $tradeData->loadFromArray($result);

            if ($tradeData->getAction() === TradeData::ACTION_SELL) {
                $vsps = $this->findVSPForTradeData($tradeData);

                $tradeData->setVsps($vsps);
            }

            $tradeData->setJobId($job->getId());
            $tradeDataCollection->add($tradeData);
        }

        return $tradeDataCollection;
    }

    public function findVSPForTradeData(TradeData $tradeData)
    {
        $sql = "
            SELECT 'VSP' as purchase, DATE_FORMAT(l.date,'%m%d%Y') as purchase_date, rq.quantity
            FROM ".$this->table." rq
              LEFT JOIN ".self::TABLE_LOT." l ON l.id = rq.lot_id
              LEFT JOIN ".self::TABLE_REBALANCER_ACTION." ra On ra.id = rq.rebalancer_action_id
            WHERE ra.job_id = :jobId AND rq.system_client_account_id = :accountId AND rq.security_id = :securityId
        ";

        $paramaters = array(
            'jobId' => $tradeData->getJobId(),
            'accountId' => $tradeData->getAccountId(),
            'securityId' => $tradeData->getSecurityId()
        );

        $results = $this->db->query($sql, $paramaters);

        return $results;
    }

//    /**
//     * @param array $data
//     * @return RebalancerQueue
//     */
//    protected function bindCollection(array $data)
//    {
//        $options = $this->getOptions();
//        $class = $options['model_name'];
//
//        $collection = new RebalancerQueue();
//
//        foreach ($data as $values) {
//            $element = new $class();
//            $element->loadFromArray($values);
//
//            $collection->add($element);
//        }
//
//        return $collection;
//    }
}