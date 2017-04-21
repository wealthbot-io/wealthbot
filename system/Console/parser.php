<?php
/*
* Usage: php parser.php <file type> <date>
* Allowed file types: TRN, SEC, CBL, CBP, PRI, POS, TRD
* Allowed date format: yyyy-mm-dd
*/

/**
* Responsible for injecting header line to each CSV file.
* Uses "mongoimport" to store the custodian CSV file into mongo
*/
namespace Console;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();

use \Database\Connection;
use \Lib\Ioc;
use Model\Pas\Repository\CustodianRepository as CustodianRepo;
use Model\Pas\Repository\AdvisorCodeRepository as AdvisorCodeRepo;
use Model\Pas\DocumentRepository\BaseRepository;

class Parser extends Console
{
	/**
	 * Current file date to process, uses date format based on
	 * file name
	 *
	 * @var string
	 */
	protected $fileDate = '090505';

	/**
	* File type to parse
	*/
	private $fileType = null;

	/**
	* To be used by temp. file
	*/
	private $headerExt = '-with-header';

	/**
	 * Path to file based on extension
	 *
	 * @var array
	 */
	private $filePath = array();

	private $filePattern = array(
		'TRN' => '{CUSTODIAN}{DATE}.{EXT}',
		'SEC' => '{CUSTODIAN}{DATE}.{EXT}',
		'PRI' => '{CUSTODIAN}{DATE}.{EXT}',
		'POS' => '{CUSTODIAN}{DATE}.{EXT}',
		'TRD' => '{CUSTODIAN}{DATE}.{EXT}',
        'TXT' => '{CODE}_CBRealized20{DATE}.{EXT}',
        'CBL' => '{CUSTODIAN}{DATE}.{EXT}',
        'CBP' => '{CUSTODIAN}{DATE}.{EXT}'
	);

	/**
	 * Fields that should be modified to MySQL date
	 * @var array
	 */
	private $dateFields = array(
		'file_date',
		'tx_date',
		'settle_date',
		'expiration_date',
		'call_date',
		'issue_date',
		'business_date',
		'original_purchase_date',
		'performance_inception_date',
		'billing_inception_date',
		'date',
		'birth_date',
        'close_date',
        'open_date'
	);

	/**
	* List of headers for each file type
	*/
	private $headerMap = array(
		'TRN' => array(
				'advisor_code', //advisor code
				'file_date', //won't be used
				'account_number', //account number
				'transaction_code', //transaction code
				'cancel_flag',
				'symbol', //symbol
				'security_code', //security code
				'tx_date', //transaction date
				'qty', //quantity
				'net_amount', //net amount
				'gross_amount', //gross amount
				'fee', //fee
				'additional_fee', //additional fee
				'settle_date', //tlment date
				'transfer_account', // to/from account
				'account_type', //not used,
				'accrued_interest',
				'closing_method', //closing method used
				'notes', // transaction comments
				'created',
				'import_date',
				'source',
                'status'
		),
		'SEC' => array(
				'symbol',
				'security_type',
				'description',
				'expiration_date',
				'call_date',
				'call_price',
				'issue_date',
				'first_coupon',
				'interest_rate',
				'share_per_contract',
				'annual_income',
				'comment',
				'created',
				'import_date',
				'source',
                'status'
		),
		'CBL' => array(
				'custodial_id',
				'business_date',
				'account_number',
				'account_type',
				'security_type',
				'symbol',
				'current_qty',
				'cost_basis_un',
				'cost_basis_am',
				'unrealized_gain_loss',
				'cost_basis_fully_nnown',
				'certified_flag',
				'original_purchase_date',
				'original_purchase_price',
				'wash_sale_indicator',
				'wash_sale_qty',
				'created',
				'import_date',
				'source',
                'status'
		),
		'CBP' => array(
				'custodial_id',
				'business_date',
				'account_number',
				'account_type',
				'security_type',
				'symbol',
				'current_qty',
				'cost_basis_un',
				'cost_basis_am',
				'unrealized_gain_loss',
				'cost_basis_fully_nnown',
				'certified_flag',
				'original_purchase_date',
				'original_purchase_price',
				'wash_sale_indicator',
				'wash_sale_qty',
				'created',
				'import_date',
				'source',
                'status'
		),
		'PRI' => array(
				'symbol',
				'security_type',
				'date',
				'price',
				'factor',
				'created',
				'import_date',
				'source',
                'status'
		),
		'POS' => array(
				'account_number',
				'account_type',
				'security_type',
				'symbol',
				'qty',
				'amount',
				'created',
				'import_date',
				'source',
                'status'
		),
		'TRD' => array(
				'company_name',
				'last_name',
				'first_name',
				'street',
				'address_2',
				'address_3',
				'address_4',
				'address_5',
				'address_6',
				'city',
				'state',
				'zipcode',
				'ssn',
				'account_number',
				'advisor_id',
				'taxable',
				'phone_number',
				'fax_number',
				'account_type',
				'objective',
				'billing_account_number',
				'default_account',
				'state_of_primary_residence',
				'performance_inception_date',
				'billing_inception_date',
				'federal_tax_rate',
				'state_tax_rate',
				'months_in_short_term_holding_period',
				'fiscal_year_end',
				'use_average_cost_accounting',
				'display_accrued_interest',
				'display acrrued_dividends',
				'display_accrued_gains',
				'birth_date',
				'discount_rate',
				'payout_rate',
				'created',
				'import_date',
				'source',
                'status'
		),
        'TXT' => array(
                'account_number',
                'close_date',
                'rec_type',
                'open_date',
                'cusip_number',
                'ticker_symbol',
                'security',
                'shares_sold',
                'proceeds',
                'cost',
                'st_gain_loss',
                'lt_gain_loss',
                'trading_method',
                'settle_date',
				'created',
				'import_date',
				'source',
                'status'
        )
	);

    /**
     * Constructor
     *
     * @param string $argv
     */
    public function __construct($argv)
    {
        // Register connection instance
        Ioc::instance('connection', new Connection);

		set_time_limit(0);

        $allowedTypes = array_keys($this->headerMap);

        $inputDate = $this->checkInputDate($argv[2]);

        if (!$inputDate) {
			throw new \Exception('File date is required. Format: YYYY-MM-DD');
		}

        $this->fileDate = $this->convertFileDate($inputDate);
		$this->fileType = trim(isset($argv[1]) ? $argv[1] : null);

        if (!in_array($this->fileType, $allowedTypes) || !isset($this->filePattern[$this->fileType])) {
			throw new \Exception('Invalid file type: ' . $this->fileType . ' (Allowed types: ' . implode(', ', $allowedTypes) . ')');
		}

        $this->filePath = $this->loadFilePaths($argv);
	}

    /**
     * @param string $custodian
     * @param string $code
     * @return string
     */
    protected function getFilePath($custodian, $code)
    {
        return strtr($this->pathTemplate . $this->filePattern[$this->fileType], array(
            '{PATH}' => $this->docMap[$this->fileType],
            '{DATE}' => $this->fileDate,
            '{CODE}' => $code,
            '{EXT}'  => $this->fileType,
            '{CUSTODIAN}' => $custodian
        ));
    }

    /**
     * @param array $argv
     * @return array
     * @throws \Exception
     */
    protected function loadFilePaths($argv)
    {
        $codeRepo = new AdvisorCodeRepo();
        $custodianRepo = new CustodianRepo();

        $paths = array();
        $codeName = isset($argv[3]) ? trim($argv[3]) : null;

        if ($codeName == null) {
            foreach ($custodianRepo->findAll() as $custodian) {
                $codes = $codeRepo->findBy(array('custodian_id' => $custodian->getId()));
                foreach ($codes as $code) {
                    $paths[] = $this->getFilePath($custodian->getShortName(), $code->getName());
                }
            }
            return $paths;
        }

        if (null == $code = $codeRepo->findOneBy(array('name' => $codeName))) {
            throw new \Exception("Advisor code [$codeName] not found");
        }

        if (null == $custodian = $custodianRepo->find($code->getCustodianId())) {
            throw new \Exception("Custodian by advisor code [$codeName] not found");
        }

        $paths[] = $this->getFilePath($custodian->getShortName(), $code->getName());

        return $paths;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getSource($path)
    {
        $path = explode('/', $path);

        return trim(strtoupper($path[2]));
    }

	/**
	 * Converts file date to match file format (YYMMDD)
	 */
	private function convertFileDate($date)
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $date);

        return $dt->format('ymd');
	}

	/**
	 * Adding headers to the custodian CSV file
	 *
	 * @return bool
	 */
	public function injectHeaders()
    {
        foreach ($this->filePath as $path) {
            $rows = array();
            $header = $this->headerMap[$this->fileType];

            $absolutePath = $this->getAbsolutePath($path);

            if ($absolutePath == null) continue;

            if (($handle = fopen($absolutePath, "r")) !== FALSE) {
                $i = 0;
                date_default_timezone_set('America/New_York');
                while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
                    $i++;
                    if ($i == 1) {
                        //injecting headers to the first row
                        $rows[] = $header;
                    }

                    $rows[] = $data;
                    $date = date('Y-m-d H:i:s', time());
                    $importDate = \DateTime::createFromFormat('ymd', $this->fileDate);
                    array_push($rows[$i], $date); //add created
                    array_push($rows[$i], $importDate->format('Y-m-d')); //add import_date
                    array_push($rows[$i], $this->getSource($path)); //add custodian source
                    array_push($rows[$i], 1); // Status not posted
                }
            }

            fclose($handle);

            //will save a new file with headers as first line.
            //new temporary file to keep existing (sample) files intact.
            $handle = fopen($absolutePath . $this->headerExt, "w+");
            foreach ($rows as $key => $row) {
                if (count($header) == count($row)) {
                    if ($key != 0) {
                        $mergedRow = array_combine($rows[0], $row);
                        $row = $this->fixDates($mergedRow);
                    }

                    try {
                        fputcsv($handle, $row);
                    } catch (\Exception $e) {
                        echo 'Problem saving temp. file: ' . $e->getErrorMessage();
                    }
                }
            }

            fclose($handle);
        }

		return true;
	}

	/**
	 * Using "mongoimport" to insert CSV file into the DB
	 *
	 * @return void
	 */
	public function importToMongo()
    {
        $importDate = \DateTime::createFromFormat('ymd', $this->fileDate);
        $mongoCollection = $this->docMap[$this->fileType];

        // Delete duplicates from mongo collection
        $mongoBaseRepo = new BaseRepository();
        $mongoBaseRepo->setCollection($mongoCollection);
        $mongoBaseRepo->deleteByImportDate($importDate->format('Y-m-d'));

        foreach ($this->filePath as $path) {
            $absolutePath = $this->getAbsolutePath($path);

            if ($absolutePath == null) continue;

            try {
                $nohup = 'nohup ';
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Windows has no "nohup"
                    $nohup = '';
                }

                exec($nohup . 'mongoimport -d pas -c ' . $mongoCollection . ' --type csv  --headerline --file ' . $absolutePath . $this->headerExt, $output);
            } catch (\Exception $e) {
                echo 'We have a problem: ' . $e->getErroMessage();
            }

            // remove file, once it's in mongo
            unlink($absolutePath . $this->headerExt);
        }

		return true;
	}

	/**
	 * Converts date into mysql format YYYY-MM-DD HH:MM:SS
	 *
	 * @param  array $row with bad data format
	 * @return array      with mysql-format data
	 */
	private function fixDates($row = null)
    {
		if (is_null($row)) { return; }

		foreach ($row as $key => $value) {
			if (in_array($key, $this->dateFields)) {
				$row[$key] = !empty($value) ? date('Y-m-d', strtotime($value)) : null;
			}
		}

		return array_values($row);
	}
}

/**
 * Parser has following syntax:
 *
 * php system/Console/parser.php TRN 2013-12-12
 *
 * @TODO I guess we could make date optional and get current date by default.
 *
 */
ini_set('memory_limit', '-1');

$parser = new Parser($argv);

if ($parser->injectHeaders()) {
	$parser->importToMongo();
}