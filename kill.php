#!/usr/bin/php

<?php

/**
 * HTML Pattern Defines
 */
define('ACTIVE',       '<IMG SRC="buttongreen.gif" WIDTH="13" HEIGHT="13" ALT="Active">');
define('INACTIVE',     '<IMG SRC="buttonyellow.gif" WIDTH="13" HEIGHT="13" ALT="Inactive">');
define('ABSENT',       '<IMG SRC="buttonred.gif" WIDTH="13" HEIGHT="13" ALT="Absent">');
define('UNREGISTERED', '<IMG SRC="Notreg.gif" WIDTH="15" HEIGHT="12" ALT="Unregistered">');

/**
 * Retrieve Clan HTML from NexusTK Website
 *
 * @param array $clans
 *     Accepts a list of clan names.
 * @return array
 *     Returns an array, where each key is the name of a clan, and each value
 *     is the corresponding HTML.
 */
function get_data_from_html($clan)
{
    echo "Retrieving: $clan";

    $data = file_get_contents("http://users.nexustk.com/webreport/$clan.html");

    file_put_contents("$clan.data", serialize($data));

    return $data;
}

/**
 * Retrieve Clan HTML from Serialized File
 *
 * @param  string $name
 *     Accepts the name of the data file.
 * @return array
 *     Returns the unserialized data.
 */
function get_data_from_file($clan)
{
    return unserialize(file_get_contents("$clan.data"));
}


function get_users_from_file($clan)
{
	return unserialize(file_get_contents("$clan.users"));
}

function get_users_from_html($clan, $names)
{
    $data = array();

    echo "$clan: Retrieving Users:";

    foreach ($names as $name)
    {
    	echo " $name";
    	$data[$name] = file_get_contents("http://www.bodhisanctum.com/player-search?option=com_playersearch&view=playersearch&Itemid=489&adsearch=$name");
    }
    echo "\n";

    file_put_contents("$clan.users", serialize($data));

    return $data;
}

function prune_names($names, $titles)
{
    $kill_names = array();
    $save_names = array();

    foreach (array_map(null, $names, $titles) as $zip)
    {
        if (strpos($zip[1], "Ret.") === false)
            $kill_names[] = $zip[0];
        else
            $save_names[] = $zip[0];

    }

    return array($kill_names, $save_names);
}

// Argument Processing
$clan = $argv[1];

if (array_search('--update', $argv) || !file_exists("$clan.data"))
    $data = get_data_from_html($clan);
else
    $data = get_data_from_file($clan);

$sort_dates = array_search('--sort', $argv);


$l = str_replace('=name', '=[a-zA-Z]*?', preg_quote('<a class="link" href="http://users.nexustk.com/?name=name" target="_new">'));
$r = preg_quote('</a>');
$a = preg_quote(ACTIVE);
$i = preg_quote(INACTIVE);
$x = preg_quote(ABSENT);
$u = preg_quote(UNREGISTERED);

$p = "($l(.*?)\"(.*?)\"$r($i|$x)$u)";

preg_match_all($p, $data, $matches);

$names = array();
$titles = array();

foreach ($matches[1] as $match)
{
    $exploded_name = explode(' ', $match);
    $names[] = $exploded_name[0];
}

foreach ($matches[2] as $match)
{
    $titles[] = $match;
}

echo "Found " . count($names) . " Unregistered Names\n";
echo "Pruning: List to Remove Retired Primogens/Generals\n";

list($names, $save_names) = prune_names($names, $titles);

echo "Pruning: " . count($save_names) . " Names Will Be Saved:\n";
foreach ($save_names as $name)
    echo " $name";
echo "\n";

if (array_search('--update', $argv) || !file_exists("$clan.users"))
    $users = get_users_from_html($clan, $names);
else
    $users = get_users_from_file($clan);

$td_l  = preg_quote('<td>');
$td_r  = preg_quote('</td>');
$nbsp  = preg_quote('&nbsp;');
$date  = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
$td_date_or_space = "$td_l(?:$nbsp|$date)$td_r";

$pattern = "($td_date_or_space$td_date_or_space$td_date_or_space$td_l($date)$td_r)";

$dates = array();

foreach ($users as $user => $page)
{
	preg_match_all($pattern, $page, $matches);


	$latest = 0;

	foreach ($matches[1] as $date)
	{
		$current = strtotime($date);

		if ($current > $latest)
			$latest = $current;
	}

	$dates[$user] = $latest;
}

if ($sort_dates)
{
    asort($dates);
}

foreach ($dates as $user => $time)
{
	echo strftime("%Y-%m-%d", $time) . " | $user\n";
}

