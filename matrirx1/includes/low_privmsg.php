<?
function irc_func_privmsg($dat) {
	$conf=$GLOBALS['config'];
	$p=$dat['pars'];
	$dest=strtolower($p[0]);
	if (substr($dest,0,1)=='#') {
		// we're parsing a privmsg to a chan !
		if (!isset($GLOBALS['chans'][$dest])) return; // unknown chan
		$u=$GLOBALS['chans'][$dest]['users'];
		foreach($u as $user=>$joined) {
			callmod('pubmsg',$dat,strtolower($user));
		}
	} else {
		$dest=explode('@',$dest);
		if (isset($dest[1])) {
			if ($dest[1]!=strtolower($conf['local']['name'])) return;
		}
		callmod('privmsg',$dat,$dest[0]);
	}
}


// IRC functions

function irc_quit($src,$msg) {
	$send=array();
	$send['src']=$src;
	$send['command']='QUIT';
	$send['pars']=array($msg);
	$res=irc_send($send);
	exec_command($send);
	return $res;
}

function irc_notice($src,$dst,$msg) {
	$send=array();
	$send['src']=$src;
	$send['command']='NOTICE';
	$send['pars']=array($dst,$msg);
	$res=irc_send($send);
	exec_command($send);
	return $res;
}

function irc_privmsg($src,$dst,$msg,$callback=true) {
	if ($msg=='') return false;
	$send=array();
	$send['src']=$src;
	$send['command']='PRIVMSG';
	$send['pars']=array($dst,$msg);
	$res=irc_send($send);
	if ($callback) exec_command($send);
	return $res;
}

