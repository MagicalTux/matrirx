<?

$GLOBALS['tokens']=array(
	'PRIVMSG'=>'!',
	'WHO'=>'\\',
	'WHOIS'=>'#',
	'WHOWAS'=>'$',
	'USER'=>'%',
	'NICK'=>'&',
	'SERVER'=>'\'',
	'LIST'=>'(',
	'TOPIC'=>')',
	'INVITE'=>'*',
	'VERSION'=>'+',
	'QUIT'=>',',
	'SQUIT'=>'-',
	'KILL'=>'.',
	'INFO'=>'/',
	'LINKS'=>'0',
	'SUMMON'=>'1',
	'STATS'=>'2',
	'USERS'=>'3',
	'HELP'=>'4',
	'HELPOP'=>'4',
	'ERROR'=>'5',
	'AWAY'=>'6',
	'CONNECT'=>'7',
	'PING'=>'8',
	'PONG'=>'9',
	'OPER'=>';',
	'PASS'=>'<',
	'WALLOPS'=>'=',
	'TIME'=>'>',
	'NAMES'=>'?',
	'ADMIN'=>'@',
	'NOTICE'=>'B',
	'JOIN'=>'C',
	'PART'=>'D',
	'LUSERS'=>'E',
	'MOTD'=>'F',
	'MODE'=>'G',
	'KICK'=>'H',
	'USERHOST'=>'J',
	'ISON'=>'K',
	'REHASH'=>'O',
	'RESTART'=>'P',
	'CLOSE'=>'Q',
	'DIE'=>'R',
	'HASH'=>'S',
	'DNS'=>'T',
	'SILENCE'=>'U',
	'AKILL'=>'V',
	'KLINE'=>'W',
	'UNKLINE'=>'X',
	'RAKILL'=>'Y',
	'GNOTICE'=>'Z',
	'GOPER'=>'[',
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
	'ZLINE'=>'q',
	'UNZLINE'=>'r',
	'RULES'=>'t',
	'MAP'=>'u',
	'SVS2MODE'=>'v',
	'DALINFO'=>'w',
	'ADCHAT'=>'x',
	'MKPASSWD'=>'y',
	'ADDLINE'=>'z',
	'GLINE'=>'}',
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
	'ADDMODE'=>'AQ',
	'ADDOMODE'=>'AR',
	'SVSMODE_'=>'AS',
	'SMO'=>'AU',
	'OPERMOTD'=>'AV',
	'TSCTL'=>'AW',
	'SAJOIN'=>'AX',
	'SAPART'=>'AY',
	'CHGIDENT'=>'AZ',
	'SWHOIS'=>'BA', // change whois extra line - SWHOIS user :line
	'SVSO'=>'BB',
	'SVSFLINE'=>'BC',
	'TKL'=>'BD',
	'VHOST'=>'BE',
	'BOTMOTD'=>'BF',
	'HTM'=>'BH', // High Traffic Mode
	'SHUN'=>'BL', // shun lines
	'SVSJOIN'=>'BR',
	'SVSPART'=>'BT',
	'SJOIN'=>'~', // server join info
	'UMODE2'=>'|', // user mode (only :USER UMODE2 modes)
	'EOS'=>'ES', // end of sync
	'BOTSERV'=>'BS', // botserv (?)
	'INFOSERV'=>'BO', // infoserv (??)
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
