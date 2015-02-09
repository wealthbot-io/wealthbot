<?php
namespace Model\Rebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Lib\Hash;
use \Model\Rebalancer\Account;

class Account extends Base {

	private $systemAccountId = null;

	private $accountNumber = null;

	private $taxable = true;

	private $roth = false;

	private $ira = false;

	private $activeEmployer = false;

	private $verified = true;

	private $sasCash;

	private $taxRate;

	private $totalCash;

	private $totalInMM;

	private $status;

	public function systemAccountId($val = null) {
		if (!is_null($val)) {
			$this->systemAccountId = $val;
		}

		return $this->systemAccountId;
	}

	public function verified($val = null) {
		if (!is_null($val)) {
			$this->verified = $val;
		}

		return $this->verified;
	}

	public function totalCash($val = null) {
		if (!is_null($val)) {
			$this->totalCash = $val;
		}

		return $this->totalCash;
	}

	public function status() {

	}

	public function taxable($val = null) {
		if (!is_null($val)) {
			$this->taxable = $val;
		}

		return $this->taxable;
	}

	public function roth($val = null) {
		if (!is_null($val)) {
			$this->roth = $val;
		}

		return $this->roth;
	}

	public function ira($val = null) {
		if (!is_null($val)) {
			$this->ira = $val;
		}

		return $this->ira;
	}

	public function activeEmployer($val = null) {
		if (!is_null($val)) {
			$this->activeEmployer = $val;
		}

		return $this->activeEmployer;
	}

	public function accountNumber($val = null) {
		if (!is_null($val)) {
			$this->accountNumber = $val;
		}

		return $this->accountNumber;
	}

	public function getTaxRate($accountId = null) {
		if (is_null($accountId)) {
			throw new \Exception('No account ID specified', 1);
		}
		return 20;
	}

	/**
	 * Gets system_client_accounts.id by financial account number
	 *
	 * @param  string $accountNumber custodian account number
	 * @return int                primary key of system_client_accounts
	 */
	public function getSystemAccountId($accountNumber = null) {
		$res = $this->__getSystemClientAccountData($accountNumber);
		$this->systemAccountId = $res[0]['id'] ?: null;
		return $this->systemAccountId;
	}

	public function getTotalCash($val = null) {
		if (!is_null($val)) {
			$this->totalCash = $val;
		}


		if (!isset($this->totalCash) || is_null($this->totalCash)) {
			$res = $this->__getClientAccountValueData();
			$this->totalCash = isset($res[0]['total_cash_in_account']) ?: null;

		}

		return $this->totalCash;
	}



	public function totalInMM($val = null) {
		if (!is_null($val)) {
			$this->totalInMM = $val;
		}


		if (!isset($this->gotalInMM) || is_null($this->totalInMM)) {
			$res = $this->__getClientAccountValueData();
			$this->totalInMM = isset($res[0]['total_cash_in_money_market']) ?: null;

		}

		return $this->totalinMM;
	}

	public function getCashBuffer($val = null) {
		if (!is_null($val)) {
			$this->cashBuffer = $val;
		}


		if (!isset($this->cashBuffer) || is_null($this->cashBuffer)) {
			$res = $this->__getClientAccountValueData();
			$this->cashBuffer = isset($res[0]['cash_buffer']) ?: null;
		}

		return $this->cashBuffer;
	}

	public function getSasCash($val = null) {
		if (!is_null($val)) {
			$this->sasCash = $val;
		}


		if (!isset($this->sasCash) || is_null($this->sasCash)) {
			$res = $this->__getClientAccountValueData();
			$this->sasCash = isset($res[0]['sas_cash']) ?: null;
		}

		return $this->sasCash;
	}

	public function getBillingCash($val = null) {
		if (!is_null($val)) {
			$this->billingCash = $val;
		}

		if (!isset($this->billingCash) || is_null($this->billingCash)) {
			$res = $this->__getClientAccountValueData();
			$this->billingCash = isset($res[0]['billing_cash']) ?: null;
		}

		return $this->billingCash;
	}

	public function getDistributionCash($systemAccountId = null, $clientAccountId = null) {
		if (is_null($systemAccountId)) {
			throw new \Exception('No account ID specified', 1);
		}
		$this->Client = new Client();

		$distribValue = $this->Client->getTotalDistribution($systemAccountId, $clientAccountId);
	}

	private function __getClientAccountValueData() {
		if (is_null($this->systemAccountId)) {
			throw new \Exception('No system account ID specified', 1);
		}

		$q = 'SELECT * FROM `client_account_values` WHERE `system_client_account_id` = ' . $this->systemAccountId;
		$res = $this->mySqlDB->q($q);

		return $res;
	}

	private function __getSystemClientAccountData($accountNumber = null) {
		if (is_null($accountNumber)) {
			throw new \Exception('No Account Number specified', 1);
		}

		$q = "SELECT * FROM `system_client_accounts` WHERE `account_number` = '" . $accountNumber . "'";
		$res = $this->mySqlDB->q($q);

		return $res;
	}

}

// $a = new Account();
// $a->systemAccountId(1);
// $a->getTotalCash();
