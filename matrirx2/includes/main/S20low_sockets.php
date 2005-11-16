<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S20low_sockets.php : Socket management functions
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

socket_init();
triggers_add('socket_init',$s=null,'child'); // called when child is spawned
function socket_init() {
	$GLOBALS['socketlist']=array();
	$GLOBALS['waitwrite']=array();
}

function socket_wait($timeout=null) {
	if (isset($GLOBALS['addons'])) {
		$ADDONS=&$GLOBALS['addons'];
		if ($ADDONS['loadcount']>0) return;
	}
	$r=$GLOBALS['socketlist'];
	$w=$GLOBALS['waitwrite'];
	$to2=0;
	if (is_null($timeout)) {
		$timeout=$GLOBALS['timers_wait'];
	}
	if ( (!is_null($GLOBALS['timers_wait'])) && ($GLOBALS['timers_wait']<$timeout)) $timeout=$GLOBALS['timers_wait'];
	if (!is_null($timeout)) {
		$to2=$timeout;
		$timeout=intval($timeout);
		$to2-=$timeout;
		$to2=$to2*1000000; // microtime
	}
	if (OLD_SOCKET_MODE) {
		if ($to2) {
			$count=@socket_select($r,$w,$e=array(),$timeout,$to2);
		} else {
			$count=@socket_select($r,$w,$e=array(),$timeout);
		}
	} else {
		if ($to2) {
			$count=@stream_select($r,$w,$e=array(),$timeout,$to2);
		} else {
			$count=@stream_select($r,$w,$e=array(),$timeout);
		}
	}
	return $count;
}

function socket_add($sock,$list='socketlist') {
	$GLOBALS[$list][$sock]=$sock;
//	echo $list.'.add('.print_r($sock,true).')'.LF;
}

function socket_del($sock,$list='socketlist') {
	if (isset($GLOBALS[$list][$sock])) unset($GLOBALS[$list][$sock]);
//	echo $list.'.del('.print_r($sock,true).')'.LF;
}
