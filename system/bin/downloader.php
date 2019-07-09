<?php
use System\Console\Downloader;


ini_set('memory_limit', '-1');

$downloader = new Downloader($argv);
$downloader->start();