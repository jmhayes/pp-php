<?php

/* $Id: ppfunc.php,v 1.13 2011/12/09 20:20:45 jordan Exp jordan $ */

/* override this in pp-config-local.php if you want */
$PPdir = 'C:\\COAA\\PlanePlotter\\Log files\\';

@include("pp-config-local.php");

// translate a time string into Unix time
function tsToUnix($str) {
    $d = date_parse($str);
    if (FALSE === $d) {
	print_r($d);
	return 0;
    }
    return gmmktime($d['hour'], $d['minute'], $d['second'],
      $d['month'], $d['day'], $d['year']);
}

class PPFlight {}

// GetPlaneInfoFile() into a file and optionally remove it after parsing
// returns: an array of PPFlight objects
function GetPPData(&$pp, $remove = false) {
    global $PPdir;

    // make a timestamped filename
    $day = gmdate("Y-m-d:H-i-s");
    $dp = explode(':',$day);
    $tail = "GetPlaneInfoFile-".$dp[0].'-'.$dp[1].".txt";
    $dir = str_replace("-",'\\',$dp[0]);
    if (! $remove)
	@mkdir($PPdir.$dir, 0777, TRUE);
    $fn = $PPdir.$tail;
    $rn = $PPdir.$dir.'\\'.$tail;

    // grab the info from PlanePlotter
    $pp->GetPlaneInfoFile($tail);

    try {
	// return value: an array of flights
	$fl = array();

	// open the file and read each line
	@$fp = fopen($fn,"r");
	if (FALSE === $fp)	// XXX?
	    return $fl;

	while (null != ($line = fgets($fp))) {
	    $fld = explode(';',$line);

	    // XXX currently this returns 19 fields
	    if (19 != count($fld)) {
		print_r($fld);
		continue;
	    }

	    // discard unknown hex codes
	    $hex = trim($fld[0]);
	    if ("?" == $hex)
		continue;

	    // discard broken hex codes
	    if (6 != strlen($hex)) {
		print_r($fld);
		continue;
	    }

	    // build up a "flight" object
	    $f = new PPFlight;
	    $f->hex = $hex;
	    $f->rreg = $fld[1];
	    $f->call = trim($fld[2]);
	    $f->lat = trim($fld[3]);
	    $f->lon = trim($fld[4]);
	    $f->alt = trim($fld[5]);
	    $f->head = trim($fld[6]);
	    $f->speed = trim($fld[7]);
	    $f->tStamp = trim($fld[8]);
	    $f->rValid = trim($fld[9]);	// report validity time
	    $f->mType = trim($fld[10]);
	    $f->share = trim($fld[13]);
	    $f->icao = trim($fld[14]);
	    $f->squawk = trim($fld[16]);
	    $fl[$f->hex] = $f;
	}
	fclose($fp);
	if ($remove)
	    unlink($fn);
	else
	    rename($fn, $rn);
	return $fl;
    } catch (Exception $e) {
	print_r($e);
	return null;
    }
}

?>
