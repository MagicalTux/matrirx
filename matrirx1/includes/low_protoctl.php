<?
// make "PROTOCTL" line
function make_protoctl() {
	// we will get : PROTOCTL NOQUIT TOKEN NICKv2 SJOIN SJOIN2 UMODE2 VL SJ3 NS SJB64 CHANMODES=beqa,kfL,l,psmntirRcOAQKVGCuzNSM
	$line='PROTOCTL';
	if (isset($GLOBALS['tokens'])) $line.=' TOKEN';
	$line.=' NICKv2'; // new nick commands
	$line.=' SJOIN SJOIN2'; // sj1 and 2
	$line.=' UMODE2'; // User mode smaller line
	$line.=' VL'; // extended server options
	$line.=' SJ3'; // ServerJoin v3
	$line.=' NS'; // numeric codes supported
	if (function_exists('base64_ts_convert')) $line.=' SJB64'; // base64 for timestamps...
	$line.=' SJB64';
	$line.=' CHANMODES=beqa,kfL,l,psmntirRcOAQKVGCuzNSM';
	return $line;
}