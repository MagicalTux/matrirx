<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S15low_servers.php : low-level servers functions
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

$GLOBALS['max_users']=0;

triggers_add('send_netinfo',$n=null,'connect');

function irc_func_server($dat) {
	$s=&$GLOBALS['servers'];
	$p=$dat['pars'];
	$n=array();
	$n['name']=$p[0];
	$n['distance']=$p[1];
	$fn=strpos($p[2],' ');
	if (substr($p[2],0,1)!='U') $fn=false;
	if ($fn!==false) {
		$info=substr($p[2],0,$fn);
		$fn=substr($p[2],$fn+1);
		$info=explode('-',$info);
		$nfo=array();
		$nfo['version']=$info[0];
		$nfo['flags']=$info[1];
		$nfo['numeric']=$info[2];
		$GLOBALS['snumerics'][$info[2]]=$p[0];
	} else {
		$fn=$p[3];
		if ($p[2]>0) {
			$nfo=array('numeric'=>$p[2]);
			$GLOBALS['snumerics'][$p[2]]=$p[0];
		} else {
			$nfo=array();
		}
	}
	$n['info']=$nfo;
	$n['fullname']=$fn;
	$n['users']=array();
	$s[$p[0]]=$n;
}

function irc_func_squit($dat) {
	$conf=&$GLOBALS['config'];
	$server=$dat['pars'][0];
	$reason=$dat['pars'][1];
	if (!isset($GLOBALS['servers'][$server])) return;
	Logger::Log('Got SQUIT for '.$server.' ('.$reason.')',LOGGER_WARNING);
	foreach($GLOBALS['servers'][$server]['users'] as $nick=>$data) {
		if (!priv_unregister_user($nick)) return false;
		amsg('userquit',$nick);
	}
	if ( (isset($GLOBALS['servers'][$server]['nfo'])) && ($GLOBALS['servers'][$server]['nfo'])) {
		unset($GLOBALS['snumerics'][$GLOBALS['servers'][$server]['nfo']['numeric']]);
	}
	unset($GLOBALS['servers'][$server]);
}

function irc_resolve_serv($dat) {
	$conf=$GLOBALS['config'];
	$numeric=(int)$dat;
	if ($numeric == $conf['local']['numeric']) return $conf['local']['name'];
	if (isset($GLOBALS['snumerics'][$numeric])) return $GLOBALS['snumerics'][$numeric];
	return $dat;
}

function irc_func_netinfo($dat) {
	$conf=$GLOBALS['config'];
	$p=$dat['pars'];
	$GLOBALS['max_users']=(int)$p[0];
	if ($p[3]!=cloak_checksum()) {
		echo 'Warning: cloak checksum mismatch!'.LF;
	}
	if ($p[7]!=$conf['network']['ircnetwork']) {
		echo 'Warning: Network name mismatch!'.LF;
	}
	return true;
}

function send_netinfo() {
	irc_send(make_netinfo());
	$send=array();
	$send['src']=null;
	$send['command']='EOS';
	$send['pars']=array();
	irc_send($send);
	return true;
}

function make_netinfo() {
	$conf=$GLOBALS['config'];
	$send=array();
	$send['src']=null;
	$send['command']='NETINFO';
	$p=array();
	$p[]=$GLOBALS['max_users']; // global max users
	$p[]=time(); // TSTime
	$p[]=UNREAL_PROTOCOL; // protocol
	$p[]=cloak_checksum();
	$p[]=0; // unknown 
	$p[]=0;
	$p[]=0;
	$p[]=$conf['network']['ircnetwork']; // network
	$send['pars']=$p;
	return $send;
}
