<?
/* CallMod function - low part of the code
 *
 * Updated 31/05/2004 : added callstack
 */

$GLOBALS['CALLMOD_STACK']=array();
define('CALLMOD_MAXCALL',5); // you can call recusrively a module up to 5 times by default

function callmod($event,$pars=null,$mod=null) {
	if (!is_null($mod)) {
		if (!is_array($mod)) $mod=array($mod);
		foreach($mod as $modn) {
			$fnc='mod_'.strtolower($modn).'_'.strtolower($event);
			if (!function_exists($fnc)) continue;
			callmod_call($fnc,$pars);
		}
		return;
	}
	$fncs=get_defined_functions();
	$fncs=$fncs['user'];
	$event='_'.strtolower($event);
	foreach($fncs as $fnc) {
		if (substr($fnc,0,4)!='mod_') continue;
		if (substr($fnc,0-strlen($event))!=$event) continue;
		callmod_call($fnc,$pars);
	}
}

function callmod_call($fnc,$pars=null) {
	// check if function is already in the stack
	$stack=$GLOBALS['CALLMOD_STACK'];
	// find stack id while scanning ;)
	$stackid=1;
	$occ=0; // occur
	foreach($stack as $level=>$call) {
		if ($level>=$stackid) $stackid=$level+1;
		if ($call['func']==$fnc) {
			$occ++;
			if ($occ<CALLMOD_MAXCALL) continue;
			mylog('WARNING: Recursive call of function '.$fnc.' disallowed. - CallStack output done to Debug (if enabled)');
			if ((defined('DEBUG')) && (DEBUG)) {
				var_output($stack);
			}
			return false;
		}
	}
	$call=array('func'=>$fnc,'pars'=>$pars);
	$GLOBALS['CALLMOD_STACK'][$stackid]=$call;
	if (!is_null($pars)) {
		$res=$fnc($pars);
	} else {
		$res=$fnc();
	}
	$GLOBALS['CALLMOD_STACK'][$stackid]=null;
	unset($GLOBALS['CALLMOD_STACK'][$stackid]);
	return $res;
}

