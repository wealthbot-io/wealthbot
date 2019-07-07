<?php
/*
* Usage: php parser.php <date>
* Allowed date format: yyyy-mm-dd
*/

/**
* Responsible for downloading custodian files
*/
namespace Console;

if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

require_once(__DIR__ . '/../AutoLoader.php');
\AutoLoader::registerAutoloader();


class Downloader extends Console
{
	public $username = 'capitalengines1';
	public $password = 'Wi11iam$34';
	public $loginUrl = 'https://www.advisorservices.com/servlet/advisor/LogIn?';
	public $queryString = 'REMEMBERUSERID=checked&STALE=N&USERID=capitalengines1&PASSWORD=Wi11iam%2434&StartPage=&
	DV_DATA=1404790627462&ORIG_QUERY=&SP_DATA=&COOKIE_DATA=&url=&DV_DATA=1404790627462&
	fp_browser=mozilla%2F5.0+%28x11%3B+ubuntu%3B+linux+x86_64%3B+rv%3A30.0%29+gecko%2F20100101+firefox%2F30.0%7C5.0+%28X11%29%7CLinux+x86_64&
	fp_screen=24%7C1920%7C1080%7C1055&fp_software=&fp_timezone=-4&
	fp_language=lang%3Den-US%7Csyslang%3D%7Cuserlang%3D&fp_java=1&fp_cookie=1&fp_flash=11.2.202&sp_data=&url_data=&query_data=&form_submit=1';

	public $curl;
	public $dom;


	public function __construct()
	{
		$this->dom = new \DOMDocument();
	}

	public function prepCurl()
	{
		//init curl
		$this->curl = curl_init();

		$header[] = "Accept: application/xml";
		$header[] = "Content-type: text/plain";
		$header[] = "User-Agent: Custom PHP Script";
		$header[] = "Host: campaign.oventus.com";
		$header[] = "Cookie: CookieName1=Value;CookieName2=Value";

	   	curl_setopt($this->curl, CURLOPT_POST, 5);
	    curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
	    curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, FALSE);
	    curl_setopt($this->curl, CURLOPT_TIMEOUT, 15);
	    curl_setopt($this->curl, CURLOPT_HEADER, 1);
	    curl_setopt($this->curl, CURLINFO_HEADER_OUT, TRUE);
	}

	public function start()
	{
		//init curl
		$this->prepCurl();

		//execute the request (the login)
		$html = $this->login();
		$this->processHtml($html);
	}

	public function login()
	{
		//Set the URL to work with
		curl_setopt($this->curl, CURLOPT_URL, $this->loginUrl);

		//Set the post parameters
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->queryString);

		return curl_exec($this->curl);
	}

	public function processHtml($html)
	{
		$this->dom->loadHTML($html);
		file_put_contents('logs/downloader.log', print_r($this->dom, 1));
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

$downloader = new Downloader($argv);
$downloader->start();