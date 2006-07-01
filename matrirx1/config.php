<?php

/* MatrIRX, Modular IRC Services
 * config.php : Configuration file for MatrIRX
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

// comment out this line when editing the config
die('Please edit the config file !');

$conf=array();
$conf['remote']['type']='Unreal'; // Only UnrealIRCd is supported right now
$conf['remote']['host']='127.0.0.1'; // Remote IRC server to connect to
$conf['remote']['port']=7000; // Remote port
$conf['remote']['password']=''; // link password

$conf['local']['name']='MatrIRX.My.server'; // Name of the MatrIRX server
$conf['local']['desc']='Powered by MatrIRX'; // Description
$conf['local']['numeric']=100; // Numeric ID of the server
$conf['local']['root']='Foo Foo Foo'; // (deprecated) Services root

// TODO: Code the new unreal cloak algorythm OR don't use it anymore
$conf['network']['cloak']='00000000'; // cloak key CRC
$conf['network']['ircnetwork']='FF.ST NetworK'; // Network name

// Config of module "stats"
$conf['stats']['nick']='Stats'; // Nick of the service
$conf['stats']['ident']='Service'; // ident (before the @)
$conf['stats']['host']=$conf['local']['name']; // Host
$conf['stats']['real']='Statistics Generator - http://stats.irc.ff.st/';
$conf['stats']['directory']=_ROOT.'logs/'; // Directory where to put logs
$conf['stats']['stats_url']='http://stats.irc.ooKoo.org/%lang/%url';

$GLOBALS['config']=$conf;

