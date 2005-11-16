<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2004 Robert Karpeles.
 * S05low_timers.php : Timers system
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
 * MagicalTux@FF.st or by postal mail : Robert Karpeles, 40, Rue Veron, 
 * 94140 Alfortville, FRANCE
 */

$GLOBALS['timers']=array();
$GLOBALS['timers_wait']=null;
timers_init();
function timers_init() {
	$TIMERS=&$GLOBALS['timers'];
	$TIMERS['timers']=array(); // let's empty timer list
	$TIMERS['ctime']=timer_getmicrotime(); // store it and call it only once a loop
	$TIMERS['timers_id']=0;
	triggers_add('timers_noop',$s=null,'noop'); // called on no operation (each loop)
	triggers_add('timers_init',$s=null,'child'); // called when child is spawned
}

function timers_noop() {
	$TIMERS=&$GLOBALS['timers'];
	$ctime=$TIMERS["ctime"]=timer_getmicrotime(true);
	// check for timers...
	$run=array();
	foreach($TIMERS['timers'] as $id=>$data) {
		if ($data['timer']<=$ctime) {
			$run[]=$id;
		}
	}
	foreach($run as $id) {
		// run timer
		if (!timers_run_it($id)) unset($TIMERS['timers'][$id]);
	}
	$low=0;
	foreach($TIMERS['timers'] as $id=>$data) {
		if (!$low) $low=$data['timer'];
		$low=($low<$data['timer'])?$low:$data['timer']; // get next timer
	}
	if (!$low) {
		$GLOBALS['timers_wait']=null;
	} else {
		$low-=timer_getmicrotime();
		$GLOBALS['timers_wait']=$low;
	}
}

/**
 * @return int Timer id of the newly created timer (or bool false in case of error)
 * @param string $func Name of function called by timer
 * @param array &$parameters Function's parameters
 * @param float $delay Delay between calls
 * @param int $repeats How many times this timer should be repeated. -1 for infinite
 * @param int $id Enforces timer id. Use null if you don't want to give it
 * @param string $endfunc Function to call once reached $repeats calls
 * @desc Adds a timer to the system. The timer will be called every $delay
 */
function timers_add($func,&$parameters,$delay=2,$repeats=1,$id=null,$endfunc='') {
	$TIMERS=&$GLOBALS['timers'];
	if (($repeats<=0) and ($repeats!=-1)) return false;
	if ($delay<0) $delay=0;
	$timer=array();
	$timer['timer']=$TIMERS['ctime']+$delay;
	$timer['max_exec']=$repeats;
	$timer['func']=$func;
	$timer['par']=&$parameters;
	$timer['endfunc']=$endfunc;
	$timer['idle']=$delay; // save it :)
	if (is_null($id)) {
		while(isset($TIMERS['timers'][$TIMERS['timers_id']])) $TIMERS['timers_id']++;
	}
	$id=$TIMERS['timers_id'];
//  nzl_log(NZL_DEBUG,"Debug.TimersLib: Adding timer id $id - function: $func - delay : $delay - repeats : $repeats ");
	$TIMERS['timers'][$id]=&$timer;
	return $id;
}

/**
 * @return bool Success
 * @param int $id Id of timer you wish to delete
 * @param string $func Name of attached function if you want to ensure the right timer is deleted
 * @desc Deletes a timer from the system. Note that $endfunc will *not* be called.
 */
function timers_delete($id,$func=null) {
	$TIMERS=&$GLOBALS['timers'];
	if (isset($TIMERS["timers"][$id])) {
		if (!is_null($func)) {
			if ($TIMERS["timers"][$id]['func']!=$func) return false;
		}
		unset($TIMERS["timers"][$id]);
		return true;
	}
	return false;
}

function timers_exists($id) {
	$TIMERS=&$GLOBALS['timers'];
	if (isset($TIMERS["timers"][$id])) return true;
	return false;
}

function timers_run_it($id) {
	$TIMERS=&$GLOBALS['timers'];
	// run timer id
//	nzl_log(NZL_DEBUG,"Debug.TimersLib: Running timer $id ...");
	if (!isset($TIMERS["timers"][$id])) {
//		nzl_log(NZL_DEBUG,"Debug.TimersLib: Error : no such timer $id !!");
		return;
	}
	$data=&$TIMERS["timers"][$id];
	$func=$data["func"];
	$TIMERS["timers"][$id]["timer"]=$TIMERS["ctime"]+$TIMERS["timers"][$id]["idle"];
	if ($data["max_exec"]==0) {
		$ef=$data["endfunc"];
		if (($ef) and (function_exists($ef))) {
			if (!is_null($data["par"])) {
				$ef($data["par"]);
			} else {
				$ef();
			}
		}
//		nzl_log(NZL_DEBUG,"Debug.TimersLib: Timer $id has expired.");
		return false;
	}
	if ($data["max_exec"]>0) {
		$data["max_exec"]-=1;
	}
	if (!function_exists($func)) {
//		nzl_log(NZL_DEBUG,"Debug.TimersLib: Alert! Timer $id is calling non-existing function $func !");
		return false;
	}
	if (!is_null($data["par"])) {
		$res=$func($data["par"]);
	} else {
		$res=$func();
	}
	if (!$res) {
//		nzl_log(NZL_DEBUG,"Debug.TimersLib: Closing timer $id - function $func returned false");
		// return false ---> delete timer
		return false;
	}
	return true;
}

// function timer_getmicrotime found in sample on the PHP doc at :
// http://fr2.php.net/microtime
// Will be changed for PHP 5 to use get as float
function timer_getmicrotime($refresh=false) {
	if (!isset($GLOBALS['getmicrotime'])) $refresh=true;
	if (!$refresh) return $GLOBALS['getmicrotime'];
	$res=microtime(true);
	$GLOBALS['getmicrotime']=$res;
	return $res;
}
