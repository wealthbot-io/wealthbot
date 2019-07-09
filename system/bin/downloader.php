<?php
require_once(dirname(__DIR__).'/vendor/autoload.php');


ini_set('memory_limit', '-1');

$downloader = new \System\Console\Downloader($argv);
$downloader->start();