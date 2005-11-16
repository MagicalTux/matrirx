<?php

/* MatrIRX, Modular IRC Services
 * S50low_link.php : Link management for PHPBot
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


  if (defined("CORE_CONNECT")) {
    fatal("ERROR: Core module CONNECT already loaded");
  }
  define("CORE_CONNECT","Core_Connect v0.3");
  $core_ext[]="CORE_CONNECT";
  function connect($curirc,$port) {
    global $cnx,$botnick,$botnicko,$joined,$j,$triggers,$botmodes;
    $j=array();
    $port+=0;
    $joined=array();
    if ($cnx) {
	  if (!feof($cnx)) return true;
	  @fclose($cnx);
	}
    echo "Connecting to $curirc port $port ...";
    putlog("CORE_CONNECT: Trying to connect to $curirc on port $port ...");
    $cnx=@fsockopen($curirc,$port,$errno,$errstr,30);
    if (!$cnx) {
      putlog("CORE_CONNECT ERROR: Connecion error # $errno : $errstr");
      echo "ERROR\r\nError $errno : $errstr\r\n\r\n";
      sleep(4);
      return false;
    }
    // connected ...
    putlog("CORE_CONNECT: Identifying to IRC Server ...");
    $botnick=$botnicko;
    fputs($cnx,"USER username b c Realname e\r\n");
    fputs($cnx,"NICK $botnick\r\n");
    // :Canada2.EnterTheGame.Com NOTICE AUTH :*** Looking up your hostname...
    // :Canada2.EnterTheGame.Com 433 * Bourreau :Nickname is already in use.
    // :Canada2.EnterTheGame.Com 376 Bourreau|TELNET :End of /MOTD command.
    $ok=false;
    while (!$ok) {
      $recv=readline();
      if (!is_array($recv)) { if (!$recv) return false; } else {
        if ($recv["code"] == 433) {
          // nickname already in use ...
          $botnick=$botnicko.time();
          putlog("CORE_CONNECT WARN: Nickname already in use. Trying $botnick ...");
          sleep(2);
          fputs($cnx,"NICK $botnick\r\n");
        }
        if ($recv["code"] == 001) $ok=true;
      }
    }
    waitfor(376); // /MOTD ...
    putlog("CORE_CONNECT: Connexion ETABLISHED, MOTD skipped");
    echo "ETABLISHED\r\n";
    fputs($cnx,"MODE ".$botnick." ".$botmodes."\r\n");
    $ttrig=$triggers;
    reset($ttrig);
    while (list($trig,$func) = each($ttrig)) {
      if (substr($trig,0,2)=="C:") $func();
    }
    return true;
  }
  putlog("CORE_CONNECT: Loaded ".CORE_CONNECT);
  echo "CORE: Loaded ".CORE_CONNECT." ...\r\n";
