<?php

// ALL DA HACKS IN THIS FILE OMG

include '../killer.php';

$update_users = isset($_GET['update']);

if (isset($_GET['kick']))
    $kicked_users = kick_user('Covenant', $_GET['kick']);
else if (isset($_GET['unkick']))
    $kicked_users = unkick_user('Covenant', $_GET['unkick']);
else
    $kicked_users = get_kicked_users('Covenant');

if (isset($_GET['kick']) || isset($_GET['unkick']))
    header('Location: ./');

ob_start();
$dates = killer('Covenant', 1800, false, true);
ob_end_clean();

$names = array_keys($dates);

function div($key, $kicked_users)
{
    global $names;
    $name = $names[$key];

    $output = '';
    $kicked = in_array($name, $kicked_users);
    $strike = $kicked ? ' kicked' : '';
    $a_l = '<a href="' . ($kicked ? "?unkick=$name" : "?kick=$name") . '">';
    $a_r = '</a>';

    $output .= "<div class=\"n{$key}{$strike}\">";
    $output .= $a_l . $name . $a_r;
    $output .= "</div>" . "\n";

    echo $output;
}

?>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:300"></link>
        <link rel="stylesheet" type="text/css" href="kill.css "></link>
        </style>
    </head>
    <body>
        <?php div(0, $kicked_users); ?>
        <?php div(1, $kicked_users); ?>
        <?php div(2, $kicked_users); ?>
        <?php div(3, $kicked_users); ?>
        <?php div(4, $kicked_users); ?>
    </body>
</html>