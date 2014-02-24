<?php

/**
 * Usage:
 *
 * To update the clan data:
 *
 *   php clans.php --update
 *
 *
 */

/**
 * HTML Pattern Defines
 */
define('ACTIVE',       '<IMG SRC="buttongreen.gif" WIDTH="13" HEIGHT="13" ALT="Active">');
define('INACTIVE',     '<IMG SRC="buttonyellow.gif" WIDTH="13" HEIGHT="13" ALT="Inactive">');
define('ABSENT',       '<IMG SRC="buttonred.gif" WIDTH="13" HEIGHT="13" ALT="Absent">');
define('UNREGISTERED', '<IMG SRC="Notreg.gif" WIDTH="15" HEIGHT="12" ALT="Unregistered">');

/**
 * List of Clan Names
 *
 * Note: These names must match the names of the clan HTML report pages.
 *
 * @var array
 */
$clans = array
(
    'Alizarin'     => 'Alizarin',
    'Bear'         => 'Bear',
    'BuyaArmy'     => 'Buya Army',
    'Covenant'     => 'Covenant',
    'Destiny'      => 'Destiny',
    'Dharma'       => 'Dharma',
    'Elendhirin'   => 'Elendhirin',
    'Enigma'       => 'Enigma',
    'Heavens'      => 'Heavens',
    'Horde'        => 'Horde',
    'KoguryoArmy'  => 'Koguryo Army',
    'Kurimja'      => 'Kurimja',
    'LostKingdom'  => 'Lost Kingdom',
    'Oceana'       => 'Oceana',
    'Pegasus'      => 'Pegasus',
    'Phoenix'      => 'Phoenix',
    'SanSin'       => 'SanSin',
    'Silla'        => 'Silla',
    'SunMoon'      => 'Sun Moon',
    'The_Forsaken' => 'The Forsaken',
    'Tiger'        => 'Tiger',
    'Viper'        => 'Viper',
);

/**
 * List of Field Names
 *
 * @var array
 */
$fields = array
(
    'T',
    'R',
    'U',
    'RT',
    'UT',
    'A',
    'AR',
    'AU',
    'ART',
    'AUT',
    'I',
    'IR',
    'IU',
    'IRT',
    'IUT',
    'X',
    'XR',
    'XU',
    'XRT',
    'XUT',
);



/**
 * Array of Clan Statistics
 *
 * @var array
 */
$stats = array();

/**
 * Retrieve Clan HTML from NexusTK Website
 *
 * @param array $clans
 *     Accepts a list of clan names.
 * @return array
 *     Returns an array, where each key is the name of a clan, and each value
 *     is the corresponding HTML.
 */
function get_data_from_html($clans)
{
    $data = array();

    echo 'Scraping: ';

    foreach ($clans as $name)
    {
        echo "$name ";
        $data[$name] = file_get_contents('http://users.nexustk.com/webreport/' . $name . '.html');
    }
    echo "\n";

    file_put_contents('clans.data', serialize($data));

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
function get_data_from_file($name)
{
    return unserialize(file_get_contents($name));
}

/**
 * Sanity Check
 * @param  integer $a
 *     Accepts Integer A
 * @param  integer $b
 *     Accepts Integer B
 * @return void
 *     Exits the script if integer A is not equal to integer B.
 */
function sanity($a, $b)
{
    if ($a != $b)
    {
        echo sprintf('%g != %g', $a, $b);
        exit(1);
    }
}

/**
 * Calculates the Percentage of
 * @param  float $a
 *     Accepts float A.
 * @param  float $b
 *     Accepts float B.
 * @return float
 *     Returns A/B * 100
 */
function percentage ($a, $b)
{
    if ($b == 0)
        return '?';
    return round($a / $b * 100);
}

/**
 * Returns a Comparison Function
 *
 * @param  string $field
 *     Accepts the name of the field to compare.
 * @return function
 *     Returns a closure which can be used to compare to arrays by a specific
 *     field.
 */
function compare_x($field)
{
    return function ($a, $b) use ($field) { return $a[$field] < $b[$field]; };
}


//
// Main Script
//
$is_script = isset($argv);
if (!$is_script)
{
    $argv = array('web');

    foreach ($_GET as $key => $value)
    {
        array_push($argv, '--' . $key);
        array_push($argv, $value);
    }
}

// true if scraping
$fresh_data = array_search('--update', $argv) || !file_exists('clans.data');

// HTML Header
if (!$is_script)
    echo "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"><title>NexusTK Clans</title></head><body><pre>\n";

// Argument Processing
if ($fresh_data)
    $data = get_data_from_html(array_keys($clans));
else
    $data = get_data_from_file('clans.data');
if (array_search('--sort', $argv))
    $sort = $argv[array_search('--sort', $argv) + 1];
else
    $sort = 'Clan';



// Populate Data
foreach ($clans as $_name => $name)
{
    $html = $data[$_name];

    preg_match('(Total registered : ([0-9]+) Total unregistered : ([0-9]+))', $html, $matches);

    $count = array();
    $count['R']  = $matches[1];
    $count['U']  = $matches[2];
    $count['T']  = $count['R'] + $count['U'];
    $count['A']  = substr_count($html, ACTIVE);
    $count['AU'] = substr_count($html, ACTIVE   . UNREGISTERED);
    $count['I']  = substr_count($html, INACTIVE);
    $count['IU'] = substr_count($html, INACTIVE . UNREGISTERED);
    $count['X']  = substr_count($html, ABSENT);
    $count['XU'] = substr_count($html, ABSENT   . UNREGISTERED);

    // Correct Active/Inactive/Absent Counts
    $count['AR'] = $count['A'] - $count['AU'];
    $count['IR'] = $count['I'] - $count['IU'];
    $count['XR'] = $count['X'] - $count['XU'];

    // Calculate Percentages
    $count['RT']  = percentage($count['R'],  $count['T']);
    $count['UT']  = percentage($count['U'],  $count['T']);
    $count['AT']  = percentage($count['A'],  $count['T']);
    $count['IT']  = percentage($count['I'],  $count['T']);
    $count['XT']  = percentage($count['X'],  $count['T']);
    $count['ART'] = percentage($count['AR'], $count['T']);
    $count['IRT'] = percentage($count['IR'], $count['T']);
    $count['XRT'] = percentage($count['XR'], $count['T']);
    $count['AUT'] = percentage($count['AU'], $count['T']);
    $count['IUT'] = percentage($count['IU'], $count['T']);
    $count['XUT'] = percentage($count['XU'], $count['T']);

    // Perform Sanity Check
    sanity($count['R'], $count['AR'] + $count['IR'] + $count['XR']);
    sanity($count['U'], $count['AU'] + $count['IU'] + $count['XU']);

    $stats[$_name] = $count;
}

// Save the Stats array... might do something with the data eventually.
if ($fresh_data)
    file_put_contents(sprintf('clan_stats_%s.data', strftime('%Y%m%d%H%M%S')), serialize($stats));

// Check that --sort is a valid field.
if ($sort !== 'Clan' && !array_key_exists($sort, end($stats)))
{
    echo 'error: invalid filter: --sort ' . $sort;
    exit(1);
}

// Perform the sort of $stats in place.
if ($sort === 'Clan')
    ksort($stats);
else
    uasort($stats, compare_x($sort));

function text_field($field, $width = 4)
{
    return str_repeat(' ', $width - strlen($field)) . $field . ' |';
}

function anchor_field($field, $width = 4)
{
    return str_repeat(' ', $width - strlen($field)) . "<a href=\"?sort=$field\">$field</a> |";
}

echo "+----+--------------+" . str_repeat('-----+', count($fields)) . "\n";
echo '|  # |'; echo $is_script ? text_field('Clan', 13) : anchor_field('Clan', 13);
foreach ($fields as $field)
    echo $is_script ? text_field($field) : anchor_field($field);
echo "\n";
echo "+----+--------------+" . str_repeat('-----+', count($fields)) . "\n";
$i = 0;
foreach ($stats as $clan => $count)
{
    printf("| %2d | %12s |", ++$i, $clans[$clan]);
    foreach ($fields as $field)
        echo sprintf(" %3d |", $count[$field]);
    echo "\n";
}
echo "+----+--------------+" . str_repeat('-----+', count($fields)) . "\n";

echo "+-------------------------------------------------------+\n";
echo "| LEGEND                                                |\n";
echo "+-------------------------------------------------------+\n";
echo "| T        Number of Total Members                      |\n";
echo "| R        Number of Registered Members                 |\n";
echo "| U        Number of Unregistered Members               |\n";
echo "| RT   Percentage of Registered Members out of Total    |\n";
echo "| UT   Percentage of Unregistered Members out of Total  |\n";
echo "| A        Number of Active Members                     |\n";
echo "| AR       Number of Active+Registered Members          |\n";
echo "| AU       Number of Active+Unregistered Members        |\n";
echo "| ART  Percentage of Active+Registered out of Total     |\n";
echo "| AUT  Percentage of Active+Unregistered out of Total   |\n";
echo "| I        Number of Inactive Members                   |\n";
echo "| IR       Number of Inactive+Registered                |\n";
echo "| IU       Number of Inactive+Unregistered              |\n";
echo "| IRT  Percentage of Inactive+Registered out of Total   |\n";
echo "| IUT  Percentage of Inactive+Unregistered out of Total |\n";
echo "| X        Number of Absent Members                     |\n";
echo "| XR       Number of Absent+Registered                  |\n";
echo "| XU       Number of Absent+Unregistered                |\n";
echo "| XRT  Percentage of Absent+Registered out of Total     |\n";
echo "| XUT  Percentage of Absent+Unregistered out of Total   |\n";
echo "+-------------------------------------------------------+\n";

if (!$is_script)
    echo "</pre></body></html>";