<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S20low_privmsg.php : communication commands
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

function irc_func_privmsg($dat) {
	// <source> PRIVMSG <target> :<message>
	$conf=$GLOBALS['config'];
	$p=$dat['pars'];
	$dst=explode(',',$p[0]);
	$msg=array('message'=>$p[1],'src'=>irc_get_case($dat['src']));
	foreach($dst as $dest) {
		$dest=strtolower($dest);
		$msg['target']=irc_get_case($dest);
		if ($dest{0}=='#') {
			// we're parsing a privmsg to a chan !
			if (!isset($GLOBALS['chans'][$dest])) return; // unknown chan
			$u=$GLOBALS['chans'][$dest]['users'];
			foreach($u as $user=>$joined) {
				amsg('pubmsg',$msg,strtolower($user));
			}
		} else {
			$dest=explode('@',$dest);
			if (isset($dest[1])) {
				if ($dest[1]!=strtolower($conf['local']['name'])) continue;
			}
			$dest=$dest[0];
			amsg('privmsg',$msg,$dest);
		}
	}
}

// TODO: Implement multi-target support and channel targettype support
function irc_func_notice($dat) {
	$conf=$GLOBALS['config'];
	$p=$dat['pars'];
	$dest=strtolower($p[0]);
	// TODO: Handle notices to ops, etc..
	if (substr($dest,0,1)=='~') return;
	if (substr($dest,0,1)=='&') return;
	if (substr($dest,0,1)=='@') return;
	if (substr($dest,0,1)=='%') return;
	if (substr($dest,0,1)=='+') return;
	
	if (substr($dest,0,1)!='#') {
		$dest=explode('@',$dest);
		if (isset($dest[1])) {
			if ($dest[1]!=strtolower($conf['local']['name'])) return;
		}
		amsg('notice',$dat,$dest[0]);
	} else {
		// pubnotice
		if (!isset($GLOBALS['chans'][$dest])) return; // unknown chan
		$u=$GLOBALS['chans'][$dest]['users'];
		foreach($u as $user=>$joined) {
			amsg('pubnotice',$dat,$user);
		}
	}
}
