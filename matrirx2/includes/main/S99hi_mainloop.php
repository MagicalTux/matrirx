<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2004 Robert Karpeles.
 * S99hi_mainloop.php : Main MatrIRX loop
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

/*
timers_add('do_globaldump',$n=null,300,-1);
function do_globaldump() {
	echo 'Dumping global scope ...'.LF;
	$fil=@fopen('/globaldump.txt','w');
	if (!$fil) {
		echo 'Fatal: Could not open /globaldump.txt for writing.'.LF;
		return false; // stop timer
	}
	fwrite($fil,print_r($GLOBALS,true));
	fclose($fil);
	return true;
}
*/

// if we return "true" we'll spawn a child
function main_loop() {
	triggers_call('noop'); // call this event on each loop
	socket_wait(5);
	return addons_getload();
}
