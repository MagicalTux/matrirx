<?
// check accesses

function is_services_root($nick) {
	// check if services root
	$conf=$GLOBALS['config'];
	$acc=explode(' ',$conf['local']['root']);
	foreach($acc as $root) {
		if ($nick==$root) return true;
	}
	return false;
}