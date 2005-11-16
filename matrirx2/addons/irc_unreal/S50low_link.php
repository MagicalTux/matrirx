<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S50low_link.php : Management of the IRC link
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

$GLOBALS['irclink']=false;
$GLOBALS['irclink_wbuf']='';
$GLOBALS['irclink_stamp']=0;
$GLOBALS['irclink_key']=null;

$GLOBALS['servers']=array();
$GLOBALS['snumerics']=array(); // numeric to server array
$GLOBALS['users']=array();
$GLOBALS['usern']=array();
$GLOBALS['chans']=array();

timers_add('irclink_check_link',$n=null,5,-1);
triggers_add('irclink_check_link',$n=null,'child_ready');
triggers_add('irclink_check_link',$n=null,'tp_conf');
triggers_add('irclink_do_send',$n=null,'noop');
triggers_add('irclink_get_data',$n=null,'noop');

function irclink_nickchars($str) {
	// latin1=cat,dut,fre,ger,ita,spa,swe
	$str=explode(',',$str);
	$res=array();
	foreach($str as $t) {
		if ($t=='latin1') {
			$res[]='cat';
			$res[]='dut';
			$res[]='fre';
			$res[]='ger';
			$res[]='ita';
			$res[]='spa';
			$res[]='swe';
			continue;
		}
		$res[]=substr($t,0,3);
	}
	array_unique($res);
	sort($res);
	return implode(',',$res);
}

function irclink_protoctl() {
	if (!isset($GLOBALS['config'])) return '';
	$conf=&$GLOBALS['config'];
	// we will get : PROTOCTL NOQUIT TOKEN NICKv2 SJOIN SJOIN2 UMODE2 VL SJ3 NS SJB64 TKLEXT NICKIP CHANMODES=beI,kfL,lj,psmntirRcOAQKVGCuzNSMTG NICKCHARS=
	$line='PROTOCTL';
	$line.=' NOQUIT'; // we will know which users are on which server, and handle that
	if (isset($GLOBALS['tokens'])) $line.=' TOKEN';
	$line.=' NICKv2'; // new nick commands
	$line.=' SJOIN SJOIN2'; // sj1 and 2
	$line.=' UMODE2'; // User mode smaller line
	$line.=' VL'; // extended server options
	$line.=' SJ3'; // ServerJoin v3
	$line.=' NS'; // numeric codes supported
	if (function_exists('base64_ts_convert')) $line.=' SJB64'; // base64 for timestamps...
	$line.=' TKLEXT NICKIP'; // Extended TKLs, and real users IP
	$line.=' CHANMODES=beI,kfL,lj,psmntirRcOAQKVGCuzNSMTG';
	$line.=' NICKCHARS='.irclink_nickchars($conf['local']['nickchars']);
	return $line;
}

function irclink_check_link() {
	if ($GLOBALS['irclink']) return true; // connection is up and running
	// need to establish an IRC connection... first make sure we have the config
	// if we don't have the config, ask the main process & import it
	if (!isset($GLOBALS['config'])) {
		threadpipe_write('CREQ'.LF);
		return true;
	}
	irclink_check_config();
	$conf=&$GLOBALS['config'];
	if (OLD_SOCKET_MODE) {
		// In "Old socket mode", the fsockopen function will return a stream, however we need
		// a socket. So we'll use socket functions to open the connection
		$sock=socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
		if (!$sock) {
			$errno=socket_last_error();
			$errstr=socket_strerror($errno);
		} else {
			if (!socket_connect($sock,$conf['remote']['host'],$conf['remote']['port'])) {
				$errno=socket_last_error($sock);
				$errstr=socket_strerror($errno);
				socket_close($sock);
				$sock=false;
			}
		}
		socket_set_nonblock($sock);
	} else {
		$sock=@fsockopen($conf['remote']['host'],$conf['remote']['port'],$errno,$errstr,10);
		stream_set_blocking($sock,false);
	}
	if (!$sock) {
		Logger::Log('Could not connect to '.$conf['remote']['host'].':'.$conf['remote']['port'].' : ['.$errno.'] '.$errstr,LOGGER_WARNING);
		return true;
	}
	Logger::Log('Connected to '.$conf['remote']['host'].':'.$conf['remote']['port'],LOGGER_NOTICE);
	$GLOBALS['irclink']=$sock;
	socket_add($sock);
	
	$GLOBALS['remote_cap']=array();
	$GLOBALS['sync']=false; // do not set Synced state
	$GLOBALS['irclink_buf']='';
	$GLOBALS['irclink_wbuf']='';
	$GLOBALS['irclink_stamp']++;
	$GLOBALS['irclink_key']='irc_unreal_'.time().$GLOBALS['irclink_stamp'];
	
	irc_send(irclink_protoctl());
	irc_send('PASS :'.$conf['remote']['password']);
	// SERVER <servername> <hops> :U<protocol>-<versionflags>-<numeric> <info>
	$send=array();
	$send['src']=null;
	$send['command']='SERVER';
	$send['pars']=array($conf['local']['name'],1,'U'.UNREAL_PROTOCOL.'-'.UNREAL_FLAGS.'-'.$conf['local']['numeric'].' '.$conf['local']['desc']);
	irc_send($send);
	// run the command on the local server so we'll see our own clients
	// NOTE: distance changed to 0 (local)
	$send['pars'][1]=0;
	exec_command($send);
	triggers_call('connect');
	// Send message to addons!
	amsg('connect',array('stamp'=>$GLOBALS['irclink_key']));
//	socket_add();
	return true;
}

// Recall "connect" on addons which are loaded after the event has happened
triggers_add('irclink_newaddon',$n=null,'new_addon_loaded');
function irclink_newaddon($n,$data,$source) {
	if (!$GLOBALS['irclink']) return;
	return amsg_call('connect',array('stamp'=>$GLOBALS['irclink_key']),$source);
}

// Mark link as lost, and make sure everything is clean
function irclink_link_lost() {
	$cnx=$GLOBALS['irclink'];
	if (!$cnx) return false;
	socket_del($cnx);
	if (OLD_SOCKET_MODE) {
		socket_close($cnx);
	} else {
		fclose($cnx);
	}
	$GLOBALS['irclink']=false;
	$GLOBALS['irclink_buf']='';
	$GLOBALS['remote_cap']=array();
	$GLOBALS['sync']=false; // do not set Synced state
	return true;
}

function irclink_get_data() {
	$cnx=$GLOBALS['irclink'];
	if (!$cnx) return false;
	$r=array($cnx);
	if (OLD_SOCKET_MODE) {
		$res=socket_select($r,$w=null,$e=null,0);
	} else {
		$res=stream_select($r,$w=null,$e=null,0);
	}
	if ($res<1) return;
	if (OLD_SOCKET_MODE) {
		$data=socket_read($cnx,4096);
	} else {
		$data=fread($cnx,4096);
	}
	if (strlen($data)<1) {
		irclink_link_lost();
		return true;
	}
	$GLOBALS['irclink_buf'].=$data;
	while($pos=strpos($GLOBALS['irclink_buf'],LF)) {
		$cmd=substr($GLOBALS['irclink_buf'],0,$pos);
		$GLOBALS['irclink_buf']=substr($GLOBALS['irclink_buf'],$pos+1);
		$cmd=irc_parse($cmd);
		if ($cmd) exec_command($cmd);
	}
	return true;
}

function irclink_do_send() {
	$cnx=$GLOBALS['irclink'];
	if (!$cnx) {
		$GLOBALS['irclink_wbuf']='';
		return true;
	}
	if ($GLOBALS['irclink_wbuf']=='') {
		socket_del($cnx,'waitwrite');
		return true; // nothing to send
	}
	if (OLD_SOCKET_MODE) {
		$res=@socket_write($cnx,$GLOBALS['irclink_wbuf'],strlen($GLOBALS['irclink_wbuf']));
	} else {
		$res=@fwrite($cnx,$GLOBALS['irclink_wbuf']);
	}
	if ($res===false) {
		irclink_link_lost();
		return false;
	}
	$GLOBALS['irclink_wbuf']=substr($GLOBALS['irclink_wbuf'],$res); // remove written bytes
}

function irclink_check_key($key) {
	// make sure there's at least 1 lowercase letter, 1 uppercase letter & 1 number
	// Key length must be in the range 5 - 100
	if (strlen($key)<5) return false;
	if (strlen($key)>100) return false;
	$gotlower=ereg('[a-z]',$key);
	$gotupper=ereg('[A-Z]',$key);
	$gotnumbr=ereg('[0-9]',$key);
	if ($gotlower && $gotupper && $gotnumbr) return true;
	return false;
}

function irclink_check_config() {
	$conf=&$GLOBALS['config'];
	$res=true;
	for($i=0;$i<=2;$i++) $res=$res && irclink_check_key($conf['network']['cloak'][$i]);
	if (!$res) {
		Logger::Log('Error : the cloak keys you provided are not valid for Unreal !',LOGGER_EMERG);
		// Wait one second in order to allow the threadpipe to transmit our log message
		sleep(1);
		exit(94);
	}
}

function irc_func_error($data) {
	$msg='Error from IRC: '.$data['pars'][0];
	Logger::Log($msg,LOGGER_ERR);
	irclink_link_lost();
}
