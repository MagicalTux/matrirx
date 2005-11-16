<?
// connect =p

function irc_connect() {
	echo "Connecting to the network...".CRLF;
	if ($GLOBALS['link']) return; // don't connect if already online
	$conf=$GLOBALS['config'];
	$cnx=fsockopen($conf['remote']['host'],$conf['remote']['port'],$errno,$errstr,25);
	if (!$cnx) {
		echo 'Can\'t connect : Error ['.$errno.'] : '.$errstr."\n";
		return;
	}
	stream_set_blocking($cnx,false);
	stream_set_timeout($cnx,0,200000);
	if (feof($cnx)) {
		echo 'Disconnected !'.CRLF;
		return;
	}
	// THESE LINES ARE PROTECTED AND DO NOT PASS THROUGH THE FULL irc_send
	$GLOBALS['link']=$cnx;
	irc_send(make_protoctl());
	irc_send('PASS :'.$conf['remote']['password']);
	
	$GLOBALS['servers']=array();
	$GLOBALS['snumerics']=array(); // numeric to server array
	$GLOBALS['users']=array();
	$GLOBALS['chans']=array();
	$GLOBALS['remote_cap']=array();
	$GLOBALS['sync']=false; // do not set Synced state
	
	// SERVER <servername> <hops> :U<protocol>-<versionflags>-<numeric> <info>
	$send=array();
	$send['src']=null;
	$send['command']='SERVER';
	$send['pars']=array($conf['local']['name'],1,'U'.UNREAL_PROTOCOL.'-FiOo-'.$conf['local']['numeric'].' '.$conf['local']['desc']);
	irc_send($send);
	
	// run on-connect scripts
	callmod('connect');
	// after we called "connect" (sync) we send our own NETINFO & EOS
	irc_send(make_netinfo());
	$send=array();
	$send['src']=NULL;
	$send['command']='EOS';
	$send['pars']=array();
	irc_send($send);

	return true;
}
