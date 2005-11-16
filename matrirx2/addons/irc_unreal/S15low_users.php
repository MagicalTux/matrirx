<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S15low_users.php : low-level users functions
 * $Id$
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * You can also contact the author of this software by mail at this address :
 * MagicalTux@FF.st or by postal mail : Mark Karpeles, 40, Rue Veron, 
 * 94140 Alfortville, FRANCE
 */

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
		$chanlist=array();
		foreach($dat['chans'] as $chan=>$joined) {
			if (!$joined) return false;
			$chanlist[]=$chan;
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
		// update nickid registration
		$GLOBALS['usern'][$dat['id']]=$new;
		// Change in server registration
		unset($GLOBALS['servers'][$dat['server']][$src]);
		$GLOBALS['servers'][$dat['server']]['users'][$new]=&$GLOBALS['users'][$new];
		$dat=array('src'=>$casesrc,'nick'=>$casesrc,'new'=>$new);
		amsg('usernickchange',$dat,$chanlist);
		amsg('nickchange',$dat,$src);
		return true;
	}
	// new nickname
	$p=$dat['pars'];
	if (isset($GLOBALS['users'][$p[0]])) {
		irc_kill(null,$p[0],'Nick collision');
		return false;
	}
	$n=priv_register_user($p);
	amsg('newuser',array('nick'=>$n['nick']));
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

function irc_func_swhois($dat) {
	$p=$dat['pars'];
	$nick=strtolower($p[0]);
	if (!isset($GLOBALS['users'][$nick])) return;
	$GLOBALS['users'][$nick]['swhois']=$p[1];
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

triggers_add('irc_addon_quit',$n=null,'addon_has_quit');
function irc_addon_quit($n,$data,$origin) {
	$conf=$GLOBALS['config'];
	$loc=&$GLOBALS['servers'][$conf['local']['name']]['users'];
	$rem=array();
	foreach($loc as $user=>&$data) {
		if (isset($data['addon'][$origin])) {
			unset($data['addon'][$origin]);
			if (!$data['addon']) $rem[]=$user;
		}
	}
	foreach($rem as $user) {
		irc_quit($user,'No addon available for this user');
	}
}

triggers_add('introduce_user_trig',$n=null,'introduce_user');
function introduce_user_trig($n,$data,$origin) {
	if (!isset($data['nick'])) {
		Logger::Log('Got incomplete introduce_user call from '.$origin);
		return;
	}
	if (!isset($data['ident'])) $data['ident']='matrirx';
	if (!isset($data['host'])) $data['host']=null;
	if (!isset($data['vhost'])) $data['vhost']='*';
	if (!isset($data['modes'])) $data['modes']='+S';
	if (!isset($data['real'])) $data['real']=null;
	if (!isset($data['ip'])) $data['ip']='127.0.0.1';
	return introduce_user($data['nick'],$data['ident'],$data['host'],$data['vhost'],$data['modes'],$data['real'],$data['ip'],$origin);
}

// Local introduction command
function introduce_user($nick,$ident='matrirx',$host=null,$vhost='*',$modes='+S',$real=null,$ip='127.0.0.1',$addon=null) {
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
		$host='localhost';
	}
	if (is_null($real)) {
		$real='MatrIRX';
	}
	$p=array();
	$p[0]=$nick;
	$p[1]=0; // distance
	$p[2]=$ts;
	$p[3]=$ident;
	$p[4]=$host;
	$p[5]=$conf['local']['name'];
	$p[6]=0;
	$p[7]=$modes;
	$p[8]=$vhost;
	$p[9]=base64_encode(pack('N',ip2long($ip)));
	$p[10]=$real;
	priv_register_user($p,$addon);
	$p[1]=1; // distance
	$send=array();
	$send['src']=null;
	$send['command']='NICK';
	$send['pars']=$p;
//	var_dump(debug_backtrace());
//	var_dump($send);
	return irc_send($send);
}

function priv_register_user($p,$addon=null) {
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
	$n['addon']=(is_null($addon)?array():array($addon=>true));
	if (function_exists('base64_ts_convert')) $n['timestamp']=base64_ts_convert($n['timestamp']);
//	if (!isset($GLOBALS['remote_cap']['NICKIP'])) {
	if (!isset($p[10])) {
		$n['ip']=null;
		$n['name']=$p[9];
	} else {
		if ($p[9]!='*') {
			$ip=inet_ntop(base64_decode($p[9]));
			$n['ip']=$ip;
		} else {
			$n['ip']=null;
		}
		$n['name']=$p[10];
	}
	$n['chans']=array();
	$n['callback']=array(); // callbacks when events happen to this user (join, part, quit, ..)
	$n['swhois']=null; // no swhois by default
	if (!isset($GLOBALS['servers'][$n['server']])) {
		// Argh! We do not know where does this user come from !
		// Send back a kill signal and do *not* register this user
		irc_kill(null,$n['nick'],'Bad USER line (unknown server)');
		return null;
	}
	$id=0;
	while(isset($GLOBALS['usern'][$id])) $id++;
	$GLOBALS['usern'][$id]=strtolower($n['nick']);
	$n['id']=$id;
	$GLOBALS['users'][strtolower($n['nick'])]=$n;
	$GLOBALS['servers'][$n['server']]['users'][strtolower($n['nick'])]=&$GLOBALS['users'][strtolower($n['nick'])];
	return $n;
}

function priv_unregister_user($user) {
	$user=strtolower($user);
	if (!isset($GLOBALS['users'][$user])) return false;
	foreach($GLOBALS['users'][$user]['chans'] as $chan => $joined) {
		if (!$joined) continue;
		irc_do_part($user,$chan,true);
	}
	$dat=&$GLOBALS['users'][$user];
	unset($GLOBALS['usern'][$dat['id']]);
	unset($GLOBALS['servers'][$dat['server']][$user]);
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
	amsg('callback',array('what'=>'kick','from'=>$from,'user'=>$user,'chan'=>$chan,'reason'=>$reason),$GLOBALS['users'][$user]['callback']);
	amsg('userkick',array('from'=>$from,'user'=>$user,'chan'=>$chan,'reason'=>$reason));
	amsg('part',$chan,$user);
}

function irc_func_part($dat) {
	if (is_null($dat['src'])) return false;
	$src=strtolower($dat['src']);
	if (!isset($GLOBALS['users'][$src])) return false;
	$c=$dat['pars'][0];
	$c=explode(',',$c);
	foreach($c as $chan) {
		irc_do_part($dat['src'],$chan);
		amsg('userpart',array('user'=>$src,'chan'=>$chan));
		amsg('part',array('chan'=>$chan),$dat['src']);
	}
}

function irc_func_kill($dat) {
	// kill da user >.<
	$p=$dat['pars'];
	amsg('quit',$dat,$p[0]);
	if (!priv_unregister_user($p[0])) return false;
	amsg('userquit',$dat);
}

function irc_func_svskill($dat) {
	// kill da user >.<
	$p=$dat['pars'];
	amsg('quit',$dat,$p[0]);
	if (!priv_unregister_user($p[0])) return false;
	amsg('userquit',$dat);
}

function irc_func_quit($dat) {
	// kill da user >.<
	$src=$dat['src'];
	amsg('quit',$dat,$src);
	if (!priv_unregister_user($src)) return false;
	amsg('userquit',$dat);
}

function irc_kill($src,$dst,$reason) {
	$conf=$GLOBALS['config'];
	$send=array();
	$send['src']=$src;
	if (!is_null($src)) {
		$send['command']='KILL';
//		$reason=$src.'Killed ('.$src.' ('.$reason.'))';
	} else {
		$send['command']='KILL';
//		$reason=$src.'Killed ('.$reason.')';
	}
	// check user
	if (!isset($GLOBALS['users'][$dst])) return;
	$data=$GLOBALS['users'][$dst];
	if ($data['server']==$conf['local']['name']) {
		$send['command']='QUIT';
		$reason='Killed ('.$reason.')';
	}
	$send['pars']=array($dst,$reason);
	// remove user from list =p
	irc_send($send);
	exec_command($send);
}

function irc_svskill($src,$dst,$reason) {
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
	amsg('userinvite',array('user'=>$src,'chan'=>$chan,'target'=>$dst));
	amsg('invite',array('chan'=>$chan,'src'=>$src),$dst);
}


