#!/usr/bin/php
<?php

include 'killer.php';

// Argument Processing
$clan   = $argv[1];
$update = array_search('--update', $argv);
$sort   = array_search('--sort', $argv);

$dates = killer($clan, $update, $update, $sort);

echo "      Date | Name\n";
foreach ($dates as $user => $time)
	echo strftime("%Y-%m-%d", $time) . " | $user\n";