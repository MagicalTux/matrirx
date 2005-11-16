<?
// low-level chan functions 
function irc_func_join($dat) {
	if (is_null($dat['src'])) return false;
	$src=strtolower($dat['src']);
	if (!isset($GLOBALS['users'][$src])) return false;
	$c=$dat['pars'][0];
	$c=explode(',',$c);
	foreach($c as $chan) {
		chans_do_join($src,$chan);
	}
}

function chans_do_join($user,$chan,$ts=null) {
	$chanoc=$chan;
	$user=strtolower($user);
	$chan=strtolower($chan);
	if (!isset($GLOBALS['users'][$user])) return false;
	if (!isset($GLOBALS['chans'][$chan])) {
		$GLOBALS['chans'][$chan]=array('modes'=>'','name'=>$chanoc,'topic'=>null,'usercount'=>0,'users'=>array());
		if (!isset($ts)) $ts=time();
		$GLOBALS['chans'][$chan]['timestamp']=$ts;
	}
	if (isset($GLOBALS['chans'][$chan]['users'][$user])) return;
	$GLOBALS['chans'][$chan]['usercount']++;
	$GLOBALS['chans'][$chan]['users'][$user]=true;
	$GLOBALS['users'][$user]['chans'][$chan]=true;
	callmod('userjoin',array('user'=>$user,'chan'=>$chan));
	callmod('callback',array('what'=>'join','from'=>null,'user'=>$user,'chan'=>$chan,'reason'=>null),$GLOBALS['users'][$user]['callback']);
}

function irc_do_part($user,$chan,$nocb=false) {
	$chan=strtolower($chan);
	$user=strtolower($user);
	if (!isset($GLOBALS['chans'][$chan])) return;
	if (!isset($GLOBALS['users'][$user])) return;
	unset($GLOBALS['chans'][$chan]['users'][$user]);
	unset($GLOBALS['users'][$user]['chans'][$chan]);
	unset($GLOBALS['chans'][$chan]['modes'][$user]);
	$GLOBALS['chans'][$chan]['usercount']=count($GLOBALS['chans'][$chan]['users']);
	if ($GLOBALS['chans'][$chan]['usercount']<1) {
		unset($GLOBALS['chans'][$chan]);
	}
	if (!$nocb) callmod('callback',array('what'=>'part','from'=>null,'user'=>$user,'chan'=>$chan,'reason'=>null),$GLOBALS['users'][$user]['callback']);
}

function set_chan_modes($chan,$modes,$origin) {
	if (isset($GLOBALS['remote_cap']['CHANMODES'])) {
		$chm=explode(',',$GLOBALS['remote_cap']['CHANMODES']);
	} else {
		$chm='beqa,kfL,l,psmntirRcOAQKVGCuzNSM';
		$chm=explode(',',$chm);
	}
	$chm[0].='qaovh'; // missing modes due to 'prefix'
	// modes : +beqa followed by something (may be multiple)
	//         +kfL : followed by something (unique per chan)
	//         +l : followed by a number (unique per chan)
	//         +psmntirRcOAQKVGCuzNSM : unique modes without any parameter
	$chan=strtolower($chan);
	
	$nmodes=$modes;
	$p=strrpos($nmodes,' ');
	if ($p) {
		$tmp=substr($nmodes,$p+1);
		if ($tmp==(string)((int)$tmp)) {
			$nmodes=substr($nmodes,0,$p);
		}
	}
	callmod('usermode',array('chan'=>$chan,'modes'=>$nmodes,'user'=>$origin));
	
	if (!isset($GLOBALS['chans'][$chan])) return false;
	$m=$GLOBALS['chans'][$chan]['modes'];
	
	// parse modes in $modes
	$modes=explode(' ',$modes); // separate that
	$s=array_shift($modes);
	$l=strlen($s);
	$add=true;
	for($i=0;$i<$l;$i++) {
		$c=$s{$i};
		if ($c=='+') { $add=true; continue; }
		if ($c=='-') { $add=false; continue; }
		if (strpos($chm[0],$c)!==false) {
			// multiple, and followe by something :)
			$arg=strtolower(array_shift($modes));
			if($add) {
				if (!isset($m[$arg])) $m[$arg]='';
				$m[$arg]=str_replace($c,'',$m[$arg]).$c; // a bit complex o.o
			} else {
				if (!isset($m[$arg])) continue;
				$m[$arg]=str_replace($c,'',$m[$arg]);
				if (!$m[$arg]) unset($m[$arg]);
			}
			continue;
		} elseif((strpos($chm[1],$c)!==false) or (strpos($chm[2],$c)!==false) or (strpos($chm[3],$c)!==false)) {
			// followed by something (unique per chan)
			if (strpos($chm[3],$c)!==false) {
				$arg=true;
			} else {
				$arg=array_shift($modes);
			}
			if (strpos($chm[1],$c)!==false) $arg=(int)$arg; // force integer
			if (!isset($m['#'])) $m['#']=array();
			if($add) {
				if (!isset($m['#'][$c])) $m['#'][$c]='';
				$m['#'][$c]=$arg;
			} else {
				if (!isset($m['#'][$c])) continue;
				unset($m['#'][$c]);
			}
			continue;
		}
	}
	$GLOBALS['chans'][$chan]['modes']=$m;
	return true;
}

function irc_func_topic($dat) {
	$p=$dat['pars'];
	$chan=$p[0];
	$chan=strtolower($chan);
	if (!isset($GLOBALS['chans'][$chan])) return false;
	$t=array();
	$t['value']=$p[3];
	$t['set_by']=$p[1];
	$t['when']=$p[2];
	$GLOBALS['chans'][$chan]['topic']=$t;
	callmod('chantopic',array('chan'=>$chan,'topic'=>$t));
}

function irc_func_sjoin($dat) {
	// extended join
//	              :<sender> SJOIN <ts> <chname> [<modes>] [<mode para> ...] :<[[*~@%+]member] ...
//                        [&"ban/except] ...>
	$chanoc=$dat['pars'][1];
	$chan=strtolower($chanoc);
	$ts=$dat['pars'][0];
	$i=2;
	$modes='';
	if (substr($dat['pars'][$i],0,1)=='+') {
		// modes
		for(;($i+1)<count($dat['pars']);$i++) {
			$modes.=' '.$dat['pars'][$i];
		}
	}
	$users=$dat['pars'][$i];
	$users=explode(' ',$users);
	foreach($users as $user) {
		$umodes='';
		if (strpos($user,'@')!==false) $umodes.='o';
		if (strpos($user,'%')!==false) $umodes.='h';
		if (strpos($user,'+')!==false) $umodes.='v';
		if (strpos($user,'~')!==false) $umodes.='a';
		if (strpos($user,'*')!==false) $umodes.='q';
//		if (strpos($user,'')!==false) $umodes.='b';
		$user=strtolower(substr($user,strlen($umodes))); // remove mode prefixes
		if (!isset($GLOBALS['users'][$user])) continue; // skip
		chans_do_join($user,$chanoc,$ts=null);
		set_chan_modes($chan,'+'.$umodes.str_repeat(' '.$user,strlen($umodes)),'');
	}
	if ($modes!='') set_chan_modes($chan,ltrim($modes),'');
}

function irc_join($src,$chan) {
	$send=array();
	$send['src']=$src;
	$send['command']='JOIN';
	if (is_array($chan)) $chan=implode(',',$chan);
	if (substr($chan,0,1)!='#') $chan='#'.$chan;
	$send['pars']=array($chan);
	irc_send($send);
	// send to ourself too =p
	exec_command($send);
	return true;
}

function irc_part($src,$chan) {
	$send=array();
	$send['src']=$src;
	$send['command']='PART';
	if (is_array($chan)) $chan=implode(',',$chan);
	$send['pars']=array($chan);
	irc_send($send);
	// send to ourself too =p
	exec_command($send);
	return true;
}
