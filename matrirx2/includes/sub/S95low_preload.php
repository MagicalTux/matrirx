<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2004 Robert Karpeles.
 * S95low_preload.php : Preloading of the script's functions
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

preload_script();

function preload_script() {
	$dir='/data/'.CHILD_NAME.'/';
	if (!is_dir($dir)) mkdir($dir);
	chown($dir,ADDON_UID);
	chgrp($dir,ADDON_GID);
	if (is_dir('/addons/'.CHILD_NAME)) {
		$dh=opendir('/addons/'.CHILD_NAME);
		while($fil=readdir($dh)) {
			if (!fnmatch('S*.php',$fil)) continue;
			copy('/addons/'.CHILD_NAME.'/'.$fil,$dir.$fil);
			chown($dir.$fil,ADDON_UID);
			chgrp($dir.$fil,ADDON_GID);
		}
		closedir($dh);
	} elseif (is_file('/addons/'.CHILD_NAME)) {
		$dh=opendir('/addons/_old');
		while($fil=readdir($dh)) {
			if (!fnmatch('S*.php',$fil)) continue;
			copy('/addons/_old/'.$fil,$dir.$fil);
			chown($dir.$fil,ADDON_UID);
			chgrp($dir.$fil,ADDON_GID);
		}
		closedir($dh);
		copy('/addons/'.CHILD_NAME,$dir.'SAA_core.php');
		chown($dir.'SAA_core.php',ADDON_UID);
		chgrp($dir.'SAA_core.php',ADDON_GID);
	} else {
		echo 'Addon not found : '.CHILD_NAME.LF;
		exit(7);
	}
}
