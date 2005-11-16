<?php

/* MatrIRX, Modular IRC Services
 * low_const.php : Some constants, common values in IRC, etc...
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

// standard defines for CR, LF, and CRLF
define('LF',"\n");
define('CR',"\r");
define('CRLF',CR.LF);

// Common IRC values
define('_CTCP',     chr(0x01));
define('_BOLD',     chr(0x02));
define('_COLOR',    chr(0x03));
define('_RESET',    chr(0x0F));
define('_UNDERLINE',chr(0x1F));

// Defaults UMODES
// TODO: The +o should be removed, however it may cause problems with
// TODO: other services (they will think we're abusing a desynch)
define('IRC_SVC_MODES','+pqioS');
