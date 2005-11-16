<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S15low_shared.php : low-level users/chans shared functions
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

triggers_add('irc_mode_trig',$n=null,'irc_mode');
function irc_mode_trig($n,$data,$origin) {
	if ( (!isset($data['src'])) or (!isset($data['target'])) or (!isset($data['mode'])) ) {
		Logger::Log('Got incomplete irc_mode call from '.$origin);
		return;
	}
	return irc_mode($data['src'],$data['target'],$data['mode']);
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

// This function is more like a small hack. It will allow an addon
// to fetch a user or a channel.
triggers_add('irc_special_fetch',$n=null,'irc_fetch');
function irc_special_fetch($n,$data,$origin) {
	return amsg_call('irc_fetch_done',$data,$origin);
}

unset($n);
triggers_add('irc_jp_trig',$n='join','irc_join'); unset($n);
triggers_add('irc_jp_trig',$n='part','irc_part'); unset($n);

function irc_jp_trig($n,$data,$origin) {
	if ( (!isset($data['src'])) or (!isset($data['chan'])) ) {
		Logger::Log('Got incomplete irc_join call from '.$origin);
		return;
	}
	if (!isset($data['reason'])) $data['reason']=null;
	$func='irc_'.$n;
	return $func($data['src'],$data['chan'],$data['reason']);
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

function irc_part($src,$chan,$reason=null) {
	$send=array();
	$send['src']=$src;
	$send['command']='PART';
	if (is_array($chan)) $chan=implode(',',$chan);
	$send['pars']=array($chan);
	if (!is_null($reason)) $send['pars'][]=$reason;
	irc_send($send);
	// send to ourself too =p
	exec_command($send);
	return true;
}

triggers_add('irc_kick_trig',$n=null,'irc_kick');

function irc_kick_trig($n,$data,$origin) {
	if ( (!isset($data['src'])) or (!isset($data['chan'])) or (!isset($data['target'])) or (!isset($data['message'])) ) {
		Logger::Log('Got incomplete irc_kick call from '.$origin);
		return;
	}
	return irc_kick($data['src'],$data['chan'],$data['target'],$data['message']);
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
