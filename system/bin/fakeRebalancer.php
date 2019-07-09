#!/usr/bin/env php
<?php
$params = getopt('j:');
$jobId = $params['j'];
set_time_limit(0);
$rebalancer = new System\Console\WealthbotRebalancer();
$rebalancer->fakeStart($jobId);

