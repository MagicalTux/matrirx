<?
// low level functions : users management

function irc_func_chghost($dat) {
	$nick=strtolower($dat['pars'][0]);
	$host=$dat['pars'][1];
	if (!isset($GLOBALS['users'][$nick])) return; // nick not found ? should kill it :)
	$GLOBALS['users'][$nick]['vhost']=$host;
}

function irc_func_nick($dat) {
//	              :<sender> NICK <nickname> <hops> <TS> <username> <host> <server>
//                        <servicestamp> <umodes> <vhost> :<info>
	if (!is_null($dat['src'])) {
		// nickname change
		if (!isset($GLOBALS['users'][strtolower($dat['src'])])) return false;
		$p=$dat['pars'];
		$new=$p[0];
		if (isset($GLOBALS['users'][strtolower($new)])) {
			// collision --> send a KILL to $new
			irc_kill(null,$new,'Nick collision');
			return false;
		}
		// change nick
		$src=strtolower($dat['src']);
		$casesrc=irc_get_case($src);
		$dat=$GLOBALS['users'][$src];
		$dat['nick']=$new;
		$new=strtolower($new);
		unset($GLOBALS['users'][$src]);
		$GLOBALS['users'][$new]=$dat;
		// change for joined chans
		foreach($dat['chans'] as $chan=>$joined) {
			if (!$joined) return false;
			$chan=strtolower($chan);
			if (!isset($GLOBALS['chans'][$chan])) continue;
			unset($GLOBALS['chans'][$chan]['users'][$src]);
			$GLOBALS['chans'][$chan]['users'][$new]=true;
			// modes
			if (isset($GLOBALS['chans'][$chan]['modes'][$src])) {
				$dat=$GLOBALS['chans'][$chan]['modes'][$src];
				$GLOBALS['chans'][$chan]['modes'][$new]=$dat;
				unset ($GLOBALS['chans'][$chan]['modes'][$src]);
			}
		}
		$dat=array('nick'=>$casesrc,'new'=>$new);
		callmod('usernickchange',$dat);
		callmod('nickchange',$dat,$src);
		return true;
	}
	// new nickname
	$p=$dat['pars'];
	if (isset($GLOBALS['users'][$p[0]])) {
		irc_kill(null,$p[0],'Nick collision');
		return false;
	}
	$n=priv_register_user($p);
	callmod('newuser',$n['nick']);
}

function set_user_modes($user,$modes) {
	$user=strtolower($user);
	if (!isset($GLOBALS['users'][$user])) return false;
	if (!isset($GLOBALS['users'][$user]['modes'])) $GLOBALS['users'][$user]['modes']='+';
	$m=$GLOBALS['users'][$user]['modes'];
	$s=$modes;
	$l=strlen($s);
	$add=true;
	for($i=0;$i<$l;$i++) {
		$c=$s{$i};
		if ($c==' ') break; // stop if we see a spac
		if ($c=='+') { $add=true; continue; }
		if ($c=='-') { $add=false; continue; }
		if ($add) {
			$m=str_replace($c,'',$m).$c;
		} else {
			$m=str_replace($c,'',$m);
		}
	}
	$GLOBALS['users'][$user]['modes']=$m;
	return true;
}

function irc_func_sethost($dat) {
	$nick=$dat['src'];
	$new=$dat['pars'][0];
	if (!isset($GLOBALS['users'][$nick])) return;
	$GLOBALS['users'][$nick]['vhost']=$new;
}

function irc_func_umode2($dat) {
	$p=$dat['pars'];
	$dest=$dat['src'];
	$modes='';
	for($i=0;isset($p[$i]);$i++) {
		$modes.=' '.$p[$i];
		$modes=trim($modes);
	}
	set_user_modes($dest,$modes);
}

function introduce_user($nick,$ident='service',$host=null,$vhost='*',$modes='+S',$real=null) {
	$ts=time();
	$n=strtolower($nick);
	if (!isset($GLOBALS['user_introduce_time'])) $GLOBALS['user_introduce_time']=array();
	if (!isset($GLOBALS['user_introduce_time'][$n])) $GLOBALS['user_introduce_time'][$n]=0;
	if ($ts <= $GLOBALS['user_introduce_time'][$n]) {
		$ts=$GLOBALS['user_introduce_time'][$n]+1;
		$GLOBALS['user_introduce_time'][$n]++;
	} else {
		$GLOBALS['user_introduce_time'][$n]=$ts;
	}
	$conf=$GLOBALS['config'];
	if (is_null($host)) {
		$host='Services-beta.FF.st';
	}
	if (is_null($real)) {
		$real='Service';
	}
	$p=array();
	$p[0]=$nick;
	$p[1]=1;
	$p[2]=$ts;
	$p[3]=$ident;
	$p[4]=$host;
	$p[5]=$conf['local']['name'];
	$p[6]=0;
	$p[7]=$modes;
	$p[8]=$vhost;
	$p[9]=$real;
	priv_register_user($p);
	$send=array();
	$send['src']=null;
	$send['command']='NICK';
	$send['pars']=$p;
//	var_dump(debug_backtrace());
//	var_dump($send);
	return irc_send($send);
}

function priv_register_user($p) {
	$n=array();
	$n['nick']=$p[0];
	$n['distance']=$p[1];
	$n['timestamp']=$p[2];
	$n['ident']=$p[3];
	$n['host']=$p[4];
	$n['server']=irc_resolve_serv($p[5]);
	$n['servicestamp']=$p[6];
	$n['modes']=str_replace('+','',$p[7]);
	$n['vhost']=$p[8];
	$n['name']=$p[9];
	$n['chans']=array();
	$n['callback']=array(); // callbacks when events happen to this user (join, part, quit, ..)
	$GLOBALS['users'][strtolower($n['nick'])]=$n;
	return $n;
}

function priv_unregister_user($user) {
	$user=strtolower($user);
	if (!isset($GLOBALS['users'][$user])) return false;
	foreach($GLOBALS['users'][$user]['chans'] as $chan => $joined) {
		if (!$joined) continue;
		irc_do_part($user,$chan,true);
	}
	unset($GLOBALS['users'][$user]);
	return true;
}

function irc_func_kick($dat) {
	$chan=strtolower($dat['pars'][0]);
	$user=strtolower($dat['pars'][1]);
	$from=strtolower($dat['src']);
	$reason=$dat['pars'][2];
	if (!isset($GLOBALS['users'][$user])) return false;
	if (!isset($GLOBALS['chans'][$chan])) return false;
	irc_do_part($user,$chan);
	callmod('callback',array('what'=>'kick','from'=>$from,'user'=>$user,'chan'=>$chan,'reason'=>$reason),$GLOBALS['users'][$user]['callback']);
	callmod('userkick',array('from'=>$from,'user'=>$user,'chan'=>$chan,'reason'=>$reason));
	callmod('part',$chan,$user);
}

function irc_func_part($dat) {
	if (is_null($dat['src'])) return false;
	$src=strtolower($dat['src']);
	if (!isset($GLOBALS['users'][$src])) return false;
	$c=$dat['pars'][0];
	$c=explode(',',$c);
	foreach($c as $chan) {
		irc_do_part($dat['src'],$chan);
		callmod('userpart',array('user'=>$src,'chan'=>$chan));
		callmod('part',$chan,$dat['src']);
	}
}

function irc_func_kill($dat) {
	// kill da user >.<
	$p=$dat['pars'];
	if (!priv_unregister_user($p[0])) return false;
	callmod('userquit',$dat);
	callmod('quit',$dat,$p[0]);
}

function irc_func_svskill($dat) {
	// kill da user >.<
	$p=$dat['pars'];
	if (!priv_unregister_user($p[0])) return false;
	callmod('userquit',$dat);
	callmod('quit',$dat,$p[0]);
}

function irc_func_quit($dat) {
	// kill da user >.<
	$src=$dat['src'];
	if (!priv_unregister_user($src)) return false;
	callmod('userquit',$dat);
	callmod('quit',$dat,$src);
}

function irc_kill($src,$dst,$reason) {
	$conf=$GLOBALS['config'];
	$send=array();
	$send['src']=$src;
	if (!is_null($src)) {
		$send['command']='SVSKILL';
		$reason=$src.'Killed ('.$src.' ('.$reason.'))';
	} else {
		$send['command']='SVSKILL';
		$reason=$src.'Killed ('.$reason.')';
	}
	// check user
	if (!isset($GLOBALS['users'][$dst])) return;
	$data=$GLOBALS['users'][$dst];
	if ($data['server']==$conf['local']['name']) {
		$send['command']='QUIT';
	}
	$send['pars']=array($dst,$reason);
	// remove user from list =p
	irc_send($send);
	exec_command($send);
}

function irc_func_invite($dat) {
	if (is_null($dat['src'])) return false;
	$src=strtolower($dat['src']);
	if (!isset($GLOBALS['users'][$src])) return false;
	$dst=$dat['pars'][0];
	$chan=$dat['pars'][1];
	callmod('userinvite',array('user'=>$src,'chan'=>$chan,'target'=>$dst));
	callmod('invite',array('chan'=>$chan,'src'=>$src),$dst);
}


