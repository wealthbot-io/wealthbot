<?php
namespace Model\Rebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Transaction {

	const TX_TYPE_DEPOSIT = 'deposit';
	const TX_TYPE_CASH_TRANSFER = 'cash transfer';
	const TX_TYPE_DIVIDEND = 'dividend';
	const TX_TYPE_INTEREST = 'interest';
	const TX_TYPE_BUY = 'buy';
	const TX_TYPE_SELL = 'sell';
	const TX_TYPE_WITHDRAWAL = 'withdrawal';
	const TX_TYPE_SECURITY_TRANSFER = 'security transfer';
	const TX_TYPE_JOURNAL = 'journal';

	/**
	 * Security symbol
	 *
	 * @var string
	 */
	private $symbol;

	/**
	 * Security quantity
	 *
	 * @var float
	 */
	private $qty;

	/**
	 * Security description
	 *
	 * @var string
	 */
	private $description;

	/**
	 * Transaction type
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Security type
	 */
	private $securityType;

	/**
	 * Amount of transaction
	 *
	 * @var float
	 */
	private $grossAmount;

	/**
	 * Any fees for that transaction
	 *
	 * @var float
	 */
	private $fees = 0;

	/**
	 * How much cash do we have?
	 * @var float
	 */
	private $cashValue;

	/**
	 * How much money is in money market?
	 *
	 * @var float
	 */
	private $moneyMarketValue;

	/**
	 * Holds security
	 *
	 * @var object
	 */
	private $security;

	/**
	 * How much is the total account value?
	 *
	 * @var float
	 */
	private $totalAccountValue = 0;

	/**
	 * Established a lot
	 *
	 * @var float
	 */
	private $lot;

	/**
	 * Holds cost basis for a given security
	 *
	 * @var float
	 */
	private $costBasis;

	/**
	 * Holds security value
	 *
	 * @var float
	 */
	private $securityValue;

	/**
	 * Age of a lot in days
	 *
	 * @var int
	 */
	private $lotAge;

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
	 * Setter/getter for qty
	 *
	 * @param  float $val to set qty
	 * @return float      to get qty
	 */
	public function qty($val = null) {
		if (!is_null($val)) {
			$this->qty = $val;
		}

		return $this->qty;
	}

	/**
	 * Setter/getter for lot
	 *
	 * @param  float $val to set lot
	 * @return float      to get lot
	 */
	public function lot($val = null) {
		if (!is_null($val)) {
			$this->lot = $val;
		}

		return $this->lot;
	}

	/**
	 * Setter/getter for cost basis
	 *
	 * @param  float $val to set cost basis
	 * @return float      to get cost basis
	 */
	public function costBasis($val = null) {
		if (!is_null($val)) {
			$this->costBasis = $val;
		}

		return $this->costBasis;
	}

	/**
	 * Setter/getter for security value
	 *
	 * @param  float $val to set security value
	 * @return float      to get security value
	 */
	public function securityValue($val = null) {
		if (!is_null($val)) {
			$this->securityValue = $val;
		}

		return $this->securityValue;
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
	 * Setter/getter for security type
	 *
	 * @param string $val to set security type
	 * @return string     to get security type
	 */
	public function securityType($val = null) {
		if (!is_null($val)) {
			$this->securityType = $val;
		}

		return $this->securityType;
	}

	/**
	 * Setter/getter for gross amount
	 *
	 * @param string $val to set gross amount
	 * @return string     to get gross amount
	 */
	public function grossAmount($val = null) {
		if (!is_null($val)) {
			$this->grossAmount = $val;
		}

		return $this->grossAmount;
	}

	/**
	 * Setter/getter for fees
	 * @param  [type] $val [description]
	 * @return [type]      [description]
	 */
	public function fees($val = null) {
		if (!is_null($val)) {
			$this->fees = $val;
		}

		return $this->fees;
	}

	/**
	 * Setter/getter for cash value
	 * @param  [type] $val [description]
	 * @return [type]      [description]
	 */
	public function cashValue($val = null) {
		if (!is_null($val)) {
			$this->cashValue = $val;
		}

		return $this->cashValue;
	}


	/**
	 * Setter/getter for money market value
	 *
	 * @param  [type] $val [description]
	 * @return [type]      [description]
	 */
	public function moneyMarketValue($val = null) {
		if (!is_null($val)) {
			$this->moneyMarketValue = $val;
		}

		return $this->moneyMarketValue;
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
	 * Executes a given transaction
	 *
	 * @param strring  $type        transaction type
	 * @param  fload $grossAmount amount of transaction
	 * @return true
	 */
	public function run($type = null, $grossAmount) {
		switch($type) {
			case(self::TX_TYPE_DEPOSIT):
				return $this->__deposit($grossAmount);
			break;

			case(self::TX_TYPE_CASH_TRANSFER):
				return $this->__cashTransfer($grossAmount);
			break;

			case(self::TX_TYPE_DIVIDEND):
				return $this->__dividend($grossAmount);
			break;

			case(self::TX_TYPE_INTEREST):
				return $this->__interest($grossAmount);
			break;

			case(self::TX_TYPE_BUY):
				return $this->__buy($grossAmount);
			break;

			case(self::TX_TYPE_SELL):
				return $this->__sell($grossAmount);
			break;

			case(self::TX_TYPE_WITHDRAWAL):
				return $this->__withdrawal($grossAmount);
			break;

			case(self::TX_TYPE_SECURITY_TRANSFER):
				return $this->__securityTransfer($grossAmount);
			break;
		}
	}

	/**
	 * Process deposit transaction
	 *
	 * @param  float $grossAmount  amount to process
	 * @return float total account value
	 */
	private function __deposit($grossAmount) {
		$this->securityType('Cash');
		$this->cashValue = $this->cashValue += $grossAmount;
		return $this->totalAccountValue += $grossAmount;
	}

	/**
	 * process cash transfer
	 * @param  float $grossAmount  amount to process
	 * @return [type]              [description]
	 */
	private function __cashTransfer($grossAmount) {
		$this->securityType('IDA12');
		$this->moneyMarketValue += $grossAmount;
		$this->cashValue -= $grossAmount;
	}

	/**
	 * process divident income
	 * @param  float $grossAmount  amount to process
	 * @return float total account value
	 */
	private function __dividend($grossAmount) {
		$this->securityType('IDA12');
		$this->moneyMarketValue += $grossAmount;
		return $this->totalAccountValue += $grossAmount;
	}

	/**
	 * process purchase
	 *
	 * @param  [type] $grossAmount [description]
	 * @return [type]              [description]
	 */
	private function __buy($grossAmount) {
		$this->securityType('MF');
		return $this->totalAccountValue += ($grossAmount + $this->fees());
	}

	/**
	 * process sale
	 *
	 * @param  [type] $grossAmount [description]
	 * @return [type]              [description]
	 */
	private function __sell($grossAmount) {
		$this->securityType('MF');
		return $this->totalAccountValue -= ($grossAmount + $this->fees());
	}

	/**
	 * process interest income
	 * @param  float $grossAmount  amount to process
	 * @return float total account value
	 */
	private function __interest($grossAmount) {
		$this->securityType('Cash');
		$this->cashValue += $grossAmount;
		return $this->totalAccountValue += $grossAmount;
	}

	/**
	 * process withdrawal of cash from an account
	 *
	 * @param  float $grossAmount  amount to process
	 * @return float total account value
	 */
	private function __withdrawal($grossAmount) {
		$this->securityType('Cash');
		$this->cashValue -= $grossAmount;
		return $this->totalAccountValue -= $grossAmount;
	}

	/**
	 * process the transfer of security from an account
	 *
	 * @param  float $grossAmount amount to transfer
	 * @return float total account value
	 */
	private function __securityTransfer($grossAmount) {
		$this->securityValue -= $grossAmount;
		$this->costBasis -= $grossAmount;
	}

	/**
	 * Handles settings for a security purchase
	 *
	 * @param  [type] $symbol [description]
	 * @param  [type] $qty    [description]
	 * @param  [type] $fee    [description]
	 * @return [type]         [description]
	 */
	public function buySecurity($symbol, $qty, $fee) {
		$this->symbol($symbol);
		$this->qty($qty);
		$this->fees($fee);
		$this->lot(1);
	}

	/**
	 * Handles settings for a security purchase
	 *
	 * @param  [type] $symbol [description]
	 * @param  [type] $qty    [description]
	 * @param  [type] $fee    [description]
	 * @return [type]         [description]
	 */
	public function sellSecurity($symbol, $qty, $fee) {
		$this->symbol($symbol);
		$this->qty($qty);
		$this->fees($fee);
	}

	/**
	 * Adjusts cost basis and cash value based on the amount
	 *
	 * @param  [type] $amount [description]
	 * @return [type]         [description]
	 */
	public function returnOfPrinciple($amount) {
		$this->costBasis -= $amount;
		$this->cashValue += $amount;
	}

	/**
	 * Increase cost basis of a given security
	 *
	 * @param  [type] $amount [description]
	 * @return [type]         [description]
	 */
	public function creditOfSecurity($amount) {
		$this->costBasis += $amount;
	}

	/**
	 * Decrease cost basis of a given security
	 *
	 * @param  [type] $amount [description]
	 * @return [type]         [description]
	 */
	public function debitOfSecurity($amount) {
		$this->costBasis -= $amount;
	}

	/**
	 * Setter/getter for lot and age of lot
	 *
	 * @return int age of lot in days
	 */
	public function lotAge($val = null) {
		if (!is_null($val)) {
			$this->lotAge = $val;
		}

		return $this->lotAge;
	}

	public function lotPriority() {
		switch($this->lotAge) {
			case($this->lotAge <= 366):
				return 'short-term loss';
			break;

			case($this->lotAge >= 366):
				return 'short-term gain';
			break;

			case ($this->lotAge):
				return 'long-term gain';
			break;

			case ($this->lotAge):
				return 'long-term loss';
			break;

		}
	}

	/**
	 * Setter/getter for the transaction date
	 * yyyy-mm-dd
	 *
	 * @return string date of transaction
	 */
	public function txDate($val = null) {
		if (!is_null($val)) {
			$this->txDate = $val;
		}

		return $this->txDate;
	}

	public function interval($symbol, $today = null) {
		if (is_null($today)) {
			$today = time();
		}
		$datetime1 = strtotime($today);
		$datetime2 = strtotime($this->txDate());

		$secs = $datetime1 - $datetime2;
		$days = $secs / 86400;

		return $days;
	}

	/**
	 * Finds transaction based on ID
	 * @param  [type] $id [description]
	 * @return [type]     [description]
	 */
	public function find($id = null) {
		//need implemenation
		return 1;
	}

	/**
	 * Saves a transaction with a data array which is passed from rebalancer
	 *
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public function save($data = array()) {
		if (empty($data)) {
			return false;
		}
		$transactionTypeId = $this->getTransactionType($data['transaction_type_name']);

		$q = 'INSERT INTO `transactions`
				(`account_id`,
				`transaction_type_id`,
				`closing_method_id`,
				`security_id`,
				`advisor_id`,
				`transfer_account`,
				`qty`,
				`net_amount`,
				`gross_amount`,
				`tx_date`,
				`settle_date`,
				`accrued_interest`,
				`notes`,
				`status`,
				`cancel_status`)
			VALUES
			(' . mysql_real_escape_string($data['account_id']) .  ',
			 ' . mysql_real_escape_string($data['transaction_type_id']) .  ',
			 ' . mysql_real_escape_string($data['closing_method_id']) .  ',
			 ' . mysql_real_escape_string($data['security_id']) .  ',
			 ' . mysql_real_escape_string($data['advisor_id']) .  ',
			 ' . mysql_real_escape_string($data['qty']) .  ',
			 ' . mysql_real_escape_string($data['net_amount']) .  ',
			 ' . mysql_real_escape_string($data['gross_amount']) .  ',
			 ' . mysql_real_escape_string($data['tx_date']) .  ',
			 ' . mysql_real_escape_string($data['settle_date']) .  ',
			 ' . mysql_real_escape_string($data['accrued_interest']) .  ',
			 ' . mysql_real_escape_string($data['notes']) .  ',
			 ' . mysql_real_escape_string($data['status']) .  ',
				)'
		;
		$res = $this->mySqlDB->q($q);
		var_dump($res);
	}

	public function getTransactionType($name = null) {

		if (is_null($name)) {
			return false;
		}
		$q = 'SELECT * FROM `transaction_types` WHERE `name` = "' . mysql_real_escape_string(trim($name)) . "'";
	}
}