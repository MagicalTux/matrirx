<?php
// NyuServ v1.0
// Made by MagicalTux <w@ff.st>

// ADDON POUR MATRIRX, INUTILISABLE SANS MATRIRX !
// Download MatrIRX at.... nowhere yet :(

// USED TO TEST OLD BOTS WITH MatrIRX II

define('PRIV_NYUSERV_NICK','NyuServ');

/************************************** INIT ****************************************************/

priv_nyuserv_init();
function priv_nyuserv_init() {
    mylog('Loading nyuserv...');
    $GLOBALS['nyuserv']=load_arrayfile(_DATA.'nyuserv.dat');
}

/************************************** CONNECT ****************************************************/

function mod_nyuserv_connect() {
    // end of sync : introduce user
    introduce_user(PRIV_NYUSERV_NICK,'nyu','Nyu-nyu-nyu.irc.FF.st','*',IRC_SVC_MODES,'Nyu ?');
    // if sync is done, call EOS() now
    if ($GLOBALS['sync']) mod_nyuserv_eos();
}

/************************************** EOS ****************************************************/

function mod_nyuserv_eos() {
    irc_join(PRIV_NYUSERV_NICK,'#help,#desir,#MT,#MatrIRX');
}

/************************************** PART ****************************************************/

function mod_nyuserv_part($chan) {
    // we were kicked oO ??? or did we leave ?
    irc_join(PRIV_NYUSERV_NICK,$chan);
}

/************************************** PUBMSG ****************************************************/

function priv_nyuserv_check_nlist($chan) {
    if (!isset($GLOBALS['nyuserv'][$chan])) $GLOBALS['nyuserv'][$chan]=array();
    $nlist=&$GLOBALS['nyuserv'][$chan];
    if (count($nlist)<550) return;
    // recompute nlist
    $lst=array();
    foreach($nlist as $id=>$phr) {
        if (rand(1,50)>25) $lst[]=$phr;
    }
    $GLOBALS['nyuserv'][$chan]=$lst;
    save_arrayfile(_DATA.'nyuserv.dat',$GLOBALS['nyuserv']);
}

function mod_nyuserv_pubmsg($dat) {
    if (strtolower($dat['src'])==strtolower(PRIV_NYUSERV_NICK)) return;
    $p=$dat['pars'];
    $chan=strtolower(array_shift($p));
    $txt=implode(' ',$p);
    priv_nyuserv_check_nlist($chan);
    
    $ltxt=strtolower($txt);
    if (strpos($ltxt,'nyu')!==false) {
        $nlist=&$GLOBALS['nyuserv'][$chan];
        $nlist[]=$txt;
        priv_nyuserv_check_nlist($nlist);
        $c=rand(1,count($nlist))-1;
        $nyu=$nlist[$c];
        irc_privmsg(PRIV_NYUSERV_NICK,$chan,$nyu);
    }
}

/************************************** QUIT, OTHER ****************************************************/

function mod_nyuserv_quit($dat) {
    // we were killed or quitted ??? oO
    if (defined('PRIV_NYUSERV_SHUTDOWN')) return;
    mod_nyuserv_connect();
}

function mod_nyuserv_shutdown() {
    // need to do something before death?
    define('PRIV_NYUSERV_SHUTDOWN',true);
    save_arrayfile(_DATA.'nyuserv.dat',$GLOBALS['nyuserv']);
    irc_quit(PRIV_NYUSERV_NICK,'Nyuuuuuuuuuuuuuu~');
}
