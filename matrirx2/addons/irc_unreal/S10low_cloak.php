<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S10low_cloak.php : Unreal new cloak algorythm
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

function cloak_host($host) {
	// Detect if host is ipv4, ipv6 or dnshost and cloak it
	if (strpos($host,':')!==false) { // only ipv6 contains ':'
		return cloak_ipv6($host);
	} elseif (ereg('^[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}$',$host)) {
		return cloak_ipv4($host);
	} else {
		return cloak_normalhost($host);
	}
}

function cloak_ipv6($ip) {
	$conf=$GLOBALS['config'];
	$key1=$conf['network']['cloak'][0];
	$key2=$conf['network']['cloak'][1];
	$key3=$conf['network']['cloak'][2];
	
	/* 
	 * Output: ALPHA:BETA:GAMMA:IP
	 * ALPHA is unique for a:b:c:d:e:f:g:h
	 * BETA  is unique for a:b:c:d:e:f:g
	 * GAMMA is unique for a:b:c:d
	 * We cloak like this:
	 * ALPHA = downsample(md5(md5("KEY2:a:b:c:d:e:f:g:h:KEY3")+"KEY1"));
	 * BETA  = downsample(md5(md5("KEY3:a:b:c:d:e:f:g:KEY1")+"KEY2"));
	 * GAMMA = downsample(md5(md5("KEY1:a:b:c:d:KEY2")+"KEY3"));
	 */
	
	$ip=explode(':',$ip);
	$ip1=$ip[0].':'.$ip[1].':'.$ip[2].':'.$ip[3];
	$ip2=$ip1  .':'.$ip[4].':'.$ip[5].':'.$ip[6];
	$ip3=$ip2  .':'.$ip[7];
	
	$alpha = cloak_downsample(md5(md5($key2.':'.$ip3.':'.$key3,true).$key1,true));
	$beta  = cloak_downsample(md5(md5($key3.':'.$ip2.':'.$key1,true).$key2,true));
	$gamma = cloak_downsample(md5(md5($key1.':'.$ip1.':'.$key2,true).$key3,true));
	return sprintf('%X:%X:%X:IP', $alpha, $beta, $gamma);;
}

function cloak_ipv4($ip) {
	$conf=$GLOBALS['config'];
	$key1=$conf['network']['cloak'][0];
	$key2=$conf['network']['cloak'][1];
	$key3=$conf['network']['cloak'][2];
	
	/* 
	 * Output: ALPHA.BETA.GAMMA.IP
	 * ALPHA is unique for a.b.c.d
	 * BETA  is unique for a.b.c.*
	 * GAMMA is unique for a.b.*
	 * We cloak like this:
	 * ALPHA = downsample(md5(md5("KEY2:A.B.C.D:KEY3")+"KEY1"));
	 * BETA  = downsample(md5(md5("KEY3:A.B.C:KEY1")+"KEY2"));
	 * GAMMA = downsample(md5(md5("KEY1:A.B:KEY2")+"KEY3"));
	 */
	
	$ip=explode('.',$ip);
	$alpha = cloak_downsample(md5(md5($key2.':'.$ip[0].'.'.$ip[1].'.'.$ip[2].'.'.$ip[3].':'.$key3,true).$key1,true));
	$beta  = cloak_downsample(md5(md5($key3.':'.$ip[0].'.'.$ip[1].'.'.$ip[2].           ':'.$key1,true).$key2,true));
	$gamma = cloak_downsample(md5(md5($key1.':'.$ip[0].'.'.$ip[1].                      ':'.$key2,true).$key3,true));
	return sprintf('%X.%X.%X.IP', $alpha, $beta, $gamma);
}

function cloak_normalhost($host) {
	$conf=$GLOBALS['config'];
	$key1=$conf['network']['cloak'][0];
	$key2=$conf['network']['cloak'][1];
	$key3=$conf['network']['cloak'][2];
	$prefix=$conf['network']['hiddenhost-prefix'];
	
	// Things are getting more simple for this one
	
	$alpha = cloak_downsample(md5(md5($key1.':'.$host.':'.$key2,true).$key3,true));
	// find position of a dot (.) followed by an alphabetic character
	$pos=0;
	while($pos<strlen($host)) {
		$pos=strpos($host,'.',$pos);
		if ($pos===false) {
			$pos=strlen($host);
			continue;
		}
		$c=ord($host{$pos+1});
		if ( (($c>=97) && ($c<=122)) || (($c>=65) && ($c<=90)) ) break;
		$pos++;
	}
	if ($pos<strlen($host)) {
		$host=substr($host,$pos+1);
		$res=sprintf('%s-%X.', $prefix, $alpha);
		$len=strlen($res)+strlen($host);
		if ($len <= HOSTLEN) {
			return $res.$host;
		}
		return $res.substr($host,$len - HOSTLEN);
	}
	return sprintf('%s-%X', $prefix, $alpha);
}

function cloak_checksum() {
	$conf=$GLOBALS['config'];
	$key1=$conf['network']['cloak'][0];
	$key2=$conf['network']['cloak'][1];
	$key3=$conf['network']['cloak'][2];
	// We need to do a "Network Bytes Order"ed md5 (low nibble first)
	// Thanks to unpack, it's easy :)
	$md5=md5($key1.':'.$key2.':'.$key3,true);
	list(,$md5_str)=@unpack('h*',$md5); // this will generate a warning on PHP5.0
	return 'MD5:'.$md5_str;
}

function cloak_downsample($bytes) {
	// downsample~
	$r0=$bytes{ 0} ^ $bytes{ 1} ^ $bytes{ 2} ^ $bytes{ 3};
	$r1=$bytes{ 4} ^ $bytes{ 5} ^ $bytes{ 6} ^ $bytes{ 7};
	$r2=$bytes{ 8} ^ $bytes{ 9} ^ $bytes{10} ^ $bytes{11};
	$r3=$bytes{12} ^ $bytes{13} ^ $bytes{14} ^ $bytes{15};
	return ( (ord($r0) << 24) | (ord($r1) << 16) | (ord($r2) << 8) | (ord($r3)) );
}

