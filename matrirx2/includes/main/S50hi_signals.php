<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2004 Robert Karpeles.
 * S50hi_signals.php : System Signals handling
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

signals_init();
function signals_init() {
	$signal_list=array(
		SIGHUP, // Reload configuration files
		SIGTERM, // Terminate process & childs
		SIGINT, // Ctrl-C - INTerrupt
		SIGQUIT, // quit ? O.o
		SIGILL, // clean & exit
		SIGTRAP, // clean & exit
		SIGSEGV, // clean & exit
		SIGUSR1, // user-defined
		SIGUSR2, // user-defined
	);
	foreach($signal_list as $signal) pcntl_signal($signal,'signals_handle');
}

function signal_reload() {
	$res=load_config();
	addons_reload_list();
	return $res; // true : ok - false : failed
}

function signals_handle($signal) {
	switch($signal) {
		case SIGILL: case SIGTRAP: case SIGSEGV:
			echo 'Aiiiieeeeeee aborting because SEGFAULT/TRAP/ILL !'.LF;
			exit(4);
		case SIGINT: case SIGTERM:
			// normal shutdown
			triggers_call('shutdown');
			exit(0);
		case SIGHUP:
			signal_reload();
			break;
		case SIGUSR1:
			triggers_call('sigusr1');
			break;
		case SIGUSR2:
			triggers_call('sigusr2');
			break;
		#
	}
}
