<?php

include("ppfunc.php");

// get a handle to the PlanePlotter main document
$pp = com_get_active_object('PlanePlotter.Document');

// returns an array of aircraft
$d = GetPPData($pp, true);

echo "Got ".count($d)." aircraft\n";

// print them out
foreach ($d as $f)
    print_r($f);

?>
