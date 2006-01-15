<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S00low_makeuser.php : Creates core user
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

define('MATRIRX_NICK','MatrIRX');
define('MATRIRX_CHAN','#Beta');

$GLOBALS['link_stamp']='';

// Create trigger on IRC Connect
triggers_add('make_matrirx_user',$n=null,'connect');
triggers_add('make_matrirx_user',$n=null,'quit');
function make_matrirx_user($n,$data,$source) {
//	echo 'MAKEUSER called!'.$source.LF;
	if (isset($data['stamp'])) {
		if ($GLOBALS['link_stamp']==$data['stamp']) return;
		$GLOBALS['link_stamp']=$data['stamp'];
	}
	irc_do('introduce_user',$pars=array('nick'=>MATRIRX_NICK),$source);
	irc_do('irc_join',array('src'=>MATRIRX_NICK,'chan'=>MATRIRX_CHAN),$source);
	irc_do('irc_mode',array('src'=>MATRIRX_NICK,'target'=>MATRIRX_CHAN,'mode'=>'+oa '.MATRIRX_NICK.' '.MATRIRX_NICK),$source);
	irc_do('irc_privmsg',array('src'=>MATRIRX_NICK,'target'=>MATRIRX_CHAN,'message'=>'MatrIRX TEST'),'irc_unreal');
}

triggers_add('handle_privmsg',$n=null,'privmsg');
function handle_privmsg($n,$data,$source) {
	var_dump($data);
//	irc_do('irc_privmsg',array('src'=>'Graal','target'=>$target,'message'=>$data['src'].' said: '.$data['message']),$source);
}

// Just a test
unset($n);
timers_add('do_test',$n=0,300,-1); unset($n);
function do_test(&$n) {
	$n++;
	irc_do('irc_privmsg',array('src'=>MATRIRX_NICK,'target'=>MATRIRX_CHAN,'message'=>'Test #'.$n.' - '.date('Y-m-d H:i:s').' - '.$GLOBALS['link_stamp']),'irc_unreal');
	return true;
}
