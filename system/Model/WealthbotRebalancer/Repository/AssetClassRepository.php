<?php
//namespace Model\WealthbotRebalancer\Repository;
//
//use Model\WealthbotRebalancer\Account;
//use Model\WealthbotRebalancer\AssetClass;
//use Model\WealthbotRebalancer\AssetClassCollection;
//use Model\WealthbotRebalancer\Portfolio;
//use Model\WealthbotRebalancer\Security;
//use Model\WealthbotRebalancer\SecurityCollection;
//use Model\WealthbotRebalancer\Subclass;
//
//require_once(__DIR__ . '/../../../AutoLoader.php');
//\AutoLoader::registerAutoloader();
//
//class AssetClassRepository extends BaseRepository {
//
//    protected function getOptions()
//    {
//        return array(
//            'table_name' => self::TABLE_ASSET_CLASS,
//            'model_name' => 'Model\WealthbotRebalancer\AssetClass'
//        );
//    }
//
//    public function bindAllocations(Portfolio $portfolio)
//    {
//        $target = $this->getTargetAllocations($portfolio);
//        $current = $this->getCurrentAllocations($portfolio);
//
//        $assetClassCollection = $this->bindCollection($target, $portfolio->getSecurities());
//
//        $totalAmount = 0;
//        foreach ($current as $values)
//        {
//            /** @var AssetClass $assetClass */
//            $assetClass = $assetClassCollection->get($values['asset_class_id']);
//            /** @var Subclass $subclass */
//            $subclass = $assetClass->getSubclasses()->get($values['subclass_id']);
//
//            $subclass->getSecurity()->setAmount($values['amount']);
//            $subclass->getSecurity()->setQty($values['qty']);
//
//            $totalAmount += $values['amount'];
//        }
//
//        return $assetClassCollection;
//    }
//
//    private function getTargetAllocations(Portfolio $portfolio)
//    {
//        $sql = "SELECT subc.id, subc.tolerance_band, subc.priority, s.id as security_id,
//                  subc.asset_class_id as asset_class_id, ceme.percent AS target_allocation
//                FROM ".self::TABLE_CE_MODEL_ENTITY." ceme
//                  LEFT JOIN ".self::TABLE_CE_MODEL." cem ON cem.id = ceme.model_id
//                  LEFT JOIN ".self::TABLE_SUBCLASS." subc ON ceme.subclass_id = subc.id
//                  INNER JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON sa.subclass_id = subc.id
//                  LEFT JOIN ".self::TABLE_SECURITY." s ON s.id = sa.security_id
//                WHERE cem.id = :portfolioId;
//        ";
//
//        $parameters = array(
//            'portfolioId' => $portfolio->getId()
//        );
//
//        return $this->db->query($sql, $parameters);
//    }
//
//    private function getCurrentAllocations(Portfolio $portfolio)
//    {
//        $sql = "SELECT subc.asset_class_id, subc.id as subclass_id, sec.id as security_id, pos.amount as amount, pos.quantity as qty
//                FROM ".self::TABLE_SUBCLASS." subc
//                  INNER JOIN ".self::TABLE_SECURITY_ASSIGNMENT." sa ON sa.subclass_id = subc.id
//                  INNER JOIN ".self::TABLE_SECURITY." sec ON sec.id = sa.security_id
//                  INNER JOIN ".self::TABLE_CE_MODEL." cem ON cem.id = sa.model_id
//                  INNER JOIN ".self::TABLE_POSITION." pos ON (pos.security_id = sec.id AND pos.status = 2)
//                  INNER JOIN ".self::TABLE_SECURITY_PRICE." sp ON (sp.security_id = sec.id AND sp.is_current = true)
//                  INNER JOIN ".self::TABLE_SYSTEM_ACCOUNT." sca ON (
//                        sca.id = pos.client_system_account_id AND
//                        sca.closed IS NULL AND
//                        sca.status != :statusOpen AND
//                        sca.status != :statusClosed
//                    )
//                WHERE cem.id = :portfolioId;
//
//        ";
//
//        $parameters = array(
//            'portfolioId' => $portfolio->getId(),
//            'statusOpen' => Account::STATUS_ACTIVE,
//            'statusClosed' => Account::STATUS_CLOSED
//        );
//
//        return $this->db->query($sql, $parameters);
//    }
//
//    protected function bindCollection(array $data, SecurityCollection $securityCollection)
//    {
//        $assetClassCollection = new AssetClassCollection();
//
//        foreach ($data as $values) {
//            $assetClass = $assetClassCollection->get($values['asset_class_id']);
//
//            if (!$assetClassCollection->get($values['asset_class_id'])) {
//                $assetClass = new AssetClass();
//                $assetClass->setId($values['asset_class_id']);
//            }
//
//            $subclass = new Subclass();
//            $subclass->loadFromArray($values);
//
//            /** @var Security $security */
//            $security = $securityCollection->get($values['security_id']);
//            $security->setAssetClass($assetClass);
//            $security->setSubclass($subclass);
//
//            $subclass->setSecurity($security);
//
//            $assetClass->addSubclass($subclass);
//
//            $assetClassCollection->add($assetClass);
//        }
//
//        return $assetClassCollection;
//    }
//
//}