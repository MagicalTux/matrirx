<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S05low_link.php : Unreal Tokens
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

$GLOBALS['tokens']=array(
	'PRIVMSG'=>'!',
	'WHOIS'=>'#',
	'NICK'=>'&',
	'SERVER'=>'\'',
	'TOPIC'=>')',
	'INVITE'=>'*',
	'VERSION'=>'+',
	'QUIT'=>',',
	'SQUIT'=>'-',
	'KILL'=>'.',
	'INFO'=>'/',
	'LINKS'=>'0',
	'STATS'=>'2',
	'HELP'=>'4',
	'ERROR'=>'5',
	'AWAY'=>'6',
	'CONNECT'=>'7',
	'PING'=>'8',
	'PONG'=>'9',
	'PASS'=>'<',
	'TIME'=>'>',
	'ADMIN'=>'@',
	'SETHOST'=>'AA',
	'NACHAT'=>'AC',
	'SETIDENT'=>'AD',
	'SETNAME'=>'AE',
	'LAG'=>'AF',
	'SDESC'=>'AG',
	'KNOCK'=>'AI',
	'CREDITS'=>'AJ',
	'LICENSE'=>'AK',
	'CHGHOST'=>'AL',
	'RPING'=>'AM',
	'RPONG'=>'AN',
	'NETINFO'=>'AO',
	'SENDUMODE'=>'AP',
	'ADDMOTD'=>'AQ',
	'ADDOMOTD'=>'AR',
	'SVSMOTD'=>'AS',
	'SMO'=>'AU',
	'OPERMOTD'=>'AV',
	'TSCTL'=>'AW',
	'SAJOIN'=>'AX',
	'SAPART'=>'AY',
	'CHGIDENT'=>'AZ',
	'NOTICE'=>'B',
	'SWHOIS'=>'BA', // change whois extra line - SWHOIS user :line
	'SVSO'=>'BB',
	'SVSFLINE'=>'BC',
	'TKL'=>'BD',
	'VHOST'=>'BE',
	'BOTMOTD'=>'BF',
	'HTM'=>'BH', // High Traffic Mode
	'DCCDENY'=>'BI',
	'UNDCCDENY'=>'BJ',
	'CHGNAME'=>'BK',
	'SHUN'=>'BL', // shun lines
	'CYCLE'=>'BP',
	'MODULE'=>'BQ',
	'SVSNLINE'=>'BR',
	'SVSPART'=>'BT',
	'SVSLUSERS'=>'BU',
	'SVSSNO'=>'BV',
	'SVS2SNO'=>'BW',
	'SVSJOIN'=>'BX',
	'SVSSILENCE'=>'Bs',
	'SVSWATCH'=>'Bw',
	'JOIN'=>'C',
	'PART'=>'D',
	'LUSERS'=>'E',
	'EOS'=>'ES', // end of sync
	'MOTD'=>'F',
	'MODE'=>'G',
	'KICK'=>'H',
	'REHASH'=>'O',
	'RESTART'=>'P',
	'CLOSE'=>'Q',
	'SENDSNO'=>'Ss',
	'DNS'=>'T',
	'TEMPSHUN'=>'Tz',
	'SILENCE'=>'U',
	'AKILL'=>'V',
	'UNKLINE'=>'X',
	'RAKILL'=>'Y',
	'GLOBOPS'=>']',
	'LOCOPS'=>'^',
	'PROTOCTL'=>'_',
	'WATCH'=>'`',
	'TRACE'=>'b',
	'SQLINE'=>'c',
	'UNSQLINE'=>'d',
	'SVSNICK'=>'e',
	'SVSNOOP'=>'f',
	'SVSKILL'=>'h',
	'SVSMODE'=>'n',
	'SAMODE'=>'o',
	'CHATOPS'=>'p',
	'UNZLINE'=>'r',
	'RULES'=>'t',
	'MAP'=>'u',
	'SVS2MODE'=>'v',
	'DALINFO'=>'w',
	'ADMINCHAT'=>'x',
	'UMODE2'=>'|', // user mode (only :USER UMODE2 modes)
	'SJOIN'=>'~', // server join info
// Those two are not anymore in Unreal's doc
//	'BOTSERV'=>'BS', 
//	'INFOSERV'=>'BO', // infoserv (??)
);

update_revtokens();

function update_revtokens() {
	$GLOBALS['revtokens']=array();
	foreach($GLOBALS['tokens'] as $cmd=>$tok) {
		$GLOBALS['revtokens'][$tok]=$cmd;
	}
}

function resolve_token($tok) {
	if (isset($GLOBALS['revtokens'][$tok])) return $GLOBALS['revtokens'][$tok];
	return $tok;
}

function make_token($cmd) {
	$cmd=strtoupper($cmd);
	if (!isset($GLOBALS['remote_cap']['TOKEN'])) {
		// token not accepted by remote
		return $cmd;
	}
	if (isset($GLOBALS['tokens'][$cmd])) return $GLOBALS['tokens'][$cmd];
	return $cmd;
}
