#!/usr/bin/env php
<?php
require_once(dirname(__DIR__).'/vendor/autoload.php');


$params = getopt('j:');
$jobId = $params['j'];
set_time_limit(0);
$rebalancer = new System\Console\WealthbotRebalancer();
$rebalancer->fakeStart($jobId);

