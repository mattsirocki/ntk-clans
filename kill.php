#!/usr/bin/php
<?php

include 'killer.php';

// Argument Processing
$clan   = $argv[1];
$update_clan = array_search('--update-clan', $argv);
$update_users = array_search('--update-users', $argv);
$sort   = array_search('--sort', $argv);

$dates = killer($clan, $update_clan, $update_users, $sort);

echo "      Date | Name\n";
foreach ($dates as $user => $time)
	echo strftime("%Y-%m-%d", $time) . " | $user\n";