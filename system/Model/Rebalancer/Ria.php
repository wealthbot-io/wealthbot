<?php
namespace Model\Rebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Lib\Hash;

class Ria extends Base {

	private $redemptionFee;

	private $billingFee;

	private $accountManaged;

	private $rebalFrequency;

	private $rebalToleranceBands;

	private $useQualifiedModel;

	private $useTransactionFees;

	private $txMin;

	private $muniSub;

	private $clientTaxBracket;

    const REBALANCED_FREQUENCY_QUARTERLY = 1;
    const REBALANCED_FREQUENCY_SEMI_ANNUALLY = 2;
    const REBALANCED_FREQUENCY_ANNUALLY = 3;
    const REBALANCED_FREQUENCY_TOLERANCE_BANDS = 4;

    public function __construct() {
		parent::__construct();
	}

    private function __getRiaData()
    {
        $q = 'SELECT * FROM `ria_company_information` WHERE `ria_user_id` = ' . $this->id;
        return $this->db->q($q);
    }

	public function redemptionFee($val = null) {
		if (!is_null($val)) {
			$this->redemptionFee = $val;
		}

		return $this->redemptionFee;
	}

	public function billingFee($val = null) {
		if (!is_null($val)) {
			$this->billingFee = $val;
		}

		if (!isset($this->billingFee) || is_null($this->billingFee)) {
			$q = 'SELECT * FROM `ria_company_information` WHERE `ria_user_id` = ' . $this->id;
			$res = $this->db->q($q);
			$this->billingFee = $res[0]['minimum_billing_fee'];
		}

		return $this->billingFee;
	}

	public function accountManaged($val = null) {
		if (!is_null($val)) {
			$this->accountManaged = $val;
		}

		if (!isset($this->accountManaged) || is_null($this->accountManaged)) {
			$res = $this->__getRiaData();
			$this->accountManaged = $res[0]['account_managed'];
		}

		//constants defined in Wealthbot\RiaBundle\Entity\RiaCompanyInformation
		// 1 - account level; 2 - household; 3 - client by client
		return $this->accountManaged;
	}

	public function rebalFrequency($val = null) {
		if (!is_null($val)) {
			$this->rebalFrequency = $val;
		}

		if (!isset($this->rebalFrequency) || is_null($this->rebalFrequency)) {
			$res = $this->__getRiaData();
			$this->rebalFrequency = $res[0]['rebalanced_frequency'];
		}

		return $this->rebalFrequency;
	}

	public function rebalMethod($val = null) {
		if (!is_null($val)) {
			$this->rebalMethod = $val;
		}

		if (!isset($this->rebalMethod) || is_null($this->rebalMethod)) {
			$res = $this->__getRiaData();
			$this->rebalMethod = $res[0]['rebalanced_method'];
		}

		return $this->rebalMethod;
	}

	public function useQualifiedModel($val = null) {
		if (!is_null($val)) {
			$this->useQualifiedModel = $val;
		}

		if (!isset($this->useQualifiedModel) || is_null($this->useQualifiedModel)) {
			$res = $this->__getRiaData();
			$this->useQualifiedModel = $res[0]['is_use_qualified_models'];
		}

		return $this->useQualifiedModel;
	}

	public function useTransactionFees($val = null) {
		if (!is_null($val)) {
			$this->useTransactionFees = $val;
		}

		if (!isset($this->useTransactionFees) || is_null($this->useTransactionFees)) {
			$res = $this->__getRiaData();
			$this->useTransactionFees = $res[0]['is_transaction_fees'];
		}

		return $this->useTransactionFees;
	}

	public function txMin($type = null, $val = null) {
		if (!is_null($val)) {
			$this->txMin = $val;
		}

		if (!isset($this->txMin) || is_null($this->txMin)) {
			$res = $this->__getRiaData();
			$this->txMin = !is_null($type) ? $res[0]['transaction_amount_'. $type] : $res[0]['transaction_amount'];
		}

		return $this->txMin;
	}

	public function muniSub($val = null) {
		if (!is_null($val)) {
			$this->muniSub = $val;
		}

		if (!isset($this->muniSub) || is_null($this->muniSub)) {
			$res = $this->__getRiaData();
			$this->muniSub = isset($res[0]['use_municipal_bond']) ? $res[0]['use_municipal_bond'] : null;
		}

		return $this->muniSub;
	}

	/**
	 * Returns array of id's of all active ria's
	 *
	 * @return [array] [array of id's of all active ria's]
	 */
	public function getAllActiveIds() {
		$q = 'SELECT * FROM `ria_company_information` WHERE `activated` = 1';
		$res = $this->db->q($q);

		$res = Hash::extract($res, '{n}.id');
		return $res;
	}

	public function clientTaxBracket($val = null) {
		if (!is_null($val)) {
			$this->clientTaxBracket = $val;
		}

		if (!isset($this->clientTaxBracket) || is_null($this->clientTaxBracket)) {
			$res = $this->__getRiaData();
			$this->clientTaxBracket = isset($res[0]['clients_tax_bracket']) ? $res[0]['clients_tax_bracket'] : null;
		}

		return $this->clientTaxBracket;
	}


}

// $ria = new Ria();
// $ria->getAllActiveIds();