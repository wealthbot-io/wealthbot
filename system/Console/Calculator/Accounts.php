<?php
/*
* Usage: php accounts.php <file type> <date>
*/

/**
* Responsible for Updating account value and portfoliao value.
*/
namespace Accounts\Console;

use System\Database\Database;
use System\Database\Connection;
use System\Model\Pas\Base;


class Accounts extends Base {

    protected $connection;

    public function __construct() {

        $this->connection = new Connection();
        $date=date('Y-m-d',strtotime("+1 days"));
        $ydate= date('Y-m-d',strtotime("-1 days"));
        $date_format = 'Y-m-d';
		$this->dataDateTime1 = strtotime($ydate);
        $this->dataDateTime = strtotime($date);
		$this->dataDate = date($date_format, $this->dataDateTime);
        $this->dataDate1 = date($date_format, $this->dataDateTime1);


    }

    public function updateAccounts() {
		//$db = new \Model\Pas\Accounts($this->connection->getMySqlDB(),
		  //                            $this->connection->getMongoDB());
    	$Account = new \Model\Pas\Calculator\AccountCalc($this->connection->getMySqlDB(),
		                              $this->connection->getMongoDB());
		$data = $Account->getMongoDBData('portfolios',$this->dataDate1,
		                            $this->dataDate);

		if(empty($data)) {
			throw new \Exception('Could not find data in mongo');
		}
        $Account->processRawData($data);
	}

}

if(php_sapi_name() == "cli") {
	//In cli-mode
	if (basename(__FILE__) == basename($_SERVER["SCRIPT_NAME"])) {
		//Let's normalize raw data
		$execClass = new Accounts();
		$execClass->updateAccounts();
	} else {
		// Most like it's called from test, so ignore autoexec ...
	}
} else {
	// Not in cli-mode
}