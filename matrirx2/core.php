<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * core.php : Main MatrIRX2 file
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

error_reporting(E_ALL);
set_time_limit(0);

define('MATRIRX_VERSION','2.0.0');
define('DEBUG',true);

// for security reason, chroot() is done in the core. It will prevent any
// fraudulent access to outside of the chroot, which will disallow people
// from doing bad things with MatrIRX !
if (!function_exists('chroot')) {
	echo "Please recompile your PHP including chroot() function ( --with-chroot )\n";
	exit(4);
}

$d=getcwd();
$d=str_replace('\\','/',$d);
if (substr($d,-1)!='/') $d.='/';
if (!@chroot($d)) {
	echo 'Could not call chroot(). Make sure you are running as root !'."\n";
	exit(2);
}
define('_ROOT','/'); // _ROOT is still used by some old scripts
unset($d); // forget our initial root

// check if DATA exists
if (!is_dir('/data')) mkdir('/data');

$dir=opendir('/includes/main');
$incl=array();

while($fil=readdir($dir)) {
	if (substr($fil,-4)==".php") {
		if ($fil{0}!='S') continue;
		$incl[]='/includes/main/'.$fil;
	}
}
sort($incl);
foreach($incl as $fil) require_once($fil);

$process=null;
declare(ticks = 1) { // needed to get posix signals
	while(1) {
		if ($sub=main_loop($process)) {
			$parent=posix_getpid();
			$socks=stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
			$process=pcntl_fork();
			if ($process<0) {
				Logger::Log('Could not fork a new process! (out of memory?)',LOGGER_ERR);
				if (OLD_SOCKET_MODE) {
					socket_close($socks[0]);
					socket_close($socks[1]);
				} else {
					fclose($socks[0]);
					fclose($socks[1]);
				}
			} elseif ($process==0) {
				// we're child !
				define('CHILD_NAME',$sub); // define
				if (OLD_SOCKET_MODE) {
					socket_close($socks[1]);
				} else {
					fclose($socks[1]);
				}
				// Protection
				$info=$GLOBALS['addons']['list'][$sub];
				if (!$info) exit(11);
				define('ADDON_FLAGS',$info['flags']);
				$GLOBALS['parent']=$socks[0]; // Socket used for communications with parent
				triggers_call('child'); // let some things reset
				$addon_conf=$GLOBALS['config']['addons'];
				define('ADDON_UID',(int)$addon_conf['defaultuser']);
				define('ADDON_GID',(int)$addon_conf['defaultgroup']);
				unset($GLOBALS['config']);
				unset($GLOBALS['addons']);
				// Stream to parent set to blocking !
				if (OLD_SOCKET_MODE) {
					socket_set_block($socks[0]);
				} else {
					stream_set_blocking($socks[0],true);
				}
				socket_add($socks[0]);
				$dir=opendir('/includes/sub');
				$incl=array();
				while($fil=readdir($dir)) {
					if (substr($fil,-4)==".php") {
						if ($fil{0}!='S') {
							if (strpos(strtolower(ADDON_FLAGS),$fil{0})===false) continue;
						}
						$incl[]='/includes/sub/'.$fil;
					}
				}
				sort($incl);
				foreach($incl as $fil) require_once($fil);
				if (!chroot('/data/'.CHILD_NAME)) {
					// Should never happen
					Logger::Log('Call to chroot() failed!',LOGGER_ERR);
					// let the threadpipe transmit the log message before closing
					sleep(1);
					exit(8);
				}
				// Change UID/GID
				if (ADDON_GID) if (!posix_setgid(ADDON_GID)) {
					Logger::Log('Call to setgid() failed!',LOGGER_ERR);
					// let the threadpipe transmit the log message before closing
					sleep(1);
					exit(9);
				}
				if (ADDON_UID) if (!posix_setuid(ADDON_UID)) {
					Logger::Log('Call to setuid() failed!',LOGGER_ERR);
					// let the threadpipe transmit the log message before closing
					sleep(1);
					exit(9);
				}
				$dir=opendir('/');
				$incl=array();
				while($fil=readdir($dir)) {
					if (substr($fil,-4)==".php") {
						if ($fil{0}!='S') continue;
						$incl[]='/'.$fil;
					}
				}
				sort($incl);
				foreach($incl as $fil) {
					require($fil);
					unlink($fil);
				}
				triggers_call('child_ready');
				while(1) child_loop($parent);
			} else {
				if (OLD_SOCKET_MODE) {
					socket_close($socks[0]);
					socket_set_nonblock($socks[1]);
				} else {
					fclose($socks[0]);
					stream_set_blocking($socks[1],false);
				}
				socket_add($socks[1]);
				Logger::Log('Forked a child for addon '.$sub.' with pid#'.$process,LOGGER_NOTICE);
				foreach($GLOBALS['addons']['list'] as $addon=>&$info) {
					if ($info['loaded']!==true) continue;
					if (!isset($info['sock'])) continue;
					addons_write($info,':'.$sub.' NEWA'.LF);
				}
				$GLOBALS['addons']['list'][$sub]['pid']=$process;
				$GLOBALS['addons']['list'][$sub]['sock']=$socks[1];
				$GLOBALS['addons']['list'][$sub]['buf']=''; // input buffer
				$GLOBALS['addons']['list'][$sub]['info']=$sub;
				$GLOBALS['addons']['sockmap'][$socks[1]]=$sub;
				addons_update($sub);
			}
		}
	}
}
