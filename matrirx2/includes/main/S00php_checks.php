<?php

/* MatrIRX, Modular IRC Services
 * S00php_checks.php : Check PHP installation
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

php_check_all();

function php_check_all() {
	$version=explode('.',PHP_VERSION);
	if ($version[0]<5) die("This program requires at least PHP5.0.x! \n");
	if ($version[1]<1) echo("WARNING ! You are not using PHP 5.1 - Running in Socket Compatibility Mode !!\n");
	$func_list=array(
		'inet_ntop',
		'stream_socket_pair',
		'chroot',
		'socket_accept',
		'pcntl_fork',
	);
	foreach($func_list as $func) {
		if (!function_exists($func)) {
			echo 'PHP function '.$func.' is missing. Please make sure you have enabled the required modules!'."\n";
			exit(4);
		}
	}
	
}

