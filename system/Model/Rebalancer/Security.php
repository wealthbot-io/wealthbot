<?php
namespace Model\Rebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Lib\Hash;

class Security extends Base {

	/**
	 * Security symbol
	 *
	 * @var string
	 */
	private $symbol;

	/**
	 * Security description
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Security type
	 *
	 * @var string
	 */
	private $type;

	/**
	 * How many of this security do we own?
	 *
	 * @var float
	 */
	private $qtyOwned;

	/**
	 * Total value in account
	 *
	 * @var float
	 */
	private $value;

	/**
	 * Weight (percetange) of security in account
	 * @var float
	 */
	private $weight = 0;

	/**
	 * Whether or not this security is to be used in the financial model
	 * @var boolean
	 */
	private $preferred = true;

	/**
	 * Cost basis for security
	 * @var float
	 */
	private $costBasis;

	/**
	 * How much is the total account value?s
	 *
	 * @var float
	 */
	private $totalAccountValue;

	/**
	 * Penalty period expiration date
	 * yyyy-mm-dd
	 *
	 * @var float
	 */
	private $penaltyExpiration;

	/**
	 * Penalty interval in days
	 *
	 * @var int
	 */
	private $penaltyInterval;

	/**
	 * required to buy at least that many shares
	 */
	private $minBuy;

	/**
	 * required to buy at least that many shares
	 * for initial sale
	 *
	 * @var int
	 */
	private $minBuyInitial;

	/**
	 * required to sell at least that many shares
	 */
	private $minSell;

	/**
	 * Is this security to be used for TLH substitution?
	 *
	 * @var bool
	 */
	private $isTlhSub;

	/**
	 * which id is used for municipal bond substitution
	 *
	 * @var int
	 */
	private $muniSubId;

	/**
	 * Security ID to be used in TLH substitution
	 *
	 * @var int
	 */
	private $tlhId;

	/**
	 * Holds PK from security_assignments table
	 *
	 * @var int
	 */
	private $securityAssignmentId;

	/**
	 * Transaction fee for security
	 *
	 * @var float
	 */
	private $txFee;

	/**
	 * redemption fee for security
	 *
	 * @var float
	 */
	private $redemptionFee;

	/**
	 * redemption percent for security
	 */
	private $redemptionPercent;

	/**
	 * Constructor method
	 *
	 * @param  string $symbol          security symbol
	 * @param  float $value            security value
	 * @param  float $totalAccountValue account value where this security exists
	 * @return true
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Setter/getter for symbol
	 *
	 * @param  string $val to set symbol
	 * @return string      to get symbol
	 */
	public function symbol($val = null) {
		if (!is_null($val)) {
			$this->symbol = $val;
		}

		return $this->symbol;
	}

	/**
	 * Setter/getter for preferred
	 *
	 * @param  bool $val to set if preferred
	 * @return bool      to get if preferred
	 */
	public function preferred($val = null) {
		if (!is_null($val)) {
			$this->preferred = $val;
		}

		return (bool) $this->preferred;
	}

	/**
	 * Setter/getter for description
	 *
	 * @param  string $val to set description
	 * @return string      to get description
	 */
	public function description($val = null) {
		if (!is_null($val)) {
			$this->description = $val;
		}

		return $this->description;
	}

	/**
	 * Setter/getter for security type
	 *
	 * @param string $val to set type
	 * @return string     to get type
	 */
	public function type($val = null) {
		if (!is_null($val)) {
			$this->type = $val;
		}

		return $this->type;
	}

	/**
	 * Setter/getter for security cost basis
	 *
	 * @param string $val to set cost basis
	 * @return string     to get cost basis
	 */
	public function costBasis($val = null) {
		if (!is_null($val)) {
			$this->costBasis = $val;
		}

		return $this->costBasis;
	}

	/**
	 * Setter/getter for qty owned
	 *
	 * @param float $val to set qty
	 * @return float     to get qty
	 */
	public function qtyOwned($val = null) {
		if (!is_null($val)) {
			$this->qtyOwned = $val;
		}

		return $this->qtyOwned;
	}

	/**
	 * Setter/getter for value
	 *
	 * @param string $val to set value
	 * @return string     to get value
	 */
	public function value($val = null) {
		if (!is_null($val)) {
			$this->value = $val;
		}

		return $this->value;
	}

	/**
	 * Setter/getter for total account value
	 *
	 * @param string $val to set value
	 * @return string     to get value
	 */
	public function totalAccountValue($val = null) {
		if (!is_null($val)) {
			$this->totalAccountValue = $val;
		}

		return $this->totalAccountValue;
	}

	/**
	 * Weight getter
	 *
	 * @return float security weight
	 */
	public function weight() {
		return $this->weight;
	}

	/**
	 * Calculates/sets wieght of a security in account.
	 *
	 * @return float weight
	 */
	public function calcWeight() {
		if (is_null($this->value) || $this->value <= 0) {
			throw new \Exception('Security must have some value');
		}

		if ($this->totalAccountValue() <= 0) {
			throw new \Exception('Account value must be greater than zero');
		}

 		$this->weight = round(($this->value() / $this->totalAccountValue()) * 100, 2);
	}

	public function lastPurchaseDate($val = null) {
		if (!is_null($val)) {
			$this->lastPurchaseDate = $val;
		}

		return $this->lastPurchaseDate;
	}

	public function penaltyInterval($val = null) {
		if (!is_null($val)) {
			$this->penaltyInterval = $val;
		}

		if (!isset($this->penaltyInterval) || is_null($this->penaltyInterval)) {
			$res = $this->__getSecurityTxData();
			$this->penaltyInterval = isset($res[0]['redemption_penalty_interval']) ? $res[0]['redemption_penalty_interval'] : null;
		}
		return $this->penaltyInterval;
	}

	public function getPenaltyExpiration() {
		$dateObject = new \DateTime($this->lastPurchaseDate());
		$days = 'P' . $this->penaltyInterval . 'D';
		$this->penaltyExpiration = $dateObject->sub(new \DateInterval($days))->format('Y-m-d');

		return $this->penaltyExpiration;
	}

	public function checkPenaltyInterval($today = null) {
		if (is_null($today)) {
			$today = time();
		}
		$datetime1 = strtotime($today);
		$datetime2 = strtotime($this->getPenaltyExpiration());

		$secs = $datetime2 - $datetime1;
		$days = $secs / 86400;

		return (bool) $days > 0;
	}

	public function minBuy($val = null) {
		if (!is_null($val)) {
			$this->minBuy = $val;
		}

		if (!isset($this->minBuy) || is_null($this->minBuy)) {
			$res = $this->__getSecurityTxData();
			$this->minBuy = isset($res[0]['minimum_buy']) ? $res[0]['minimum_buy'] : null;
		}
		return $this->minBuy;
	}

	public function minBuyInitial($val = null) {
		if (!is_null($val)) {
			$this->minBuyInitial = $val;
		}

		if (!isset($this->minBuyInitial) || is_null($this->minBuyInitial)) {
			$res = $this->__getSecurityTxData();
			$this->minBuyInitial = isset($res[0]['minimum_initial_buy']) ? $res[0]['minimum_initial_buy'] : null;
		}
		return $this->minBuyInitial;
	}

	public function minSell($val = null) {
		if (!is_null($val)) {
			$this->minSell = $val;
		}

		if (!isset($this->minSell) || is_null($this->minSell)) {
			$res = $this->__getSecurityTxData();
			$this->minSell = isset($res[0]['minimum_sell']) ? $res[0]['minimum_sell'] : null;
		}
		return $this->minSell;
	}

 	public function securityAssignmentId($val = null) {
		if (!is_null($val)) {
			$this->securityAssignmentId = $val;
		}

		if (!isset($this->securityAssignmentId) || is_null($this->securityAssignmentId)) {
			$res = $this->__getSecurityAssignmentData();
			$this->securityAssignmentId = isset($res[0]['id']) ? $res[0]['id'] : null;
		}

		return $this->securityAssignmentId;

	}

	public function txFee($val = null) {
		if (!is_null($val)) {
			$this->txFee = $val;
		}

		if (!isset($this->txFee) || is_null($this->txFee)) {
			$res = $this->__getSecurityTxData();
			$this->txFee = isset($res[0]['transaction_fee']) ? $res[0]['transaction_fee'] : null;
		}
		return $this->txFee;
	}

	public function redemptionFee($val = null) {
		if (!is_null($val)) {
			$this->redemptionFee = $val;
		}

		if (!isset($this->redemptionFee) || is_null($this->redemptionFee)) {
			$res = $this->__getSecurityTxData();
			$this->redemptionFee = isset($res[0]['redemption_fee']) ? $res[0]['redemption_fee'] : null;
		}
		return $this->redemptionFee;
	}

	public function redemptionPercent($val = null) {
		if (!is_null($val)) {
			$this->redemptionPercent = $val;
		}

		if (!isset($this->redemptionPercent) || is_null($this->redemptionPercent)) {
			$res = $this->__getSecurityTxData();
			$this->redemptionPercent = isset($res[0]['redemption_percent']) ? $res[0]['redemption_percent'] : null;
		}
		return $this->redemptionPercent;
	}

	public function isTlhSub($val = null) {
		if (!is_null($val)) {
			$this->isTlhSub = $val;
		}

		if (!isset($this->isTlhSub) || is_null($this->isTlhSub)) {
			$res = $this->__getTlhData();
			$this->isTlhSub = isset($res[0]['minimum_buy']) ? $res[0]['minimum_buy'] : null;
		}
		return $this->isTlhSub;
	}

	public function muniSubId($modelId, $val = null) {
		if (!is_null($val)) {
			$this->muniSubId = $val;
		}

		if (!isset($this->muniSubId) || is_null($this->muniSubId)) {
			$res = $this->__getCEModelData($modelId);
			$this->muniSubId = isset($res[0]['muni_substitution_id']) ? $res[0]['muni_substitution_id'] : null;
		}
		return $this->muniSubId;
	}

	public function tlhId($modelId, $val = null) {
		if (!is_null($val)) {
			$this->tlhId = $val;
		}

		if (!isset($this->tlhId) || is_null($this->tlhId)) {
			$res = $this->__getCEModelData($modelId);
			$this->tlhId = isset($res[0]['tax_loss_harvesting_id']) ? $res[0]['tax_loss_harvesting_id'] : null;
		}
		return $this->tlhId;
	}

	private function __getSecurityTxData() {
		if (is_null($this->securityAssignmentId)) {
			throw new \Exception('Assignment ID is null', 1);
		}

		$q = 'SELECT * FROM `security_transaction` WHERE `security_assignment_id` = ' . $this->securityAssignmentId;
		$res = $this->mySqlDB->q($q);
		return $res;
	}

	private function __getSecurityAssignmentData() {
		$q = 'SELECT * FROM `securities_assignments` WHERE `security_id` = ' . $this->id;
		$res = $this->mySqlDB->q($q);
		return $res;
	}

	private function __getSecurityData() {
		$q = 'SELECT * FROM `securities` WHERE `symbol` = ' . $this->symbol;
		$res = $this->mySqlDB->q($q);
		return $res;
	}

	private function __getTlhData() {
		$q = 'select * from
				ria_company_information
				inner join ce_models on ce_models.owner_id = ria_company_information.ria_user_id
				inner join ce_model_entities on ce_model_entities.model_id = ce_models.id
				where is_tax_loss_harvesting is true
				and (ria_company_information.is_use_qualified_models = false or ria_company_information.is_use_qualified_models = true
				and ce_model_entities.is_qualified = false)
				group by ria_company_information.ria_user_id';
		$res = $this->mySqlDB->q($q);
		return $res;
	}

	private function __getCEModelData($modelId) {
		$q = 'SELECT * FROM `ce_model_entities` WHERE `id` = ' . $modelId;
		$res = $this->mySqlDB->q($q);
		return $res;
	}

	/**
	 * Gets all securities for a given system account ID
	 *
	 * @param  [type] $systemAccountId [description]
	 * @return [type]                  [description]
	 */
	public function getSecurityIdsForSystemAccount($systemAccountId = null) {
		if(is_null($systemAccountId)) {
			return false;
		}

		$q = 'SELECT * FROM `positions`
		INNER JOIN `securities_assignments` on `securities_assignments.security_id` = `positions.security_id`
		WHERE `client_system_account_id` = ' . $systemAccountId;
		$res = $this->mySqlDB->q($q);

		$seucrityIds = Hash::extract($res, '{n}.security_id');
		return $res;
	}

	public function getAll($systemAccountId) {
		//returns all ids for an account
		return true;
	}
}