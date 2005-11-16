<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S02low_triggers.php : Triggers management system
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

$GLOBALS['triggers']=array();
triggers_init();
function triggers_init() {
	$TRIGGERS=&$GLOBALS['triggers'];
	$TRIGGERS['list']=array();
}

function triggers_add($func,&$parameters,$event) {
	// register a function to be called when an event is triggered
	$TRIGGERS=&$GLOBALS['triggers'];
	$TRIGGERS['list'][$event][$func]=&$parameters;
}

function triggers_del($func,$event) {
	$TRIGGERS=&$GLOBALS['triggers'];
	if (!isset($TRIGGERS['list'][$event])) return false;
	if (!array_key_exists($func,$TRIGGERS['list'][$event])) return false;
	unset($TRIGGERS['list'][$event][$func]);
	return true;
}

function triggers_call($event,$data=null,$origin=null) {
	// call all functions of a trigger
	$TRIGGERS=&$GLOBALS['triggers'];
	if (!isset($TRIGGERS['list'][$event])) return 0;
	$list=&$TRIGGERS['list'][$event];
	$fnc=array();
	foreach($list as $func=>$pars) {
		$fnc[]=$func;
	}
	if ($event=='child') {
		// special event, we need to cleanup triggers AT THE BEGINNING
		triggers_init();
	}
	foreach($fnc as $func) {
		$func($list[$func],$data,$origin);
	}
	return count($fnc);
}
