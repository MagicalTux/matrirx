<?php

/* MatrIRX, Modular IRC Services
 * S01low_config.php : Configuration loading & rehashing
 * $Id$
 *
 * Copyright (C) 2004 Robert Karpeles.
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

load_config();

function load_config() {
	// Make a backup of the config
	if (isset($GLOBALS['config'])) {
		$bconf=$GLOBALS['config'];
	} else {
		$bconf=false;
	}
	$res=include('/conf/main.php');
	if (!$res) {
		Logger::Log('Could not load configuration file!',LOGGER_WARNING);
		if (!$bconf) {
			Logger::Log('No configuration available. Can\'t continue!!',LOGGER_EMERG);
			exit(11);
		}
		$GLOBALS['config']=$bconf;
	}
	return (bool)$res;
}
