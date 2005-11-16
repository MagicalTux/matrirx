<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S10low_exec.php : Execute an irc statement
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

function exec_command($dat) {
	// find a command
	$cmd=strtolower($dat['command']);
	$cmd='irc_func_'.$cmd;
	if (!function_exists($cmd)) $cmd='irc_unknown_func';
//	echo 'calling '.$cmd.LF;
	$res=$cmd($dat);
	if (is_array($res)) irc_send($res);
}

function irc_unknown_func($dat) {
	$conf=&$GLOBALS['config'];
	if ( (defined('DEBUG')) && DEBUG) {
		irc_privmsg(null,$conf['network']['controlchan'],'WARNING: Unknown function called : `'.$dat['command'].'\' !',false); // no callback
//		irc_privmsg(null,$conf['network']['controlchan'],$dat['raw'],false); // no callback
		$data=print_r($dat,true);
		$data=explode(LF,$data);
		foreach($data as $lin) {
			$lin=rtrim($lin);
			irc_privmsg(null,$conf['network']['controlchan'],$lin,false);
		}
	}
}
