<?php
/* main low-level loop
 * $Id$
 */

$GLOBALS['lastnoop'] = 0; // avoid Notice on first ever loop

function main_loop() {
	if (!$GLOBALS['link']) {
		irc_connect();
	} else {
		$cnx=$GLOBALS['link'];
		if (stream_select($r=array($cnx),$w=null,$e=null,0,200000)===false) { // 200ms
			// select failed -> fd broken ?
			mylog('Select on the stream descriptor failed. The link to the network seems broken.');
			$GLOBALS['link']=false;
			return;
		}
		$lin=fgets($cnx,8192);
		if (!$lin) {
			if (feof($cnx)) {
				$GLOBALS['link']=false;
			}
		} else {
			$dat=irc_parse($lin);
			exec_command($dat);
		}
		// lastnoop ...
		if ($GLOBALS['lastnoop']!=time()) {
			$GLOBALS['lastnoop']=time();
			callmod('noop'); // end of loop - call a noop :)
		}
	}
}

