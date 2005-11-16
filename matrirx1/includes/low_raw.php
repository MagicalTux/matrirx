<?

function irc_func_rawcode($dat) {
	$code=$dat['value'];
	switch($code) {
		case 412:
			// error : No text to send (empty PRIVMSG)
			break;
		default:
			irc_privmsg('Debug','#Beta','Got unknown RAW code - Line: '.$dat['raw']);
		//
	}
}
