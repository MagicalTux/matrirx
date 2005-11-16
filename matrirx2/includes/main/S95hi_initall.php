<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S95hi_initall.php : Init all base-values
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

initall_init();

function initall_init() {
	// initall !
	$conf=&$GLOBALS['config'];
	$type=$conf['remote']['type'];
	$module='irc_'.strtolower($type); // irc_unreal for example
	$result=true;
	if (!addons_load($module)) {
		echo 'Warning : could not start addon <<'.$module.'>> !'.LF;
		echo 'Please check conf/main.php and type :'.LF;
		echo 'kill -HUP '.posix_getpid().LF;
		$result=false;
	}
		if (!addons_load('core')) {
		echo 'Warning : could not load core addon !'.LF;
		echo 'Please check your addons directory and type :'.LF;
		echo 'kill -HUP '.posix_getpid().LF;
		$result=false;
	}
	return $result;
}
