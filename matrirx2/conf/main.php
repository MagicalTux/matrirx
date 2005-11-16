<?php

/* MatrIRX, Modular IRC Services
 * main.php : Main configuration file for MatrIRX
 * $Id$
 *
 * Copyright (C) 2005 Mark Karpeles.
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

// do not modify this line :
$conf=&$GLOBALS['config'];

// comment out this line when editing the config
#die('Please edit the conf/main.php file !');

$conf['remote']['type']='Unreal'; // You must have addon irc_<type> in addons
$conf['remote']['host']='192.168.100.1'; // Remote IRC server to connect to
$conf['remote']['port']=7000; // Remote port
$conf['remote']['password']='link'; // link password

$conf['local']['name']='MatrIRX.test.irc.FF.st'; // Name of the MatrIRX server
$conf['local']['desc']='Powered by MatrIRX'; // Description
$conf['local']['numeric']=3; // Numeric ID of the server (Unreal)
$conf['local']['nickchars']='latin1'; // Supported nickname charsets (Unreal)

// Unreal Cloak Keys - Only used with Unreal Servers
// TODO: Code the new unreal cloak algorythm OR don't use it anymore
$conf['network']['cloak']=array(
	'abcdeFGH123456789',
	'HGFedcba987654321',
	've1757VE7TRHhRT7rt75h',
); // cloak keys
$conf['network']['ircnetwork']='FF.ST NetworK'; // Network name string
$conf['network']['hiddenhost-prefix']='FFST'; // Network hidden host prefix

$conf['network']['controlchan']='#Beta'; // control channel

$conf['core']['log_level']=LOGGER_ALL | LOGGER_DEBUG; // Log everything

// setuid/setgid settings
// Only use numeric IDs as the server can't access /etc/passwd or /etc/group
$conf['addons']['defaultuser']=1000;
$conf['addons']['defaultgroup']=1000;

// Do *not* remove this line, it tells the configuration handler that the config
// could be loaded
return true;
