<?
function irc_func_notice($dat) {
	$conf=$GLOBALS['config'];
	$p=$dat['pars'];
	$dest=strtolower($p[0]);
	if (substr($dest,0,1)=='~') return;
//	if (substr($dest,0,1)=='') return;
	if (substr($dest,0,1)=='@') return;
	if (substr($dest,0,1)=='%') return;
	if (substr($dest,0,1)=='+') return;
	
	if (substr($dest,0,1)!='#') {
		$dest=explode('@',$dest);
		if (isset($dest[1])) {
			if ($dest[1]!=strtolower($conf['local']['name'])) return;
		}
		callmod('notice',$dat,$dest[0]);
	} else {
		// pubnotice
		if (!isset($GLOBALS['chans'][$dest])) return; // unknown chan
		$u=$GLOBALS['chans'][$dest]['users'];
		foreach($u as $user=>$joined) {
			callmod('pubnotice',$dat,$user);
		}
	}
}
