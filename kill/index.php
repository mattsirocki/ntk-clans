<?php
include '../killer.php';

ob_start();
$dates = killer('Covenant', true, false, true);
ob_end_clean();

$names = array_keys($dates);

?>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans:300"></link>
        <style type="text/css">
            html, body { height: 100%; }
            html { display: table; margin: auto; }
            body { display: table-cell; vertical-align: middle; font-family: "Open Sans"; font-weight: 300; text-align: center;}

        </style>
    </head>
    <body>
        <div style="font-size: 50px;"><?php echo $names[0]; ?></div>
        <div style="font-size: 30px;"><?php echo $names[1]; ?></div>
        <div style="font-size: 20px;"><?php echo $names[2]; ?></div>
        <div style="font-size: 15px;"><?php echo $names[3]; ?></div>
        <div style="font-size: 10px;"><?php echo $names[4]; ?></div>
    </body>
</html>