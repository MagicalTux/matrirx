<?php

/* MatrIRX, Modular IRC Services
 * S00base_compat.php : PHP 5.0.0 / 5.1-dev compatibility
 * $Id$
 *
 * Copyright (C) 2004 Robert Karpeles.
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
 * MagicalTux@FF.st or by postal mail : Robert Karpeles, 40, Rue Veron, 
 * 94140 Alfortville, FRANCE
 *
 * The goal of this file is to provide the functions missing in PHP 5.0.0 but
 * which can be found in PHP 5.1-dev.
 *
 * List of the functions :
 *  - stream_socket_pair
 *  - inet_ntop
 */

if ( (!function_exists('stream_socket_pair')) && (function_exists('socket_create_pair')) ) {
	if (!defined('STREAM_PF_UNIX')) define('STREAM_PF_UNIX',AF_UNIX);
	if (!defined('STREAM_SOCK_STREAM')) define('STREAM_SOCK_STREAM',SOCK_STREAM);
	define('OLD_SOCKET_MODE',true);
	function stream_socket_pair($domain,$type,$protocol) {
		$pair=array();
		if (socket_create_pair($domain,$type,$protocol,$pair)) {
			return $pair;
		}
		$err=socket_last_error();
		$errstr=socket_strerror($err);
		var_dump($pair);
		Logger::Log('Could not create socket pair : ['.$err.'] '.$errstr,LOGGER_ERR);
		return false;
	}
} else {
	define('OLD_SOCKET_MODE',false);
}

if (!function_exists('inet_ntop')) {
	function inet_ntop($ip) {
		if (strlen($ip)==4) {
			// IPv4
			list(,$ip)=unpack('N',$ip);
			$ip=long2ip($ip);
		} elseif(strlen($ip)==16) {
			// IPv6
			$ip=bin2hex($ip);
			$ip=substr(chunk_split($ip,4,':'),0,-1);
			$ip=explode(':',$ip);
			$res='';
			foreach($ip as $seg) {
				while($seg{0}=='0') $seg=substr($seg,1);
				if ($seg!='') {
					$res.=($res==''?'':':').$seg;
				} else {
					if (strpos($res,'::')===false) {
						if (substr($res,-1)==':') continue;
						$res.=':';
						continue;
					}
					$res.=($res==''?'':':').'0';
				}
			}
			$ip=$res;
		}
		return $ip;
	}
}
