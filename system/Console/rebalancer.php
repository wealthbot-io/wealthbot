<?php
namespace Console;

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Database\Database;
use \Database\Connection;
use \Model\Rebalancer\Account;
use \Model\Rebalancer\Security;
use \Model\Rebalancer\AssetClass;
use \Model\Rebalancer\AssetSubclass;
use \Model\Rebalancer\Transaction;
use \Model\Rebalancer\Client;
use \Model\Rebalancer\Ria;
use \Monolog\Logger as Logger;
use \Monolog\Handler\StreamHandler as StreamHandler;

class Rebalancer {

	private $firstRun = true;

	private $liquidate = false;

	private $contributions = false;

	private $distributions = false;

	private $accountVerified = true;

	private $cashOnHand;

	private $generateBillingCash = false;

//	private $rebalFrequency = false;

	private $rebalTolerenceBands = false;

	private $rebalAssetClassLevel;

	private $rebalAssetSubclassLevel;

	private $cashNeeds;

	private $today;

    private $modelDeviation;

	/**
	 * Connection instance
	 */
	private $mySqlDB;

	/**
	 * Logger instance
	 *
	 * @var obj
	 */
	private $log;

	public $Account;

	public $Security;

	public $AssetClass;

	public $AssetSubclass;

	public $Transaction;

	public $Client;

	private $buyData = array(
		'accountNumber',
		'1',
		'E',
		'S',
		' ',
		'numShares',
		'symbol',
		' ',
		'M',
		' ',
		' ',
		'Day',
		' ',
		' ',
		'N',
		' ',
		'N',
		'S'
	);

	private $billingDates = array(
		'01-12',
		'04-12',
		'07-12',
		'10-12',
	);

	private $quaterDays = array(
		'01-01',
		'04-01',
		'07-01',
		'10-01'
	);

	private $semiAnnualDays = array(
		'01-01',
		'07-01'
	);

	private $annualDays = array(
		'01-01'
	);

	private $rebalLevel;

	private $connection;

	public function __construct() {
		$this->connection = new Connection();
		$this->Account = new Account();
		$this->Security = new Security();
		$this->AssetClass = new AssetClass();
		$this->AssetSubclass = new AssetSubclass();
		$this->Transaction = new Transaction();
		$this->Client = new Client();
		$this->Ria = new Ria();

		$this->log = new Logger('filesys');
		$this->log->pushHandler(new StreamHandler(__DIR__ . '/../logs/rebalancer.log', Logger::WARNING));

		$this->connection = new Connection();
		$this->db = $this->connection->getMySqlDB();
	}

	public function firstRun($val = null) {
		if (!is_null($val)) {
			$this->firstRun = $val;
		}

		return (bool) $this->firstRun;
	}

	public function today() {
		return new \DateTime();
	}

	public function getBillingDates() {
		return $this->billingDates;
	}

	public function formatDateMonthDay($date = null) {
		if (!is_null($date)) {
			$date = new \DateTime($date);
			return $date->format('m-d');
		}
		return false;
	}

	public function accountVerified($val = null) {
		if (!is_null($val)) {
			$this->accountVerified = $val;
		}

		return $this->Account->verified($val);
	}
	public function liquidate($val = null) {
		if (!is_null($val)) {
			$this->liquidate = $val;
		}

		return (bool) $this->liquidate;
	}

	public function generateBillingCash($val = null) {
		if (!is_null($val)) {
			$this->generateBillingCash = $val;
		}

		return (bool) $this->generateBillingCash;
	}

	public function contributions($val = null) {
		if (!is_null($val)) {
			$this->contributions = $val;
		}

		return (bool) $this->contributions;
	}

	public function rebalLevel($val = null) {
		if (!is_null($val)) {
			$this->rebalLevel = $val;
		}

		return $this->rebalLevel;
	}

	public function rebalFrequency($val = null) {
		if (!is_null($val)) {
			$this->rebalFrequency = $val;
		}

		return $this->rebalFrequency;
	}

	public function rebalTolerenceBands($val = null) {
		if (!is_null($val)) {
			$this->rebalTolerenceBands = $val;
		}

		return (bool) $this->rebalTolerenceBands;
	}

	public function distributions($val = null) {
		if (!is_null($val)) {
			$this->distributions = $val;
		}

		return (bool) $this->distributions;
	}

	public function rebalAssetClassLevel($val = null) {
		if (!is_null($val)) {
			$this->rebalAssetClassLevel = $val;
		}

		return (bool) $this->rebalAssetClassLevel;
	}

	public function rebalAssetSubclassLevel($val = null) {
		if (!is_null($val)) {
			$this->rebalAssetSubclassLevel = $val;
		}

		return (bool) $this->rebalAssetSubclassLevel;
	}

	public function getSystemAccountId($accountNumber = null) {
		return $this->Account->getSystemAccountId($accountNumber);
	}

	public function getTotalCash() {
		$this->Account->systemAccountId(1);
		return $this->Account->getTotalCash();
	}

	public function getCashBuffer() {
		$this->Account->systemAccountId(1);
		return $this->Account->getCashBuffer();
	}

	public function isPreferredSecurity() {
		return $this->Security->preferred();
	}

	public function calcCashOnHand($ids = null) {
		if (is_null($ids)) {
			return false;
		}

		foreach ($ids as $systemAccountId) {
			$this->Account->systemAccountId($systemAccountId);
			$this->cashOnHand = $this->Account->getTotalCash();

		return $this->cashOnHand;
		}
	}

	public function calcCashNeeds() {
		//$this->Client->getTotalDistribution() + $this->Client->getBillingCashNeeds();
	}

	public function isRebalAllowed($today = null) {
		if (is_null($today)) {
			$today = $this->today();
		}

        $this->Client->id(79);
        $this->Ria->id(77);

//		if (false !== $rebalFrequency) {
        switch ($this->Ria->rebalFrequency()) {
            case (Ria::REBALANCED_FREQUENCY_QUARTERLY):
                if (!in_array($this->formatDateMonthDay($today), $this->quaterDays)) {
                    return false;
                }
            break;

            case (Ria::REBALANCED_FREQUENCY_SEMI_ANNUALLY):
                if (!in_array($this->formatDateMonthDay($today), $this->semiAnnualDays)) {
                    return false;
                }
            break;

            case (Ria::REBALANCED_FREQUENCY_ANNUALLY):
                if (!in_array($this->formatDateMonthDay($today), $this->annualDays)) {
                    return false;
                }
            break;
        }

        switch ($this->Client->getAccountManaged()) {

            case (Client::ACCOUNT_MANAGED_ACCOUNT):

                /** @var AssetSubclass $subclass */
                foreach ($this->Client->getAllocationForSubclasses() as $subclass) {
                    if (abs($subclass->calcOob()) > $subclass->toleranceBand()) {
                        return true;
                    }
                }

                break;

            case (Client::ACCOUNT_MANAGED_HOUSEHOLD):

                /** @var AssetClass $assetClass */
                foreach ($this->Client->getAllocationForAssetClasses() as $assetClass) {
                    if (abs($assetClass->calcOob()) > $assetClass->toleranceBand()) {
                        return true;
                    }
                }

                break;
        }

		return false;
	}

	public function isSellAllowed() {
		if ($this->Transaction->type() !== 'sell') {
			return false;
		}
		return true;
	}

	public function rebalByLevel() {
		if($this->rebalAssetClassLevel === true) {
			return true;
		} elseif($this->rebalAssetSubclassLevel === true) {
			return true;
		}

		return false;
	}

	public function createTxFile($txType = null) {
		if (is_null($txType)) {
			$txType = $this->Transaction->type();
		}

		if ($txType === 'buy') {
			//return $this->__genrateFile($this->buyData);
		} elseif ($txType === 'sell') {
			return true;
		}

		return false;
	}

	public function muniBuyProcess() {
		return true;
	}

	public function shortTermGain() {
		if($this->Transaction->type() !== 'sell') {
			return false;
		}

		if($this->Transaction->grossAmount() <= 0) {
			return false;
		}

		if($this->Transaction->lotAge() <= 366) {
			return false;
		}

		if(!$this->AssetSubclass->topOob()) {
			return false;
		}

		return true;
	}

	public function washSale() {
		if($this->Transaction->type() !== 'buy') {
			return false;
		}

		if($this->Transaction->grossAmount() <= 0) {
			return false;
		}

		if($this->Transaction->interval('ABC') > 31) {
			return false;
		}

		return true;
	}

	/**
	 * Initial function that starts the rebalancer flow
	 *
	 * @return [type] [description]
	 */
	public function start() {
		$riaIds = $this->Ria->getAllActiveIds();
		if (!empty($riaIds)) {

			$systemAccountIds = $this->Client->getAllActiveSystemClientAccountsForRia($riaIds);
			//--- remove to disallowprocessing
			$systemAccountIds = true;
			if (!$systemAccountIds) {
				throw new \Exception('Could not find any system accounts to rebalance', 1);
			}

			$clientUserIds = $this->Client->getAllClientIdsForRia($riaIds);
			//--- remove to disallowprocessing
			$clientUserIds = true;
			if (!$clientUserIds) {
				throw new \Exception('Could not find any clients to rebalance', 1);
			}

			//fake sys account Ids to test
			//test
			$systemAccountIds = array(range(1,9));
			foreach ($systemAccountIds as $systemAccountId) {
				//init rebalancer
				$this->init($systemAccountId);
				$this->rebalance($systemAccountId);
			}



		} else {
			throw new \Exception('Could not find any active RIA\'s', 1);
		}
	}

	/**
	 * initiates some rebalancer properties
	 *
	 * @return [type] [description]
	 */
	public function init() {
		//posssibly init some properties
	}

	/**
	 * Start rebalancer for a given system account id
	 *
	 * @param  [type] $systemAccountId [description]
	 * @return [type]                  [description]
	 */
	public function rebalance($systemAccountId) {
		$systemAccountId = 1;
		$this->Account->id($systemAccountId);
		$this->firstRun = $this->Account->status();
		if($this->firstRun) {
			//process first run for an account
		}

		if($this->Account->verified()) {
			//this account is verified
		}

		if($this->Account->activeEmployer()) {
			//this is an active employer account
		}

		$nonPreferrred[$systemAccountId] = $this->getNonPreferred($systemAccountId);
		$this->sellNonPreferred($nonPreferrred);
		$this->sellLargetsOOB($systemAccountId);

		if ($this->tlhEnabled) {
			$this->TLH->process($systemAccountId);
		}
		return false;
	}

	/**
	 * get Array of non-preferred securities to sell
	 *
	 * @param  [type] $systemAccountId [description]
	 * @return [type]                  [description]
	 */
	public function getNonPreferred($systemAccountId) {
		//get all securities for account
		$securityIds = $this->Security->getAll($systemAccountId);
		// --- remove to test
		$securityIds = array(range(1,200));
		foreach ($securityIds as $securityId) {
			$this->Security->id($securityId);
			$prefNonPref = $this->Security->preferred();
			if (!$prefNonPref) {
				$nonPrefererred[] = $securityId;
			}
		}
		// -- remove to test
		$nonPrefererred = array(range(1,20));
		return $nonPrefererred;
	}

	/**
	 * Generate trade file to sell non preffered securities
	 * @return [type] [description]
	 */
	public function sellNonPreferred() {
		//generate trade file to sell these symbols
		$data = array();
		$this->_genrateFile($data);
	}

	private function __getRia() {
		$this->Ria->getAllActiveIds();
	}
	private function __getAccounts() {
		$q = 'SElECT * from `system_client_accounts` WHERE `status` = "verified"';
		$res = $this->db->q($q);
		return $res;
	}

	protected function _genrateFile($data) {
		$data = $this->getDataArray();

		try {
			$fp = fopen(__DIR__ . '/../outgoing_files/test/TD000.csv', 'w+');
		} catch (Exception $e) {
			$this->log('Could not created trade file: ' . $e);
			return false;
		}

		if ($fp !== false) {
			try {
				foreach ($data as $lineRecord) {
		    		fputcsv($fp, $lineRecord, ',');
				}
				fclose($fp);
			} catch (Exception $e) {
				$this->log('Could not write to trade file: ' . $e);
				return false;
			}

			return true;
		}

		return false;
	}

	private function getDataArray() {
		$transactionType = 'sell';
		$data = array(
			array(
				'account_number' => 12345567789,
				'sepc_use_one' => 1,
				'spec_use_E' => 'E',
				'transaction_type' => 'sell',
				'spec_use_blank' => '',
				'num_shares_to_trade' => 100,
				'symbol' => 'ABC',
				'spec_use_blank' => '',
				'spec_use_M' => 'M',
				'spec_use_blank' => '',
				'spec_use_blank' => '',
				'spec_use_Day' => 'Day',
				'spec_use_blank' => '',
				'spec_use_blank' => '',
				'spec_use_N' => 'N',
				'spec_use_blank' => '',
				'spec_use_N' => 'N',
				'sepc_use_S' => 'S'
			),
			array(
				'account_number' => 12345567789,
				'sepc_use_one' => 1,
				'spec_use_E' => 'E',
				'transaction_type' => 'sell',
				'spec_use_blank' => '',
				'num_shares_to_trade' => 50,
				'symbol' => 'BCC',
				'spec_use_blank' => '',
				'spec_use_M' => 'M',
				'spec_use_blank' => '',
				'spec_use_blank' => '',
				'spec_use_Day' => 'Day',
				'spec_use_blank' => '',
				'spec_use_blank' => '',
				'spec_use_N' => 'N',
				'spec_use_blank' => '',
				'spec_use_N' => 'N',
				'sepc_use_S' => 'S'
			)
		);
		if ($transactionType === 'sell') {
			array_push($data, array(
				'code' => 'VSP',
				'original_lot_sale' => '7082011',
				'qty' => 30
			));
		}
		return $data;
	}
}

//run
$rebalancer = new Rebalancer();
$rebalancer->isRebalAllowed();