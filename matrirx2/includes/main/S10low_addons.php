<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S10low_addons.php : Addons management
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

define('ADDONS_NORMAL',0);
define('ADDONS_IRCLINK',1);

addons_init();
function addons_init() {
	$ADDONS=&$GLOBALS['addons'];
	$ADDONS['list']=array();
	$ADDONS['loadcount']=0;
	$ADDONS['sockmap']=array();
	triggers_add('addons_check',$s=null,'noop');
	triggers_add('addons_child',$s=null,'child'); // called when child is spawned
	addons_reload_list();
}

function addons_reload_list() {
	// Generate list of addons
	$ADDONS=&$GLOBALS['addons'];
	$dir=opendir('/addons');
	while($addon=readdir($dir)) {
		$fil='/addons/'.$addon;
		if (is_dir($fil)) {
			$fil.='/infos.txt';
			if (!file_exists($fil)) continue; // not really an active addon
			$info=file($fil);
			$i=array();
			foreach($info as $ifo) {
				$ifo=explode(':',rtrim($ifo));
				$ifo_tag=strtolower(array_shift($ifo));
				$ifo=ltrim(implode(':',$ifo));
				$i[$ifo_tag]=$ifo;
			}
			$info=$i;
			if (!isset($info['autoload'])) $info['autoload']=false;
			if (!isset($ADDONS['list'][$addon])) {
				$ADDONS['list'][$addon]=array('loaded'=>null);
			}
			$tag=&$ADDONS['list'][$addon];
			foreach($info as $var=>$val) $tag[$var]=$val;
			if ($info['autoload']) addons_load($addon);
			unset($info); unset($i);
			continue;
		}
		if (is_file($fil)) {
			if (!isset($ADDONS['list'][$addon])) {
				$ADDONS['list'][$addon]=array('loaded'=>null);
			}
			continue;
		}
	}
	closedir($dir);
}

function addons_child() {
	$ADDONS=&$GLOBALS['addons'];
	// Close fds on fork (security improvement)
	if ( (isset($ADDONS['sockmap'])) && (is_array($ADDONS['sockmap']))) {
		foreach($ADDONS['sockmap'] as $sock=>$map) {
			if (OLD_SOCKET_MODE) {
				socket_close($ADDONS['list'][$map]['sock']);
			} else {
				fclose($ADDONS['list'][$map]['sock']);
			}
		}
	}
	unset($GLOBALS['addons']);
}

function addons_load($addon) {
	$ADDONS=&$GLOBALS['addons'];
	if (!isset($ADDONS['list'][$addon])) return false; // unknown addon
	if ($ADDONS['list'][$addon]['loaded']===true) return true; // already loaded
	if ($ADDONS['list'][$addon]['loaded']===false) return true; // already trying to load
	$ADDONS['list'][$addon]['loaded']=false;
	$ADDONS['list'][$addon]['wbuf']='';
	$ADDONS['loadcount']++;
	return true;
}

function addons_getload() {
	$ADDONS=&$GLOBALS['addons'];
	if (!$ADDONS['loadcount']) return;
	$toload=0;
	$nload=false;
	foreach($ADDONS['list'] as $addon=>$info) {
		if (is_null($info['loaded'])) continue;
		if ($info['loaded']) continue;
		$toload++;
		if (!$nload) $nload=$addon;
	}
	$ADDONS['loadcount']=$toload;
	if ($nload) {
		$ADDONS['list'][$nload]['loaded']=true;
		$ADDONS['list'][$nload]['pid']=0; // pid pending
	}
	return $nload;
}

function addons_check() {
	// check if childs are still running...
	$pid=pcntl_wait($status, WNOHANG);
	if ($pid<1) return true; // nothing has happened :)
	// search which addon has quit~
	$ADDONS=&$GLOBALS['addons'];
	foreach($ADDONS['list'] as $addon=>&$info) {
		if (isset($info['pid'])) {
			if ($info['pid']==$pid) break;
		}
		unset($info);
	}
	if (!isset($info)) return true; // the dead child wasn't traced (asked for kill)
	if (isset($info['sock'])) {
		socket_del($info['sock']);
		if (OLD_SOCKET_MODE) {
			socket_close($info['sock']);
		} else {
			fclose($info['sock']);
		}
		unset($ADDONS['sockmap'][$info['sock']]);
		$info['sock']=null;
	}
	$info['pid']=null;
	$info['loaded']=null;
	if (pcntl_wifexited($status)) {
		$code=pcntl_wexitstatus($status);
		Logger::Log('Process #'.$pid.' ['.$addon.'] has exited with status '.$code.'.',LOGGER_WARNING);
	} elseif(pcntl_wifsignaled($status)) {
		Logger::Log('Process #'.$pid.' ['.$addon.'] has exited after being killed.',LOGGER_WARNING);
	} else {
		Logger::Log('Process #'.$pid.' ['.$addon.'] has exited.',LOGGER_WARNING);
	}
	// broadcast : addon has quit
	foreach($GLOBALS['addons']['list'] as &$info) {
		if ($info['loaded']!==true) continue;
		if (!isset($info['sock'])) continue;
		addons_write($info,':'.$addon.' QUIT'.LF);
	}
	timers_add('addons_load',$addon,10,1); // reload crashed addon
	return true;
}

triggers_add('addons_readpipe',$s=null,'noop'); // called on no operation (each loop)
function addons_readpipe() {
	$ADDONS=&$GLOBALS['addons'];
	$r=array();
	foreach($ADDONS['sockmap'] as $sock=>$map) {
		$r[]=$ADDONS['list'][$map]['sock'];
	}
	if (OLD_SOCKET_MODE) {
		$res=socket_select($r,$w=array(),$e=array(),0);
	} else {
		$res=stream_select($r,$w=array(),$e=array(),0);
	}
	if ($res<1) return true;
	foreach($r as $sock) {
		$sub=$ADDONS['sockmap'][$sock];
		$data=&$ADDONS['list'][$sub];
		if (OLD_SOCKET_MODE) {
			$tmpbuf=socket_read($sock,4096);
		} else {
			$tmpbuf=fread($sock,4096);
		}
		$data['buf'].=$tmpbuf;
		while($pos=strpos($data['buf'],LF)) {
			$cmd=substr($data['buf'],0,$pos);
			$data['buf']=substr($data['buf'],$pos+1);
			addons_command($sub,$cmd);
		}
	}
	return true;
}

function addons_dowrite() {
	// check for addons having data to send
	$count=0;
	foreach($GLOBALS['addons']['list'] as $name=>&$addon) {
		if ($addon['loaded']!==true) continue; // skip
		if(strlen($addon['wbuf'])==0) {
			socket_del($addon['sock'],'waitwrite');
			continue;
		}
		if (OLD_SOCKET_MODE) {
			$x=@socket_write($addon['sock'],$addon['wbuf'],strlen($addon['wbuf']));
		} else {
			$x=@fwrite($addon['sock'],$addon['wbuf']);
		}
		if ($x<1) continue;
		$addon['wbuf']=substr($addon['wbuf'],$x);
		if (strlen($addon['wbuf'])>0) {
			$count++;
			continue;
		}
		socket_del($addon['sock'],'waitwrite');
	}
	if (!$count) triggers_del('addons_dowrite','noop');
}

function addons_write(&$addon,$data) {
	$addon['wbuf'].=$data;
	while(strlen($addon['wbuf'])>0) {
		$x=@fwrite($addon['sock'],$addon['wbuf']);
		if ($x<1) {
			socket_add($addon['sock'],'waitwrite');
			triggers_add('addons_dowrite',$n=null,'noop');
			return true;
		}
		$addon['wbuf']=substr($addon['wbuf'],$x);
	}
	socket_del($addon['sock'],'waitwrite');
	return true;
}

function addons_command($sub,$cmd) {
	$ADDONS=&$GLOBALS['addons'];
	$data=&$ADDONS['list'][$sub];
	$data['activity']=timer_getmicrotime();
	$cmd=explode(' ',$cmd);
	$rcmd=array_shift($cmd);
	if ($rcmd{0}==':') {
		// direct message
		$dest=substr($rcmd,1);
		if (isset($ADDONS['list'][$dest])) {
			$info=&$ADDONS['list'][$dest];
			if ($info['loaded']===true) {
				addons_write($info,':'.$sub.' '.implode(' ',$cmd).LF);
				return;
			}
		}
		// message : addons $dest is down
		fwrite($data['sock'],':'.$dest.' DOWN'.LF);
		return;
	}
	switch(strtoupper($rcmd)) {
		case 'PING':
			break;
		case 'ABMG':
			// Broadcast message to all *other* addons
			$cmd=implode(' ',$cmd);
			foreach($ADDONS['list'] as $addon=>&$info) {
				if ($addon==$sub) continue;
				if ($info['loaded']!==true) continue;
				// TODO: code a buffer system to send data to addons in a non-blocking way
				addons_write($info,':'.$sub.' '.$cmd.LF);
			}
			break;
		case 'PONG':
			$sent=array_shift($cmd);
			$now=microtime(true);
			$diff=($now-$sent)*1000;
			if (isset($data['latency'])) {
				$lat=$data['latency'];
			} else {
				$lat=array(0,0);
			}
			$lat[0]++;
			$lat[1]+=$diff;
			if ($lat[0]>=100) {
				$lat[1]=$lat[1] / $lat[0];
				$lat[0]=1;
			}
			$data['latency']=$lat;
			break;
		case 'SLOG':
			$level=(int)array_shift($cmd);
			$msg=implode(' ',$cmd);
			Logger::Log($msg,$level,$sub);
			break;
		case 'CREQ':
			if (strpos($data['flags'],'I')===false) {
				Logger::Log('Addon '.$sub.' tried to fetch protected configuration. Attempt denied!',LOGGER_WARNING);
				break;
			}
			$conf=serialize($GLOBALS['config']);
			$conf=base64_encode($conf);
			addons_write($data,'CONF '.$conf.LF);
			break;
		default:
			Logger::Log('Unknown data from '.$sub.' : '.$rcmd,LOGGER_DEBUG);
			break;
		#
	}
}

function addons_update($addon) {
	// TODO here : serialize addon structure and send it to all addons with flag C
	return true;
}

timers_add('calc_latency',$n=null,5,-1);
function calc_latency() {
	$ADDONS=&$GLOBALS['addons'];
	$ping='PING '.microtime(true).LF;
	foreach($ADDONS['list'] as $addon=>&$info) {
		if ($info['loaded']!==true) continue;
		addons_write($info,$ping);
	}
	return true;
}

timers_add('report_latency',$n=null,300,-1);
function report_latency() {
	// report latency for all addons every 5 minutes
	$ADDONS=&$GLOBALS['addons'];
	$report=array();
	foreach($ADDONS['list'] as $addon=>&$info) {
		if ($info['loaded']!==true) continue;
		if (!isset($info['latency'])) continue;
		$lat=$info['latency'];
		$report[$addon]=$lat[1] / $lat[0];
	}
	asort($report);
	$res='';
	foreach($report as $addon=>$lat) {
		$res.=($res==''?'':' ').$addon.'='.sprintf('%01.2fms',$lat);
	}
	Logger::Log('Latency report : '.$res,LOGGER_DEBUG);
	return true;
}

