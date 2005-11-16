<?

function irc_func_eos($dat) {
	// SYNC finished
	// -------------
	// send the NETINFO data

	if ($GLOBALS['sync']) return;
	
	$GLOBALS['sync']=true;
	
	echo 'Now synced with server !'.CRLF;
	
	// scan services
	callmod('eos'); // call module event : EOS (end of sync)
	
//	var_output($GLOBALS['servers']);
	return true;
}
