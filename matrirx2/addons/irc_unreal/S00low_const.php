<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2004 Robert Karpeles.
 * S00low_defines.php : Various defines used for Unreal
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

/*
Protocol       Version
------------------------------------------------------------------------------------------------
2306           3.2.3
2305           3.2.2
2304           3.2.1
2303           3.2-beta*, 3.2-RC*, 3.2
2302           3.1.1-Darkshades, 3.1.2-Darkshades, 3.1.3-Komara, 3.1.4-Meadows
2301           3.1-Silverheart
2300           3.0-Morrigana
*/

define('UNREAL_PROTOCOL',2306);

/*
Flag           Description
------------------------------------------------------------------------------------------------
c              Server is chrooted
C              command line config enabled
D              Server is in debugmode
F              Using file descriptor lists
h              Compiled as a hub
i              Shows invisible users in /trace
n              NOSPOOF enabled
V              Uses valloc()
W              Windows version
Y              Syslog logging enabled
K              No ident checking (?)
6              IPv6 supported
X              STRIPBADWORDS enabled (chmode/umode +G)
P              Uses poll()
e              SSL supported
O              OperOverride enabled
o              OperOverride without verify
Z              Zip links supported
3              3rd party modules (were) loaded or unreal is any other way 'tainted' (eg: bad libs)
E              Extended channel modes supported

My test server sent : Fhi6XeOoZE

*/

define('UNREAL_FLAGS','cFi6XOoE');

// HOSTLEN is used in S10low_cloak.php
define('HOSTLEN',63);
