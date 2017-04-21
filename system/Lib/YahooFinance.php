<?php
namespace Lib;

class YahooFinance {

	protected $yqlBaseUrl = 'http://query.yahooapis.com/v1/public/yql';

	protected $query = 'select * from yahoo.finance.quotes where symbol in ("YHOO","AAPL","GOOG","MSFT")';

	public function init() {
		$queryUrl = $this->yqlBaseUrl . '?q=' . urlencode($this->query) . '&env=store://datatables.org/alltableswithkeys&format=json';
		$session = curl_init($queryUrl);
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		$json = curl_exec($session);
		$resultObj = json_decode($json);

		$quotes = $this->__buildQuoteString($resultObj);

		return $quotes;
	}

	private function __buildQuoteString($resultObj) {
		if (!is_object($resultObj)) {
			return false;
		}
		$quotes = array();
		for($i = 0; $i < $resultObj->query->count; $i++) {
    		$quotes[$resultObj->query->results->quote[$i]->symbol] = $resultObj->query->results->quote[$i]->AskRealtime;
  		}
  		return $quotes;
	}
}

// $yf = new YahooFinance();
// $yf->init();