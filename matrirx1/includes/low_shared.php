<?

function irc_func_mode($dat) {
	$p=$dat['pars'];
	$dest=$p[0];
	$src=$dat['src'];
	$modes='';
	for($i=1;isset($p[$i]);$i++) {
		$modes.=' '.$p[$i];
		$modes=trim($modes);
	}
	if (substr($dest,0,1)=='#') {
		set_chan_modes($dest,$modes,$src);
	} else {
		set_user_modes($dest,$modes);
	}
}

function irc_func_svsmode($dat) {
	irc_func_mode($dat); // aloas
}

function irc_get_case($stuff) {
	$stuff=strtolower($stuff);
	if (substr($stuff,0,1)=='#') {
		// find this chan
		if (!isset($GLOBALS['chans'][$stuff])) return false;
		return $GLOBALS['chans'][$stuff]['name'];
	}
	// find this user
	if (!isset($GLOBALS['users'][$stuff])) return false;
	return $GLOBALS['users'][$stuff]['nick'];
}

function irc_mode($src,$dst,$modes) {
	$send=array();
	$send['src']=$src;
	$send['command']='MODE';
	$p=array();
	$p[]=$dst;
	$modes=explode(' ',$modes);
	foreach($modes as $mode) {
		$p[]=$mode;
	}
	$p[]=time(); // add timestamp
	$send['pars']=$p;
	$res=irc_send($send);
	exec_command($send);
	return $res;
}

function irc_kick($src,$chan,$dst,$msg) {
	if ($msg=='') return false;
	$send=array();
	$send['src']=$src;
	$send['command']='KICK';
	$send['pars']=array($chan,$dst,$msg);
	$res=irc_send($send);
	exec_command($send);
	return $res;
}
