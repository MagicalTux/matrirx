<?

function irc_parse($lin) {
	$lin=trim($lin);
	if (!$lin) return;
//	echo 'IN: '.$lin.CRLF;
	$par=explode(' ',$lin);
	
	$cmd=array();
	if (substr($lin,0,1)==':') {
		// this command is from someone
		$cmd['src']=substr($par[0],1);
		$cmd['command']=$par[1];
		$join=false; $npar=0;
		$pars=array();
		for($i=2;isset($par[$i]);$i++) {
			if ($join) {
				$pars[$npar].=' '.$par[$i];
			} else {
				$pars[$npar]=$par[$i];
				$npar++;
				if (substr($par[$i],0,1)==':') {
					$join=true;
					$npar-=1;
					$pars[$npar]=substr($par[$i],1);
				}
			}
		}
		$cmd['pars']=$pars;
	} elseif(substr($lin,0,1)=='@') {
		// this command is from a server
		$cmd['src']=null;
		$cmd['server']=substr($par[0],1);
		$t=irc_resolve_serv($cmd['src']);
		if ($t) $cmd['src']=$t;
		$cmd['command']=$par[1];
		$join=false; $npar=0;
		$pars=array();
		for($i=2;isset($par[$i]);$i++) {
			if ($join) {
				$pars[$npar].=' '.$par[$i];
			} else {
				$pars[$npar]=$par[$i];
				$npar++;
				if (substr($par[$i],0,1)==':') {
					$join=true;
					$npar-=1;
					$pars[$npar]=substr($par[$i],1);
				}
			}
		}
		$cmd['pars']=$pars;
	} else {
		// command from server
		$cmd['src']=null;
		$cmd['server']=null;
		$cmd['command']=$par[0];
		$join=false; $npar=0;
		$pars=array();
		for($i=1;isset($par[$i]);$i++) {
			if ($join) {
				$pars[$npar].=' '.$par[$i];
			} else {
				$pars[$npar]=$par[$i];
				$npar++;
				if (substr($par[$i],0,1)==':') {
					$join=true;
					$npar-=1;
					$pars[$npar]=substr($par[$i],1);
				}
			}
		}
		$cmd['pars']=$pars;
	}
	$cmd['raw']=$lin;
	if (function_exists('resolve_token')) $cmd['command']=resolve_token($cmd['command']);
	$c=$cmd['command'];
	if ( ($c) == ((string)((int)$c))) {
		$cmd['value']=(int)$c;
		$cmd['command']='rawcode';
	}
	return $cmd;
}

function irc_send($lin) {
	if (!is_array($lin)) {
		// send as raw
		$lin=trim($lin);
		$cnx=$GLOBALS['link'];
		if (!$cnx) return false;
//echo 'OUT:'.$lin.CRLF;
		fputs($cnx,$lin.CRLF);
		return true;
	}
	// build line
	$res='';
	if (isset($lin['src'])) {
		$res.=':'.$lin['src'].' ';
	}
	if (function_exists('make_token')) $lin['command']=make_token($lin['command']);
	if ($lin['command']=='RAWCODE') $lin['command']=$lin['value'];
	$res.=$lin['command'];
	$join=false;
	foreach($lin['pars'] as $par) {
		if ($join) {
			$res.=' '.$par;
		} else {
			if (strpos($par,' ')!==false) {
				$join=true;
				$res.=' :'.$par;
			} else {
				$res.=' '.$par;
			}
		}
	}
	$cnx=$GLOBALS['link'];
	if (!$cnx) return false;
//	echo 'OUT:' .$res.CRLF;
	fputs($cnx,$res.CRLF);
	return true;
}