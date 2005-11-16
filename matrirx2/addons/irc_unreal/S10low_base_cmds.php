<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 205 Mark Karpeles.
 * S10low_base_cmds.php : Basic irc commands
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

function irc_func_pass($dat) {
	return true; // ignore this call
}

function irc_func_protoctl($dat) {
	$p=array();
	foreach($dat['pars'] as $prot) {
		$prot=explode('=',$prot);
		if (!isset($prot[1])) $prot[1]=true;
		$p[strtoupper($prot[0])]=$prot[1];
	}
	$GLOBALS['remote_cap']=$p;
	return true;
}

function irc_func_away($dat) {
	if (is_null($dat['src'])) return false;
	if (!isset($GLOBALS['users'][$dat['src']])) return false;
	if ( (isset($dat['pars'][0])) and ($dat['pars'][0]!='')) {
		$GLOBALS['users'][$dat['src']]['away']=$dat['pars'][0];
	} else {
		unset($GLOBALS['users'][$dat['src']]['away']);
	}
}

// ignore server notices
function irc_func_smo($dat) {
	return true;
}

function irc_func_ping($dat) {
	$code=$dat['pars'][0];
	$answer=array();
	$answer['command']='PONG';
	$answer['src']=null;
	$answer['pars']=array(':'.$code);
	return $answer;
}

function irc_func_version($dat) {
	$src=$dat['src'];
	$dest=$dat['pars'][0];
	$conf=$GLOBALS['config'];
	$send=array();
	$send['src']=$conf['local']['name'];
	$send['command']='RAWCODE';
	$send['value']=351;
	$res='MatrIRX'.MATRIRX_VERSION.'. '.$conf['local']['name'].' Help available on the MatrIRX website <'._UNDERLINE.'http://matrirx.ff.st/'._UNDERLINE.'>';
	$send['pars']=array($src,$res);
	irc_send($send);
}
