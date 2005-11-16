<?
// Hi-level core functions : SERVERS MANAGEMENT

function irc_func_server($dat) {
	$s=&$GLOBALS['servers'];
	$p=$dat['pars'];
	$n=array();
	$n['name']=$p[0];
	$n['distance']=$p[1];
	$fn=strpos($p[2],' ');
	if (substr($p[2],0,1)!='U') $fn=false;
	if ($fn) {
		$info=substr($p[2],0,$fn);
		$fn=substr($p[2],$fn+1);
		$info=explode('-',$info);
		$nfo=array();
		$nfo['version']=$info[0];
		$nfo['flags']=$info[1];
		$nfo['numeric']=$info[2];
		$GLOBALS['snumerics'][$info[2]]=$p[0];
	} else {
		$fn=$p[2];
		$nfo=null;
	}
	$n['info']=$nfo;
	$n['fullname']=$fn;
	$s[$p[0]]=$n;
}

function irc_resolve_serv($dat) {
	$conf=$GLOBALS['config'];
	$numeric=(int)$dat;
	if ($numeric == $conf['local']['numeric']) return $conf['local']['name'];
	if (isset($GLOBALS['snumerics'][$numeric])) return $GLOBALS['snumerics'][$numeric];
	return $dat;
}

function irc_func_version($dat) {
	$src=$dat['src'];
	$dest=$dat['pars'][0];
	$conf=$GLOBALS['config'];
	$send=array();
	$send['src']=$conf['local']['name'];
	$send['command']='RAWCODE';
	$send['value']=351;
	$res='MatrIRX'.MATRIRX_VERSION.'. '.$conf['local']['name'].' If you have any problem, contact MagicalTux <'._UNDERLINE.'w@ff.st'._UNDERLINE.'>';
	$send['pars']=array($src,$res);
	irc_send($send);
}
