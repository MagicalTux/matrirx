<?
function irc_func_netinfo($dat) {
	// we will probably never need this function :D
	return true;
}

function make_netinfo() {
	$conf=$GLOBALS['config'];
	$send=array();
	$send['src']=null;
	$send['command']='NETINFO';
	$p=array();
	$p[]=0; // global max users - unknown to services
	$p[]=time(); // TSTime
	$p[]=UNREAL_PROTOCOL; // protocol
	$p[]=$conf['network']['cloak'];
	$p[]=0; // unknown 
	$p[]=0;
	$p[]=0;
	$p[]=$conf['network']['ircnetwork']; // network
	$send['pars']=$p;
	return $send;
}
