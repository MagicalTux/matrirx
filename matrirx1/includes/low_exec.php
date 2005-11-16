<?
// exec command

function exec_command($dat) {
	// find a command
	$cmd=strtolower($dat['command']);
	$cmd='irc_func_'.$cmd;
	if (!function_exists($cmd)) $cmd='irc_unknown_func';
	$res=$cmd($dat);
	if (is_array($res)) irc_send($res);
}


function irc_unknown_func($dat) {
	if ( (defined('DEBUG')) && DEBUG) irc_privmsg('Debug','#Beta','WARNING: Unknown function called : `'.$dat['command'].'\' !');
	if ( (defined('DEBUG')) && DEBUG) var_output($dat);
}

function irc_func_pass($dat) {
	return true; // ignore this call
}

function irc_func_protoctl($dat) {
	$p=array();
	foreach($dat['pars'] as $prot) {
		$prot=explode('=',$prot);
		if (!isset($prot[1])) $prot[1]=true;
		$p[$prot[0]]=$prot[1];
	}
	$GLOBALS['remote_cap']=$p;
	return true;
}

function irc_func_away($dat) {
	if (is_null($dat['src'])) return false;
	if (!isset($GLOBALS['users'][$dat['src']])) return false;
	if ( (isset($dat['pars'][0])) and ($dat['pars'][0]!='')) {
		$GLOBALS['users'][$dat['src']]['away']=$dat['pars'][0];
	} else {
		unset($GLOBALS['users'][$dat['src']]['away']);
	}
}

function irc_func_smo($dat) {
	// let's ignore the server notices =p
//	irc_privmsg('Debug','#Beta','Got server notice !');
//	var_output($dat);
	return true;
}

function irc_func_ping($dat) {
	$code=$dat['pars'][0];
	$answer=array();
	$answer['command']='PONG';
	$answer['src']=null;
	$answer['pars']=array($code);
	return $answer;
}
