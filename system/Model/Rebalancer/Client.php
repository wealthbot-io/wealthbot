<?php
namespace Model\Rebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Lib\Hash;

class Client extends Base {

	private $taxRate;

	private $oneTimeDistribution;

	private $scheduledDistribution = 0;

	private $distributionStartDate;

	private $totalDistribution;

    const ACCOUNT_MANAGED_ACCOUNT = 1;
    const ACCOUNT_MANAGED_HOUSEHOLD = 2;

    public function __construct() {
		parent::__construct();
	}

	public function taxRate($val = null) {
		if (!is_null($val)) {
			$this->taxRate = $val;
		}

		if (!isset($this->taxRate) || is_null($this->taxRate)) {
			$q = 'SELECT * FROM `user_profiles` WHERE `user_id` = ' . $this->id;
			$res = $this->db->q($q);
			$this->taxRate = isset($res[0]['estimated_income_tax']) ? $res[0]['estimated_income_tax'] : null;
		}

		return $this->taxRate;
	}

	/**
	 * gets one time distribution value based on system client account id
	 * (system_client_accounts)
	 *
	 * @param  [int] $systemAccountId [system_client_accounts.id]
	 * @param  [double] $val       [DI, for unit testing]
	 * @return [double]            [distribution value]
	 */
	public function oneTimeDistribution($systemAccountId, $val = null) {
		if (!is_null($val)) {
			$this->oneTimeDistribution = $val;
		}

		if (!isset($this->oneTimeDistribution) || is_null($this->oneTimeDistribution)) {
			$res = $this->__getOneTimeDistributionData($systemAccountId);
			$this->oneTimeDistribution = isset($res[0]['amount']) ? $res[0]['amount'] : null;
		}

		return $this->oneTimeDistribution;
	}

	public function scheduledDistribution($clientAccountId, $val = null) {
		if (!is_null($val)) {
			$this->scheduledDistribution = $val;
		}

		if (!isset($this->scheduledDistribution) || is_null($this->scheduledDistribution)) {
			$res = $this->__getClientDistributionData($clientAccountId);
			$this->scheduledDistribution = isset($res[0]['amount']) ? $res[0]['amount'] : null;
		}

		return $this->scheduledDistribution;
	}

	public function getTotalDistribution($systemAccountId, $clientAccountId) {
		return $this->oneTimeDistribution($systemAccountId) + $this->scheduledDistribution($clientAccountId);
	}

	public function distributionStartDate($clientAccountId, $val = null) {
		if (!is_null($val)) {
			$this->distributionStartDate = $val;
		}

		if (!isset($this->distributionStartDate) || is_null($this->distributionStartDate)) {
			$res = $this->__getClientDistributionData($clientAccountId);
			$this->distributionStartDate = isset($res[0]['start_transfer_date']) ? $res[0]['start_transfer_date'] : null;
		}

		return $this->distributionStartDate;
	}

	public function distributionFrequency($clientAccountId, $val = null) {
		if (!is_null($val)) {
			$this->distributionFrequency = $val;
		}

		if (!isset($this->distributionFrequency) || is_null($this->distributionFrequency)) {
			$res = $this->__getClientDistributionData($clientAccountId);
			$this->distributionFrequency = isset($res[0]['frequency']) ? $res[0]['frequency'] : null;
		}

		return $this->distributionFrequency;
	}

	public function getAllActiveSystemClientAccountsForRia($riaIds = null) {
		if (is_null($riaIds)) {
			return false;
		}

		$riaIds = implode(',', $riaIds);
		$q = 'SELECT * FROM `system_client_accounts`
			INNER JOIN `ce_models` ON `ce_models`.`owner_id` = `system_client_accounts`.`client_id`
			INNER JOIN `ria_company_information` ON `ria_company_information`.`portfolio_model_id`  = `ce_models`.`id`
			WHERE `ria_company_information`.`id`
			IN (' . $riaIds . ') ';

		$res = $this->db->q($q);
		$res = Hash::extract($res, '{n}.id');

		return $res;
	}

	public function getAllClientIdsForRia($riaIds = null) {
		if (is_null($riaIds)) {
			return false;
		}

		$riaIds = implode(',', $riaIds);

		$q = 'SELECT * FROM `ce_models`
			INNER JOIN `ria_company_information` ON `ria_company_information`.`portfolio_model_id`  = `ce_models`.`id`
			WHERE `ria_company_information`.`id`
			IN (' . $riaIds . ') ';

		$res = $this->db->q($q);
		$res = Hash::extract($res, '{n}.owner_id');

		return $res;
	}

    public function getAccountManaged()
    {
        if (null === $this->id) {
            return null;
        }

        $profile = $this->__getProfile();

        return isset($profile[0]['client_account_managed']) ? $profile[0]['client_account_managed'] : null;
    }

    public function getAllocationForSubclasses()
    {
        $currentAllocations = $this->getCurrentAllocations();
        $targetAllocations = $this->getTargetAllocations();

        $subclasses = array();

        foreach ($targetAllocations as $targetAllocation) {
            $subclassId = $targetAllocation['subclass_id'];

            $currentAllocationPercent = 0;
            if (isset($currentAllocations[$subclassId])) {
                $currentAllocationPercent = $currentAllocations[$subclassId]['percent'];
            }

            $subclass = new AssetSubclass();
            $subclass->id($subclassId);
            $subclass->targetPercent($targetAllocation['percent']);
            $subclass->currentPercent($currentAllocationPercent);

            $subclasses[] = $subclass;
        }

        return $subclasses;
    }

    public function getAllocationForAssetClasses()
    {
        $currentAllocations = $this->getCurrentAllocations();
        $targetAllocations = $this->getTargetAllocations();

        $assetClassAllocations = array();

        foreach ($targetAllocations as $targetAllocation) {
            $assetClassId = $targetAllocation['asset_class_id'];
            $subclassId = $targetAllocation['subclass_id'];
            $targetAllocationPercent = $targetAllocation['percent'];

            $currentAllocationPercent = 0;
            if (isset($currentAllocations[$subclassId])) {
                $currentAllocationPercent = $currentAllocations[$subclassId]['percent'];
            }

            if (isset($assetClassAllocations[$assetClassId])) {
                $assetClassAllocations[$assetClassId]['target'] += $targetAllocationPercent;
                $assetClassAllocations[$assetClassId]['current'] += $currentAllocationPercent;
            } else {
                $assetClassAllocations[$assetClassId] = array(
                    'target' => $targetAllocationPercent,
                    'current' => $currentAllocationPercent
                );
            }

        }

        $assetClasses = array();
        foreach ($assetClassAllocations as $assetClassId => $assetClassAllocation) {
            $assetClass = new AssetClass();
            $assetClass->id($assetClassId);
            $assetClass->currentPercent($assetClassAllocation['current']);
            $assetClass->targetPercent($assetClassAllocation['target']);

            $assetClasses[] = $assetClass;
        }

        return $assetClasses;
    }

    public function getTargetAllocations()
    {
        $q = '
          SELECT s.id AS subclass_id, s.asset_class_id as asset_class_id, SUM(ceme.percent) AS percent
          FROM ce_model_entities ceme
              LEFT JOIN subclasses s ON ceme.subclass_id = s.id
              LEFT JOIN client_portfolio cp ON cp.portfolio_id = ceme.model_id
          WHERE cp.client_id = '.$this->id().'
          GROUP BY ceme.subclass_id;
        ';

        $res = $this->db->q($q);

        $subclasses = array();
        foreach ($res as $dataRow) {
            $subclasses[$dataRow['subclass_id']] = $dataRow;
        }

        return $subclasses;
    }

    public function getCurrentAllocations()
    {
        $q = '
          SELECT sp.price AS price, SUM(pos.quantity) AS qty, SUM(pos.amount) AS amount, sa.subclass_id AS subclass_id, subc.asset_class_id AS asset_class_id
          FROM positions pos
              INNER JOIN system_client_accounts sca ON (pos.client_system_account_id = sca.id AND sca.client_id = '.$this->id().')
              INNER JOIN securities sec ON pos.security_id = sec.id
              INNER JOIN security_prices sp ON (sec.id = sp.security_id AND sp.is_current = 1)
              INNER JOIN securities_assignments sa ON sec.id = sa.security_id
              INNER JOIN subclasses subc ON (sa.subclass_id = subc.id AND subc.owner_id = '.$this->id().')
              INNER JOIN ce_model_entities ceme ON sa.id = ceme.security_assignment_id
              INNER JOIN ce_models cem ON ceme.model_id = cem.id
              INNER JOIN client_portfolio cp ON (cem.id = cp.portfolio_id AND cp.is_active = 1)
          WHERE pos.date = (SELECT MAX(date) FROM positions)
          GROUP BY sa.subclass_id;
        ';

        $res = $this->db->q($q);

        $values = array();
        $subclasses = array();
        $total = 0;
        foreach ($res as $dataRow) {
            $quantity = $dataRow['qty'] ? : $dataRow['amount'];
            $value = round($quantity * $dataRow['price'], 2);

            $total += $value;

            $values[$dataRow['subclass_id']] = array(
                'value' => $value,
                'asset_class_id' => $dataRow['asset_class_id']
            );
        }

        foreach ($values as $subclassId => $value) {
            $subclasses[$subclassId] = array(
                'subclass_id' => $subclassId,
                'asset_class_id' => $value['asset_class_id'],
                'percent' => round(($value['value'] / $total) * 100, 2)
            );
        }

        return $subclasses;
    }

	private function __getClientDistributionData($clientAccountId) {
		$q = 'SELECT * FROM `client_account_distribution` WHERE `client_account_distribution`.`account_id` = ' . $clientAccountId;
		$res = $this->db->q($q);
		return $res;
	}

	private function __getOneTimeDistributionData($systemAccountId) {
		$q = 'SELECT * FROM `one_time_distribution` WHERE `system_account_id` = ' . $systemAccountId;
		$res = $this->db->q($q);
		return $res;
	}

    private function __getProfile()
    {
        $q = 'SELECT * FROM user_profiles u WHERE u.user_id = '.$this->id;
        $res = $this->db->q($q);
        return $res;
    }
}

// $c = new Client();
// $c->getAllClientIdsForRia(array(31));