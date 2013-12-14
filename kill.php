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

    file_put_contents("$clan.users", serialize($data));

    return $data;
}


// Argument Processing
$clan = $argv[1];

if (array_search('--update', $argv) || !file_exists("$clan.data"))
    $data = get_data_from_html($clan);
else
    $data = get_data_from_file($clan);


$l = str_replace('\{name\}', '.*?', preg_quote('<a class="link" href="http://users.nexustk.com/?name={name}" target="_new">'));
$r = preg_quote('</a>');
$a = preg_quote(ACTIVE);
$i = preg_quote(INACTIVE);
$x = preg_quote(ABSENT);
$u = preg_quote(UNREGISTERED);

preg_match_all("($l(.*?)$r($a|$i|$x)$u)", $data, $matches);

$names = array();

foreach ($matches[1] as $match)
{
    $exploded_name = explode(' ', $match);
    $names[] = $exploded_name[0];
}

echo "Found " . count($names) . " Unregistered Names\n";

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


foreach ($dates as $user => $time)
{
	echo "$user," . strftime("%Y-%m-%d", $time) . "\n";
}
