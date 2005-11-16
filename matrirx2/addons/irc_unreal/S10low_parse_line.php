<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S10low_parse_line.php : IRC Line parser for Unreal
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

// Parse data from the IRC server
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
		$cmd['server']=(int)substr($par[0],1);
		$t=irc_resolve_serv($cmd['server']);
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

// Send data to the IRC Server
function irc_send($lin) {
	$cnx=$GLOBALS['irclink'];
	if (!$cnx) return false;
	if (!is_array($lin)) {
		// send as raw
		$lin=trim($lin);
//echo 'OUT:'.$lin.CRLF;
		$GLOBALS['irclink_wbuf'].=$lin.CRLF;
		socket_add($cnx,'waitwrite');
		return true;
	}
	// build line
	$res='';
	if (isset($lin['src'])) {
		$res.=':'.$lin['src'].' ';
	}
	if ($lin['command']=='RAWCODE') {
		$lin['command']=$lin['value'];
	} else {
		if (function_exists('make_token')) $lin['command']=make_token($lin['command']);
	}
	$res.=$lin['command'];
	$join=false;
	foreach($lin['pars'] as $par) {
		if ($join) {
			$res.=' '.$par;
		} else {
			if (($par) && ($par{0}==':')) {
				$join=true;
				$res.=' '.$par;
			} elseif (strpos($par,' ')!==false) {
				$join=true;
				$res.=' :'.$par;
			} else {
				$res.=' '.$par;
			}
		}
	}
//	echo 'OUT:' .$res.CRLF;
	$GLOBALS['irclink_wbuf'].=$res.CRLF;
	socket_add($cnx,'waitwrite');
	return true;
}
