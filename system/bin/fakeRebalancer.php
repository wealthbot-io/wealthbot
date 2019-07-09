#!/usr/bin/env php
<?php
$params = getopt('j:');
$jobId = $params['j'];

set_time_limit(0);

require_once __DIR__.'/WealthbotRebalancer.php';

$rebalancer = new \Console\WealthbotRebalancer();
$rebalancer->fakeStart($jobId);

