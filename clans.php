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
    'Covenant'     => 'Covenant',
    'Destiny'      => 'Destiny',
    'Dharma'       => 'Dharma',
    'Elendhirin'   => 'Elendhirin',
    'Enigma'       => 'Enigma',
    'Heavens'      => 'Heavens',
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

    echo 'Retrieving: ';

    foreach ($clans as $name)
    {
        echo "$name ";
        $data[$name] = file_get_contents('http://users.nexustk.com/webreport/' . $name . '.html');
    }

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
    return $a / $b * 100;
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

// Argument Processing
if (array_search('--update', $argv) || !file_exists('clans.data'))
    $data = get_data_from_html(array_keys($clans));
else
    $data = get_data_from_file('clans.data');
if (array_search('--sort', $argv))
    $sort = $argv[array_search('--sort', $argv) + 1];
else
    $sort = 'T';

// Populate Data
foreach ($clans as $_name => $name)
{
    $html = $data[$name];

    preg_match('(Total registered : ([0-9]+) Total unregistered : ([0-9]+))', $html, $matches);

    $count = array();
    $count['R']  = $matches[1];
    $count['U']  = $matches[2];
    $count['T']  = $count['R'] + $count['U'];
    $count['A']  = substr_count($html, $active);
    $count['AU'] = substr_count($html, $active   . $unregistered);
    $count['I']  = substr_count($html, $inactive);
    $count['IU'] = substr_count($html, $inactive . $unregistered);
    $count['X']  = substr_count($html, $absent);
    $count['XU'] = substr_count($html, $absent   . $unregistered);

    // Correct Active/Inactive/Absent Counts
    $count['AR'] = $count['A'] - $count['AU'];
    $count['IR'] = $count['I'] - $count['IU'];
    $count['XR'] = $count['X'] - $count['XU'];

    // Calculate Percentages
    $count['RP']  = percentage($count['R'],  $count['T']);
    $count['UP']  = percentage($count['U'],  $count['T']);
    $count['ARP'] = percentage($count['AR'], $count['A']);
    $count['AUP'] = percentage($count['AU'], $count['A']);
    $count['IRP'] = percentage($count['IR'], $count['I']);
    $count['IUP'] = percentage($count['IU'], $count['I']);
    $count['XRP'] = percentage($count['XR'], $count['X']);
    $count['XUP'] = percentage($count['XU'], $count['X']);
    $count['P']   = percentage($count['AR'], $count['T']);

    // Perform Sanity Check
    sanity($count['R'], $count['AR'] + $count['IR'] + $count['XR']);
    sanity($count['U'], $count['AU'] + $count['IU'] + $count['XU']);

    $stats[$name] = $count;
}

// Check that --sort is a valid field.
if (array_key_exists($sort, end($stats)))
{
    echo 'error: invalid filter: --sort ' . $sort;
    exit(1);
}

// Perform the sort of $stats in place.
uasort($stats, compare_x($sort));

// Key:
//  T     - Total Users
//  R     - Registered Users
//  U     - Unregistered Users
//  A     - Active Users (0 <= Played < 15)
//  AR    - Active + Registered
//  AU    - Active + Unregistered
//  I     - Inactive (15 <= Played < 30)
//  IR    - Inactive + Registered
//  IU    - Inactive + Unregistered
//  I     - Percentage of (Inactive + Registered) / Inactive
//  X     - Absent (30 <= Played)
//  XR   - Absent + Registered
//  XU   - Absent + Unregistered
//  XRP - Percentage of (Absent + Registered) / Absent
//  AP - Percentage of (Active + Registered) / Total
//
//  RT    - Percentage of Registered              / Total
//  UT    - Percentage of Unregistered            / Total
//  AT    - Percentage of Active                  / Total
//  IT    - Percentage of Inactive                / Total
//  XT    - Percentage of Absent                  / Total
//  ARA   - Percentage of Active   + Registered   / Active
//  IRA   - Percentage of Inactive + Registered   / Inactive
//  XRA   - Percentage of Absent   + Registered   / Absent
//  AUA   - Percentage of Active   + Unregistered / Active
//  IUA   - Percentage of Inactive + Unregistered / Inactive
//  XUA   - Percentage of Absent   + Unregistered / Absent
//  ART   - Percentage of Active   + Registered   / Total
//  IRT   - Percentage of Inactive + Registered   / Total
//  XRT   - Percentage of Absent   + Registered   / Total
//  AUT   - Percentage of Active   + Unregistered / Total
//  IUT   - Percentage of Inactive + Unregistered / Total
//  XUT   - Percentage of Absent   + Unregistered / Total
echo "+--------------++-----+-----+-----++-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+\n";
echo "| Clan         ||   T |   R |   U ||   A |   I |   X |   U |  RP |  UP |  AR |  AU | ARP | AUP |   I |  IR |  IU | IRP | IUP |   X |  XR |  XU | XRP | XUP |   P |\n";
echo "+--------------++-----+-----+-----++-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+-----+\n";
foreach ($stats as $clan => $count)
    //              Clan     T     R     U      RP      UP     A    AR    AU     ARP     AUP     I    IR    IU     IRP     IUP     X    XR    XU     XRP     XUP       P
    echo sprintf("| %12s | %3d | %3d | %3d | %3d%% | %3d%% | %3d | %3d | %3d | %3d%% | %3d%% | %3d | %3d | %3d | %3d%% | %3d%% | %3d | %3d | %3d | %3d%% | %3d%% | %3d%% |\n",
        $clan,
        $count['T'],
        $count['R'],
        $count['U'],
        $count['RP'],
        $count['UP'],
        $count['A'],
        $count['AR'],
        $count['AU'],
        $count['ARP'],
        $count['AUP'],
        $count['I'],
        $count['IR'],
        $count['IU'],
        $count['IRP'],
        $count['IUP'],
        $count['A'],
        $count['XR'],
        $count['AU'],
        $count['XRP'],
        $count['XUP'],
        $count['P']
        );
echo "+--------------+-----+-----+-----+------+------+-----+-----+-----+-------+-------+-----+-------+---+-------+-------+-----+-----+-----+-------+-------+\n";
