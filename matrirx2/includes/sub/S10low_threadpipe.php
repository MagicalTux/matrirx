<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S10low_threadpipe.php : Manages communications with main thread
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

$GLOBALS['parent_buf']='';
$GLOBALS['parent_wbuf']='';
triggers_add('threadpipe_noop',$n=null,'noop');
function threadpipe_noop() {
	// Check for data coming from the thread pipe
	$r=array($GLOBALS['parent']);
	if (OLD_SOCKET_MODE) {
		$res=socket_select($r,$w=null,$e=null,0);
	} else {
		$res=stream_select($r,$w=null,$e=null,0);
	}
	if ($res<1) return;
	if (OLD_SOCKET_MODE) {
		$tmpbuf=socket_read($GLOBALS['parent'],4096);
	} else {
		$tmpbuf=fread($GLOBALS['parent'],4096);
	}
	if (strlen($tmpbuf)<1) {
		echo 'ERROR: Lost link with parent!'.LF;
		exit(11);
	}
	$GLOBALS['parent_buf'].=$tmpbuf;
	while($pos=strpos($GLOBALS['parent_buf'],LF)) {
		$cmd=substr($GLOBALS['parent_buf'],0,$pos);
		$GLOBALS['parent_buf']=substr($GLOBALS['parent_buf'],$pos+1);
		threadpipe_command($cmd);
	}
	return true;
}

function threadpipe_command($cmd) {
	$cmd=explode(' ',$cmd);
	$rcmd=array_shift($cmd);
	$source=null;
	if ($rcmd{0}==':') {
		$source=substr($rcmd,1);
		$rcmd=array_shift($cmd);
	}
	switch(strtoupper($rcmd)) {
		case 'CONF':
			$conf=array_shift($cmd);
			$conf=base64_decode($conf);
			$GLOBALS['config']=unserialize($conf);
			triggers_call('tp_conf');
			break;
		case 'NEWA':
			triggers_call('new_addon_loaded',null,$source);
			break;
		case 'QUIT':
			triggers_call('addon_has_quit',null,$source);
			break;
		case 'CALL':
			$trigger=array_shift($cmd);
			$params=unserialize(base64_decode(array_shift($cmd)));
			if ((isset($params['cache'])) && (is_array($params['cache']))) {
				$cacheupdate=array();
				if (strpos(ADDON_FLAGS,'I')===false) {
					foreach($params['cache'] as $var=>$vals) {
						foreach($vals as $vars=>$rval) {
//							if ( ($var=='users') && ($vars=='matrirx') ) Logger::Log('Bogus event: '.$trigger,LOGGER_DEBUG);
							if (is_null($rval)) {
								if (isset($GLOBALS[$var][$vars])) unset($GLOBALS[$var][$vars]);
							} else {
								$GLOBALS[$var][$vars]=$rval;
							}
							$cacheupdate[$var.'.'.$vars]=$vars;
						}
					}
					if($cacheupdate) triggers_call('cacheupdate',$cacheupdate);
				} else {
					Logger::Log('Addon '.print_r($source,true).' tried to inject users/chans.',LOGGER_EMERG);
				}
				unset($params['cache']);
			}
			if($trigger=='usernickchange') {
				// Special case : a user has changed his nickname, we need to make sure his old info is cleared
				// from memory (or it would be called "memory leak")
				$src=strtolower($params['src']);
				if (isset($GLOBALS['users'][$src])) unset($GLOBALS['users'][$src]);
			}
//			echo 'CALLING '.$trigger.LF;
			triggers_call($trigger,$params,$source);
			break;
		case 'PING':
			// Ping command : send reply to sender, just replacing command "PING" with "PONG"
			$prefix='';
			array_unshift($cmd,'PONG');
			if (!is_null($source)) $prefix=':'.$source.' ';
			threadpipe_write($prefix.implode(' ',$cmd).LF);
			break;
		default:
			echo 'Unknown data from core : '.$rcmd.LF;
			
			break;
		#
	}
}

timers_add('threadpipe_ping',$n=null,10,-1);
function threadpipe_ping() {
	// send a ping to the main thread every 10 seconds
	threadpipe_write('PING'.LF);
	return true;
}

function threadpipe_dowrite() {
	while(strlen($GLOBALS['parent_wbuf'])>0) {
		if (OLD_SOCKET_MODE) {
			$x=socket_write($GLOBALS['parent'],$GLOBALS['parent_wbuf'],strlen($GLOBALS['parent_wbuf']));
		} else {
			$x=@fwrite($GLOBALS['parent'],$GLOBALS['parent_wbuf']);
		}
		if ($x<1) return true;
	}
	socket_del($GLOBALS['parent'],'waitwrite');
	// Delete self
	triggers_del('threadpipe_dowrite','noop');
	return true;
}

// force writing everything before returning
function threadpipe_write($str) {
	$r=0;
	$GLOBALS['parent_wbuf'].=$str;
	while(strlen($GLOBALS['parent_wbuf'])>0) {
		if (OLD_SOCKET_MODE) {
			$x=socket_write($GLOBALS['parent'],$GLOBALS['parent_wbuf'],strlen($GLOBALS['parent_wbuf']));
		} else {
			$x=@fwrite($GLOBALS['parent'],$GLOBALS['parent_wbuf']);
		}
		if ($x<1) {
			socket_add($GLOBALS['parent'],'waitwrite');
			triggers_add('threadpipe_dowrite',$n=null,'noop');
			return $r;
		}
		$GLOBALS['parent_wbuf']=substr($GLOBALS['parent_wbuf'],$x);
		$r+=$x;
	}
	return $r;
}

function irc_do($command,$pars=array(),$dest=null) {
	if (is_null($dest)) $dest='irc';
	$pars=serialize($pars);
	$pars=base64_encode($pars);
	threadpipe_write(':'.$dest.' CALL '.$command.' '.$pars.LF);
}
