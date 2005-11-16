<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2004 Robert Karpeles.
 * S10low_irc_funcs.php : IRC functions
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

unset($n);
triggers_add('irc_message_trig',$n='notice','irc_notice'); unset($n);
triggers_add('irc_message_trig',$n='privmsg','irc_privmsg'); unset($n);

function irc_message_trig($n,$data,$origin) {
	if ( (!isset($data['src'])) or (!isset($data['target'])) or (!isset($data['message'])) ) {
		Logger::Log('Got incomplete irc_message call from '.$origin);
		return;
	}
	if (!isset($data['callback'])) $data['callback']=false;
	$func='irc_'.$n;
	return $func($data['src'],$data['target'],$data['message'],$data['callback']);
}

function irc_notice($src,$dst,$msg,$callback=false) {
	$send=array();
	$send['src']=$src;
	$send['command']='NOTICE';
	$send['pars']=array($dst,$msg);
	$res=irc_send($send);
	if ($callback) exec_command($send);
	return $res;
}

function irc_privmsg($src,$dst,$msg,$callback=false) {
	if ($msg=='') return false;
	$send=array();
	$send['src']=$src;
	$send['command']='PRIVMSG';
	$send['pars']=array($dst,$msg);
	$res=irc_send($send);
	if ($callback) exec_command($send);
	return $res;
}

