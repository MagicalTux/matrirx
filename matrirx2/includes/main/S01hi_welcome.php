<?php

/* MatrIRX, Modular IRC Services
 * S01hi_welcome.php : Welcome message
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

$welcome='Welcome to MatrIRX v'.MATRIRX_VERSION;
echo str_repeat('*',strlen($welcome)+4).LF;
echo '* '.$welcome.' *'.LF;
echo str_repeat('*',strlen($welcome)+4).LF;

Logger::Log('MatrIRX v'.MATRIRX_VERSION.' is currently loading...',LOGGER_INFO);
