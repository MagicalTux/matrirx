<?
function mylog($str) {
	echo '['.date('Y-m-d H:i:s').'] '.$str.CRLF;
	if ( (defined('DEBUG')) && DEBUG) {
		irc_privmsg('Debug','#Beta',$str,false);
	}
}
