<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * i10low_addonsmsg.php : Addons messages management
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

function callmod($event,$pars=null,$mod=null) {
	list($stack)=debug_backtrace();
	$info=$stack['file'].':'.$stack['line'];
	echo 'WARNING: Old callmod() called at '.$info.'!'.LF;
	amsg($event,$pars,$mod);
}

function amsg($event,$pars=null,$nick=null) {
	if (!is_null($nick)) {
		$addonlist=array();
		if (!is_array($nick)) $nick=array($nick);
		foreach($nick as $nickn) {
			$nickn=strtolower($nickn);
			if ($nickn{0}=='#') {
				if (!isset($GLOBALS['chans'][$nickn])) continue;
				foreach($GLOBALS['chans'][$nickn]['users'] as $user=>$info) {
					if (!isset($GLOBALS['users'][$user])) continue;
					if (!isset($GLOBALS['users'][$user]['addon'])) continue;
					foreach($GLOBALS['users'][$user]['addon'] as $addon=>$foo) {
						$pars['called_user'][$addon][$user]=$user;
						$addonlist[$addon]=true;
					}
				}
			} else {
				if (!isset($GLOBALS['users'][$nickn])) continue;
				if (!isset($GLOBALS['users'][$nickn]['addon'])) continue;
				foreach($GLOBALS['users'][$nickn]['addon'] as $addon=>$foo) {
					$pars['called_user'][$addon][$nickn]=$nickn;
					$addonlist[$addon]=true;
				}
			}
		}
		foreach($addonlist as $addon=>$ignore) {
			amsg_call($event,$pars,$addon);
		}
		return;
	}
	amsg_call($event,$pars);
}

function includepars(&$pars,$what) {
	if (is_null($pars)) return;
	$what=strtolower($what);
	if (!$what) return;
	if ($what{0}=='#') {
		// include channel
		if (isset($GLOBALS['chans'][$what])) {
			$pars['cache']['chans'][$what]=&$GLOBALS['chans'][$what];
		} else {
			$pars['cache']['chans'][$what]=null;
		}
		return;
	}
	// include user
	if (isset($GLOBALS['users'][$what])) {
		$pars['cache']['users'][$what]=&$GLOBALS['users'][$what];
	} else {
		$pars['cache']['users'][$what]=null;
	}
}

function amsg_call($fnc,$pars=null,$addon=null) {
	// Check for additionnal parameters
	if (!is_null($pars)) {
		if (!is_array($pars)) {
			var_dump($pars);
			var_dump(debug_backtrace());
			return;
		}
		if (isset($pars['user'])) includepars($pars,$pars['user']);
		if (isset($pars['src'])) includepars($pars,$pars['src']);
		if (isset($pars['target'])) includepars($pars,$pars['target']);
		if (isset($pars['new'])) includepars($pars,$pars['new']);
		if (isset($pars['chan'])) includepars($pars,$pars['chan']);
	}
	// encode parameters
	$pars=serialize($pars);
	$pars=base64_encode($pars);
	if (!is_null($addon)) {
		// send message to this addon
		// AddonMeSsaGe
		threadpipe_write(':'.$addon.' CALL '.$fnc.' '.$pars.LF);
		return;
	}
	// AddonBroadcastMessaGe
	threadpipe_write('ABMG CALL '.$fnc.' '.$pars.LF);
}

