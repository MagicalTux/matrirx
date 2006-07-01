<?
// Stats System
// $Id$
//
// This is a light version of Stats, released to the public with great hopes to see
// newer versions of this file. I added some comments to help the devs, and feel free
// to come and ask me more infos.
//
// The generated logfiles are in eggdrop format. It's up to you to create a script which
// will scan the logs directory, and generate a pisg (or whatever you want to use) config
// file to generate HTML stats files.
//
//   MagicalTux - #MT @ irc.ooKoo.org


/************************************** INIT ****************************************************/

define('STATS_VERSION','1.0.44');

priv_stats_init();
function priv_stats_init() {
	mylog('Initializing Stats version '.STATS_VERSION.' ...');
	$GLOBALS['mod_stats']=array();
	$G=&$GLOBALS['mod_stats'];
	$G['announce_timer']=time()+86400;
	$G['max_timer']=array();
	$G['chantest_timer']=time()+300;
	// load max chan data
	$G['maxchans']=load_arrayfile(_DATA.'stats_maxchan.dat');
	$G['chanslang']=load_arrayfile(_DATA.'stats_chanslang.dat');
	$G['exceptchan']=load_arrayfile(_DATA.'stats_exceptchan.dat');
	$G['save_timer']=time()+60; // force next save to happen in 1 minute
}

/************************************ PRIVMSG (CTCP) ****************************************/

function mod_stats_privmsg($dat) {
	$conf=$GLOBALS['config']['stats'];
	$user=strtolower($dat['src']);
	$text=$dat['pars'][1];
	if ( ($text{0}==_CTCP) and (substr($text,-1)==_CTCP)) {
		// CTCP reзu... :) 
		$text=substr($text,1,strlen($text)-2); // strip CTCP tags
		$pos=strpos($text,' ');
		if (!$pos) $pos=strlen($text);
		$cmd=strtoupper(substr($text,0,$pos));
		$reply=false;
		switch($cmd) {
			case 'VERSION':
				$reply='Stats v'.STATS_VERSION.' by MagicalTux <'._UNDERLINE.'MagicalTux@gmail.com'._UNDERLINE.'>';
				$reply.=' PHP/'.phpversion();
				$reply.=' MatrIRX/'.MATRIRX_VERSION;
				break;
			case 'TIME':
				$reply=date('Y-m-d H:i:s');
				break;
			case 'PING':
				$reply=substr($text,$pos+1);
				break;
			case 'FINGER':
				$reply=$conf['nick'].' (MagicalTux@gmail.com) Idle ';
				$idle=time();
				$dat=array(
					'year'=>86400*365,
					'month'=>86400*30,
					'day'=>86400,
					'hour'=>3600,
					'minute'=>60,
					'second'=>1,
				);
				$res='';
				foreach($dat as $unit=>$val) {
					if ($idle<$val) continue;
					$tmp=floor($idle/$val);
					$idle-=$val*$tmp;
					$res.=($res==''?'':' ').$tmp.' '.$unit.($tmp==1?'':'s');
				}
				$reply.=$res;
				break;
			default:
				mylog('Got unknown CTCP from '.$user.' : '.$text);
				break;
			# 
		}
		if (!$reply) return;
		irc_notice($conf['nick'],$user,_CTCP.$cmd.' '.$reply._CTCP);
		return;
	}
}

/************************************** NOOP ****************************************************/

function mod_stats_noop() {
	priv_stats_check(); // run it regulary
}

/************************************** PRIV_CHECK ****************************************************/

function priv_stats_save() {
	mylog('Stats saving databases...');
	$G=&$GLOBALS['mod_stats'];
	save_arrayfile(_DATA.'stats_maxchan.dat',$G['maxchans']);
	save_arrayfile(_DATA.'stats_chanslang.dat',$G['chanslang']);
	save_arrayfile(_DATA.'stats_exceptchan.dat',$G['exceptchan']);
}

function priv_stats_check() {
	$G=&$GLOBALS['mod_stats'];
	$conf=$GLOBALS['config']['stats'];
	
	if ($G['save_timer'] < time()) {
		$G['save_timer']=time()+7200;
		priv_stats_save();
	}
	
	if ($G['chantest_timer'] < time()) {
		$G['chantest_timer']=time()+3600;
		$leave=array();
		foreach($GLOBALS['chans'] as $chan=>$dat) {
			if ($GLOBALS['chans'][$chan]['usercount']<=1) $leave[]=$chan;
		}
		foreach($leave as $chan) {
			// exit from this chan =p
			priv_stats_log($chan,' '.$conf['nick'].' ('.$conf['ident'].'@'.$conf['host'].') left '.irc_get_case($chan).'.');
			priv_stats_endlog($chan);
			irc_part($conf['nick'],$chan);
		}
	}
	
	if ($G['announce_timer'] < time()) {
		mylog('Announcing stats...');
		$G['announce_timer']=time()+86000;
		irc_privmsg('Stats','#Beta','Announcing stats.');
		foreach($GLOBALS['chans'] as $chan=>$dat) {
			$ch=substr($chan,1);
			irc_privmsg($conf['nick'],$chan,priv_chan_msg($chan,1));
		}
	}
}

// Generate a message and send it to a channel (detects channel's language)
function priv_chan_msg($chan,$type) {
	global $stats_locale;
	$msg=&$stats_locale;
	// '.urlencode($ch).'
	$G=&$GLOBALS['mod_stats'];
	if (!isset($msg[$type])) return '';
	$chan=strtolower($chan);
	if (!isset($G['chanslang'][$chan])) $G['chanslang'][$chan]='en';
	$lang=$G['chanslang'][$chan];
	if (!isset($msg[$type][$lang])) $lang='en';
	$G['chanslang'][$chan]=$lang;
	// %max - %url
	$repl=array();
	$ch=substr($chan,1);
	$ch=mk_name($ch);
	$ch=urlencode($ch);
	$repl['%url']=$ch;
	$max=intval($G['maxchans'][$chan]);
	$repl['%max']=$max;
	$res=$msg[$type][$lang];
	foreach($repl as $var=>$val) $res=str_replace($var,$val,$res);
	return $res;
}

/************************************** CONNECT ****************************************************/

function mod_stats_connect() {
	$conf=$GLOBALS['config']['stats'];
	// end of sync : introduce user 'STATS'
	introduce_user($conf['nick'],$conf['ident'],$conf['host'],'*',IRC_SVC_MODES,$conf['real']);
	// scan chans and join them
	mod_stats_eos();
}

/************************************** EOS ****************************************************/

function mod_stats_eos() {
	$conf=$GLOBALS['config']['stats'];
	$G=&$GLOBALS['mod_stats'];
	foreach($GLOBALS['chans'] as $chan=>$dat) {
		if (isset($dat['users'][strtolower($conf['nick'])])) continue; // skip
		if (isset($G['exceptchan'][strtolower($chan)])) continue;
		irc_join($conf['nick'],$chan);
	}
}

/************************************** PART ****************************************************/

function mod_stats_part($chan) {
	priv_stats_check();
	$conf=$GLOBALS['config']['stats'];
	$G=&$GLOBALS['mod_stats'];
	// we were kicked oO ??? or did we leave ?
	$chan=strtolower($chan);
	if (!isset($GLOBALS['chans'][$chan])) return;
	if (isset($G['exceptchan'][$chan])) return; // do not come back
	// count users in chan
	if ($GLOBALS['chans'][$chan]['usercount']<=1) return;
	irc_join($conf['nick'],$chan);
}

/************************************** MODE ****************************************************/

function mod_stats_usermode($dat) {
	priv_stats_check();
	$conf=$GLOBALS['config'];
	$u = &$GLOBALS['users'][strtolower($dat['user'])];
	$dat['user']=irc_get_case($dat['user']);
	if (!$dat['user']) return; // $dat['user']=$conf['local']['name']; <-- ignore it
	$chan=strtolower($dat['chan']);
	// [01:17] #dragons: mode change '+o CuDDleS1' by ChanServ!service@dal.net
	priv_stats_log($chan,$chan.': mode change \''.$dat['modes'].'\' by '.$dat['user'].'!'.$u['ident'].'@'.$u['host']);
}

/************************************** USERPART ****************************************************/

function mod_stats_userpart($dat) {
	priv_stats_check();
	$conf=$GLOBALS['config']['stats'];
	if (strtolower($dat['user']) == strtolower($conf['nick'])) return;
	$chan=$dat['chan'];
	$chan=strtolower($chan);
	if (!isset($GLOBALS['chans'][$chan])) return;
	$u = &$GLOBALS['users'][strtolower($dat['user'])];
	if (!$u) return;
	priv_stats_log($chan,' '.irc_get_case($dat['user']).' ('.$u['ident'].'@'.$u['host'].') left '.irc_get_case($dat['chan']));
	if ($GLOBALS['chans'][$chan]['usercount']<=1) {
		// exit from this chan =p
		irc_part($conf['nick'],$chan);
		priv_stats_log($chan,' '.$conf['nick'].' ('.$conf['ident'].'@'.$conf['host'].') left '.irc_get_case($dat['chan']).'.');
		priv_stats_endlog($chan);
	}
}

/************************************** USERKICK ****************************************************/

function mod_stats_userkick($dat) {
	priv_stats_check();
	$conf=$GLOBALS['config']['stats'];
	$chan=$dat['chan'];
	$chan=strtolower($chan);
	if (!isset($GLOBALS['chans'][$chan])) return;
	priv_stats_log($chan,irc_get_case($dat['user']).' kicked from '.irc_get_case($chan).' by '.irc_get_case($dat['from']).': '.$dat['reason']);
	if ($GLOBALS['chans'][$chan]['usercount']<=1) {
		// exit from this chan =p
		irc_part($conf['nick'],$chan);
		priv_stats_log($chan,' '.$conf['nick'].' ('.$conf['ident'].'@'.$conf['host'].') left '.irc_get_case($dat['chan']));
		priv_stats_endlog($chan);
	}
}

/************************************** TOPIC ****************************************************/

function mod_stats_chantopic($dat) {
	priv_stats_check();
	// topic changed (?)
	$chan=$dat['chan'];
	$t=$dat['topic'];
	// [00:01] Topic changed on #Dragons by evilrain!rainS@dialpool1027.millenianet.com: call death at 808-561-384...
	priv_stats_log($chan, 'Topic changed on '.irc_get_case($chan).' by '.$t['set_by'].'!*@*: '.$t['value']);
//	priv_stats_log($chan,'* Topic is \''.$t['value'].'\'');
//	priv_stats_log($chan,'* Set by '.$t['set_by'].' on '.date('D M d H:i:s',$t['when']));
}

/************************************** NICKCHANGE **********************************************/

function mod_stats_usernickchange($dat) {
	priv_stats_check();
	$n=$dat['nick']; // nickcase can't be obtained from irc_get_case so preserve it
	$new=strtolower($dat['new']);
	if (!isset($GLOBALS['users'][$new])) return false;
	$msg='Nick change: '.$n.' -> '.irc_get_case($new);
	foreach($GLOBALS['users'][$new]['chans'] as $chan=>$joined) {
		priv_stats_log($chan,$msg);
	}
}

/************************************** QUIT ****************************************************/

function mod_stats_userquit($dat) {
	priv_stats_check();
	$p=$dat['pars'];
	if ($dat['command']=='QUIT') {
		$msg=$p[0];
		$user=$dat['src'];
	} else {
		$user=$p[0];
		$msg=$p[1];
	}
	$user=strtolower($user);
	$u=&$GLOBALS['users'][$user];
	if (!$u) return;
	if (!isset($GLOBALS['users'][$user])) return false;
	$msg=irc_get_case($user).' ('.$u['ident'].'@'.$u['host'].') left irc: '.$msg;
	foreach($GLOBALS['users'][$user]['chans'] as $chan=>$joined) {
		priv_stats_log($chan,$msg);
		if ($GLOBALS['chans'][$chan]['usercount']<=1) {
			// exit from this chan =p
			irc_part($conf['nick'],$chan);
			priv_stats_log($chan,' '.$conf['nick'].' ('.$conf['ident'].'@'.$conf['host'].') left '.irc_get_case($chan).'.');
			priv_stats_endlog($chan);
		}
	}
}

/************************************** LOGSPECIAL ****************************************************/

function priv_stats_endlog($chan) {
	return; // void
}

function priv_stats_getlogfp($chan) {
	$chan=strtolower($chan);
	if (!isset($GLOBALS['chans'][$chan])) return false;
	$chandata=&$GLOBALS['chans'][$chan];
	$conf=$GLOBALS['config'];
	// check directory
	if (substr($chan,0,1)=='#') $chan=substr($chan,1);
	$dir=mk_path($conf['stats']['directory'],$chan);
	if (substr($dir,-1)!='/') $dir.='/';
	if (!is_dir($dir)) mkdir($dir);
	if (!is_dir($dir)) return false;
	$dir.=date('Y-m-d').'.log';
	$fp=fopen($dir,'a');
	return $fp;
//	fputs($fp,CRLF.date('[H:i:s] ').'* Now talking in '.irc_get_case('#'.$chan).CRLF);
}

function priv_stats_log($chan,$txt) {
	$txt=date('[H:i] ').$txt;
	$fp=priv_stats_getlogfp($chan);
	if (!$fp) return false;
	fputs($fp,$txt.CRLF);
	fclose($fp);
}

/************************************** USERJOIN ****************************************************/

function mod_stats_userjoin($dat) {
	priv_stats_check();
	$G=&$GLOBALS['mod_stats'];
	$conf=$GLOBALS['config']['stats'];
	$chan=$dat['chan'];
	$chan=strtolower($chan);
	if (!isset($GLOBALS['chans'][$chan])) return;
	if (isset($G['exceptchan'][$chan])) return;
	if (!isset($G['maxchans'][$chan])) $G['maxchans'][$chan]=0;
	if ($GLOBALS['chans'][$chan]['usercount']>$G['maxchans'][$chan]) $G['maxchans'][$chan]=$GLOBALS['chans'][$chan]['usercount'];
	// log that
	$u=&$GLOBALS['users'][strtolower($dat['user'])];
	priv_stats_log($chan, irc_get_case($dat['user']).' ('.$u['ident'].'@'.$u['host'].') joined '.irc_get_case($chan).'.');
	if (isset($GLOBALS['chans'][$chan]['users'][strtolower($conf['nick'])])) return; // already joined
	irc_join($conf['nick'],$chan);
}

/************************************** PUBMSG ****************************************************/

function mod_stats_pubmsg($dat) {
	$G=&$GLOBALS['mod_stats'];
	$conf=$GLOBALS['config']['stats'];
	priv_stats_check();
	$p=$dat['pars'];
	$chan=strtolower(array_shift($p));
	if (isset($G['exceptchan'][$chan])) return;
	$txt=implode(' ',$p);
	$pars=explode(' ',ltrim($txt));
	if ($txt=='!chanstats') {
		if (!isset($G['max_timer'][$chan])) $G['max_timer'][$chan]=0;
		if ($G['max_timer'][$chan]<time()) {
			$G['max_timer'][$chan]=time()+300;
			$ch=substr($chan,1);
			irc_privmsg($conf['nick'],$chan,priv_chan_msg($chan,2));
		} else {
			irc_notice($conf['nick'],$dat['src'],priv_chan_msg($chan,'noflood'));
		}
	}
	if ($pars[0]=='!statslang') {
		$src=strtolower($dat['src']);
		// check if have +o, +a, +h or +q flag
		$flags='';
		if (isset($GLOBALS['chans'][$chan]['modes'][$src])) $flags=$GLOBALS['chans'][$chan]['modes'][$src];
		$modes=array('a','o','q','h');
		$ok=false;
		foreach($modes as $mode) {
			if (strpos($flags,$mode)!==false) $ok=true;
		}
		if ($ok) {
			$G['chanslang'][$chan]=$pars[1];
			irc_privmsg($conf['nick'],$chan,priv_chan_msg($chan,3));
		}
	} elseif ($pars[0]=='!denychan') {
		// Remove a chan from the list of chans where Stats is.
		// NB: DO NOT remove #beta, or you'll be blocked !!
		if ($chan!='#beta') return; // denied >.<
		$G['exceptchan'][strtolower($pars[1])]=true;
		irc_privmsg($conf['nick'], '#beta', 'Channel added to except list: '.$pars[1]);
	} elseif ($pars[0]=='!allowchan') {
		// Reverse operation, allow stats to join a chan
		if ($chan!='#beta') return; // denied >.<
		if (!isset($G['exceptchan'][strtolower($pars[1])])) return;
		irc_privmsg($conf['nick'], '#beta', 'Channel removed from except list: '.$pars[1]);
		unset($G['exceptchan'][strtolower($pars[1])]);
	}
	if (substr($txt,0,1)==_CTCP) {
		$txt=str_replace(_CTCP,'',$txt);
		$p=strpos($txt,' ');
		if (!$p) return;
		$act=strtolower(substr($txt,0,$p));
		$txt=substr($txt,$p+1);
		if ($act!='action') return;
		priv_stats_log($chan,'Action: '.irc_get_case($dat['src']).' '.$txt);
		return;
	}
	priv_stats_log($chan,'<'.irc_get_case($dat['src']).'> '.$txt);
}

/************************************** PUBNOTICE ****************************************************/

function mod_stats_pubnotice($dat) {
	priv_stats_check();
	$p=$dat['pars'];
	$chan=strtolower(array_shift($p));
	$txt=implode(' ',$p);
	priv_stats_log($chan,'-'.irc_get_case($dat['src']).':'.irc_get_case($chan).'- '.$txt);
}

/************************************** QUIT, OTHER ****************************************************/

function mod_stats_quit($dat) {
	// we were killed or quitted ??? oO
	if (defined('STATS_SHUTDOWN')) return; // do nothing if shutting down
	mod_stats_connect();
}

function mod_stats_shutdown() {
	$conf=$GLOBALS['config']['stats'];
	priv_stats_save();
	define('STATS_SHUTDOWN',true);
	irc_quit($conf['nick'],'MatrIRX is shutting down');
}

$url = $GLOBALS['config']['stats']['stats_url'];
$stats_locale=array(
	1=>array(
		'en'=>'Statistics for this chan are available at '.str_replace('%lang', 'en', $url).' - Type !chanstats to see the maximum of users on this chan.',
		'fr'=>'Les statistiques de ce chan sont disponibles а l\'adresse '.str_replace('%lang', 'fr', $url).' - Tapez !chanstats pour voir le maximum d\'utilisateurs sur ce chan',
		'miam'=>'MIAM MIAM MIAAAM O_O '.str_replace('%lang', 'fr', $url).' - MIAM O___O !chanstats MIAM MIAM.',
		'nyu'=>'Nyuuuu~ '.str_replace('%lang', 'fr', $url).' - Nyu !chanstats nyu nyu !',
		'1337'=>'WЈTcH 0UR L337 5TЈTZ Ј7 '.str_replace('%lang', 'en', $url).' - JO0 TYP3 !chanstats 70 S33 TH3 GR347 USERZ ЈM0UNT',
		'ru'=>'Статистика этого канала доступна по адресу '.str_replace('%lang', 'en', $url).' - Введите !chanstats, чтобы узнать рекорд посещения на этом канале.',
	),
	2=>array(
		'en'=>'The maximum user count on this chan is : %max users - Full stats at '.str_replace('%lang', 'en', $url),
		'fr'=>'Le maximum d\'utilisateurs sur ce chan est de %max utilisateurs - Statistiques complиtes а l\'adresse '.str_replace('%lang', 'fr', $url),
		'miam'=>'MIAM %max O___O MIAM - MIAM O_O '.str_replace('%lang', 'fr', $url),
		'nyu'=>'Nyuu nyu nyuuu %max nyu - Nyuuu '.str_replace('%lang', 'fr', $url),
		'1337'=>'0uR CHЈ|\| OWNZ U W17H %max US3RZ !! WЈ7CH 0UR 1337 S7Ј7Z ON '.str_replace('%lang', 'en', $url),
		'ru'=>'Максимальное количество пользователей на этом канале: %max - Полная статистика находится по адресу '.str_replace('%lang', 'en', $url),
	),
	3=>array(
		'en'=>'The language for this chan is now set to English',
		'fr'=>'La langue de ce salon est а prйsent le Franзais',
		'miam'=>'MIAMMMMM MIAM "MIAM" MIAM O____O',
		'nyu'=>'Nyuu~ Nyu Nyuuuuu',
		'1337'=>'1 5P34K 1337 N0W !!!111!1',
		'ru'=>'Вы выбрали Русский язык',
	),
	'noflood'=>array(
		'en'=>'You can call !chanstats only once every 5 minutes. Please wait a little longer.',
		'fr'=>'Vous ne pouvez appeler !chanstats que une fois toutes les 5 minutes. Veuillez patienter encore un peu...',
		'miam'=>'PROUT X__X',
		'nyu'=>'nyu nyu 5 min nyu.',
		'1337'=>'K33P 1337, D0 N07 F700D!',
		'ru'=>'Вы можете воспользоваться командой !chanstats не ранее, чем через 5 минут после последнего запроса.',
	),
);

// CONVERT everything to UTF8
// Remove this code to use either ISO-8859-15 or CP1251 (russian) by default
foreach($stats_locale as &$dat) {
	foreach($dat as $lng=>&$str) {
		$cs = 'ISO-8859-15';
		if ($lng=='ru') $cs='CP1251';
		$str = iconv($cs, 'UTF-8', $str);
	}
}

