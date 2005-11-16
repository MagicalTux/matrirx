<?php

// SPECIAL TEST CODE
// $Id$
//
// Dumped files will be found in data/irc_*/ 

timers_add('dump_globals',$n=null,5,-1);

function dump_globals() {
	// globals to dump
	$glob=array('users','chans','servers');
	foreach($glob as $g) {
		if (!isset($GLOBALS[$g])) continue;
//		echo "DUMPING GLOBAL $g !\n";
		$fil=@fopen('/'.$g.'.txt','w');
		if (!$fil) continue;
		fwrite($fil,print_r($GLOBALS[$g],true));
		fclose($fil);
	}
	return true;
}

