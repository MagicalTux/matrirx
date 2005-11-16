<?
/* Debug module (internal module)
 *
 */

define('DEBUG',true); // debug enabled - change value to disable

function mod_debug_connect() {
	if (!DEBUG) return;
	// end of sync : introduce user 'GLOBAL'
	introduce_user('Debug','debug','BugKiller.irc.FF.st','*','+S','Debug logger');
	if ($GLOBALS['sync']) mod_debug_eos();
}

function mod_debug_eos() {
	if (!DEBUG) return false;
	irc_join('Debug','#Beta');
	irc_mode('Debug','#Beta','+oa Debug Debug');
}

function mod_debug_quit($dat) {
	if (!DEBUG) return false;
	// we were killed or quitted ??? oO
	mod_debug_connect();
}

function mod_debug_part() {
	if (!DEBUG) return false;
	// we were kicked oO ???
	irc_join('Debug','#Beta');
	irc_mode('Debug','#Beta','+oa Debug Debug');
}

//function mod_debug_pubmsg($dat) {
//
//}

function mod_debug_privmsg($dat) {
	if (!DEBUG) return false;
	// check if user is on #Beta
	$user=strtolower($dat['src']);
	if (!isset($GLOBALS['chans']['#beta']['users'][$user])) {
		irc_notice('Debug',$dat['src'],'You are not allowed to send commands to me.');
		return;
	}
	$text=$dat['pars'][1];
	$pos=strpos($text,' ');
	if (!$pos) {
		$cmd=$text;
		$text='';
	} else {
		$cmd=substr($text,0,$pos);
		$text=substr($text,$pos+1);
	}
	$cmd=strtoupper($cmd);
	switch($cmd) {
		case 'SHUTDOWN': priv_debug_do_shutdown(); break;
		case 'DUMPUSER': var_output_file($GLOBALS['users'],'users'); break;
		case 'DUMPCHAN': var_output_file($GLOBALS['chans'],'chans'); break;
		case 'RAW': priv_debug_do_raw($dat,$text); break;
		default:
			irc_notice('Debug',$dat['src'],'The command '._BOLD.strtolower($cmd)._BOLD.' is unknown to Debug. Type '._BOLD.'/msg Debug help'._BOLD.' for help');
			break;
		//
	}
}

function var_output_file($var,$file) {
	if (!DEBUG) return false;
	$res=print_r($var,true);
	$fp=fopen(_ROOT.'dump.'.$file.'.txt','w');
	fputs($fp,$res);
	fclose($fp);
}

function var_output($var) {
	if (!DEBUG) return false;
	$res=print_r($var,true);
	$var=explode("\n",$res);
	foreach($var as $lin) {
		$lin=str_replace("\r",'',$lin);
		irc_privmsg('Debug','#Beta',$lin);
	}
}

function priv_debug_do_shutdown() {
	if (!DEBUG) return false;
	callmod('shutdown');
	$send=array();
	$send['src']=null;
	$send['command']='SQUIT';
	$send['pars']=array('Exiting from network');
	irc_send($send);
	exit;
}

function priv_debug_do_raw($dat,$text) {
	if (!DEBUG) return false;
	// check access
	// 1 : parse text
	$res=irc_parse($text);
	if (!is_array($res)) {
		irc_notice('Debug',$dat['src'],'The RAW command you typed couldn\'t be parsed.');
		return;
	}
	irc_send($res);
	exec_command($res);
}
