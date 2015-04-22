<?php
/*
 * This file will be responsibe for reading raw data from mongoDB and insert it
 * into normalized mysql DB.
 *
 * Usage: php AccountCalc.php
 */
namespace Model\Pas\Calculator;
use Model\Pas\Base;
use Model\Pas\Accounts;
class Accountcalc extends Base
{

   public function processRawData($data) {
		if (\Config::$DEBUG == 2) {
			var_dump(count($data));
			var_export($data[0]);
		}
		foreach($data as $index => $item) {
			if (\Config::$DEBUG && $index>1) {
				// TEMP HACK.. break
				// currently to process all rows it'stoo slow...
				break;
			}
		    $portfolioId = $item['portfolio_id'];
            $date=$item['date'];
            $this->updatePortfolioValueMysql($portfolioId, $date);
        }
	}
      public function updatePortfolioValueMysql($portfolioId,$date)
      {
       	echo "updatePortfolioValueMysql($portfolioId, $date) \n ------------------------------\n";
		$totalAmountInSecurities = 0;
		$totalCashInAccount = 0;
		$totalCashInMoneyMarket = 0;
		$total = 0;
        $tableName = self::CLIENT_PORTFOLIO_VALUE;
        $accountsModel = new Accounts($this->db, $this->mongoDB);
        $clientIds = $accountsModel->getPortfolioAccountIds($portfolioId);
		foreach ($clientIds as $clientId) {
			try {
				$res = $this->updateAccountValueMysql($portfolioId, $clientId, $date);
               	$totalAmountInSecurities += $res['total_in_securities'];
                $totalCashInAccount += $res['total_cash_in_account'];
				$totalCashInMoneyMarket += $res['total_cash_in_money_market'];
               	$total += $res['total_amount'];
   	           } catch (\Exception $ex) {
				// TODO: process problem....
			}
		}
        if(!empty($portfolioId)){

            //UpdateData
        $q = "UPDATE {$tableName} SET date='{$date}',total_value='{$total}',total_in_securities='{$totalAmountInSecurities}',
                  total_cash_in_accounts='{$totalCashInAccount}',total_cash_in_money_market='{$totalCashInMoneyMarket}'
				  WHERE client_portfolio_id = '{$portfolioId}' AND date='{$date}'";

        }else{

            //Insert Data
            	$q = "INSERT INTO {$tableName} (date, total_value, total_in_securities, total_cash_in_accounts, total_cash_in_money_market)
					VALUES ('{$date}', '{$total}', '{$totalAmountInSecurities}', '{$totalCashInAccount}','{$totalCashInMoneyMarket}')";
        }
        $res = $this->db->q($q);
         echo "END updatePortfolioValueMysql($portfolioId, $date) \n ------------------------------\n";
    }

    function updateAccountValueMysql($portfolioId, $clientId, $date){

   	 echo "updateAccountValueMysql($portfolioId, $clientId, $date )\n";
		$accountsModel = new Accounts($this->db, $this->mongoDB);
        $totalAmountInSecurities=0;
        $totalCashInAccount=0;
        $totalCashInMoneyMarket=0;
        $total = 0;
		$accountIdInfo = $accountsModel->getAccountIdInfo($clientId);
		$accountSource = $accountIdInfo['source'];
		$accountId = $accountIdInfo['account_id'];
        $tableName = self::CLIENT_ACCOUNT_VALUE;
        $totalAmountInSecurities = $accountsModel->getAccountSecuritiesValue($clientId, $date);
        $totalCashInAccount = $accountsModel->getCashInAccount($clientId, $date);
		$totalCashInMoneyMarket = $accountsModel->getCashInMoneyMarket($clientId, $date);
		$total = $totalAmountInSecurities + $totalCashInAccount + $totalCashInMoneyMarket;

        if(!empty($portfolioId)){

             $q = "UPDATE {$tableName}
				  SET system_client_account_id='{$accountId}',source='{$accountSource}',date='{$date}',total_value='{$total}',total_in_securities='{$totalAmountInSecurities}',
                  total_cash_in_account='{$totalCashInAccount}',total_cash_in_money_market='{$totalCashInMoneyMarket}'
				  WHERE client_portfolio_id = '{$portfolioId}' AND date='{$date}'AND system_client_account_id='{$accountId}' AND source='{$accountSource}'";


        }else{
             //Insert Data to Mysql
           	$q = "INSERT INTO {$tableName} (system_client_account_id,source,date, total_value, total_in_securities, total_cash_in_account, total_cash_in_money_market)
					VALUES ('{$accountId}','{$accountSource}','{$date}', '{$total}', '{$totalAmountInSecurities}', '{$totalCashInAccount}','{$totalCashInMoneyMarket}')";
            }
        $res = $this->db->q($q);
              return array(
		             'total_amount' => $total,
		             'total_in_securities' => $totalAmountInSecurities,
		             'total_cash_in_account' => $totalCashInAccount,
		             'total_cash_in_money_market' => $totalCashInMoneyMarket
		             );
        }
  }

