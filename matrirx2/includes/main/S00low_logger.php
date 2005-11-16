<?php

/* MatrIRX, Modular IRC Services
 * Copyright (C) 2005 Mark Karpeles.
 * S00low_logger.php : Logging class for MatrIRX
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


define('LOGGER_EMERG',   0x0001); // System unusable
define('LOGGER_ALERT',   0x0002);
define('LOGGER_CRIT',    0x0004);
define('LOGGER_ERR',     0x0008);
define('LOGGER_WARNING', 0x0010);
define('LOGGER_NOTICE',  0x0020);
define('LOGGER_INFO',    0x0040);
define('LOGGER_DEBUG',   0x0080);

define('LOGGER_ALL',     0x007f); // ALL *but* DEBUG1

define('LOGGER_DEFAULT',LOGGER_WARNING); // Default level for messages

// Static class logger
//  * Logger::Log(message,level)
class Logger {
	function GetFilename() {
		// Generate filename for logs
		return '/logs/'.date('Y-m-d').'.txt';
	}
	
	function MessageLevel($level) {
		// Get level string and return it
		$levels=array(
			'EMERG','ALERT','CRIT','ERR','WARNING','NOTICE','INFO','DEBUG'
		);
		$res='';
		foreach($levels as $lvl) {
			$val=constant('LOGGER_'.$lvl);
			if ( ($level & $val) != 0) $res.=($res==''?'':'|').$lvl;
		}
		return $res;
	}
	
	function Log($str,$level=LOGGER_DEFAULT,$src=null) {
		$str=str_replace(CR,'',str_replace(LF,'',$str)); // Strip any CR or LF
		if (defined('CHILD_NAME')) {
			// Send log line to parent
			threadpipe_write('SLOG '.$level.' '.$str.LF);
			return;
		}
		// check level
		if (isset($GLOBALS['config'])) if ( ( $level & $GLOBALS['config']['core']['log_level']) == 0 ) return;
		if (is_null($src)) $src='main';
		$line=date('D d H:i:s').' ['.Logger::MessageLevel($level).'] '.$src.' '.$str.LF;
		echo $line;
		$name=Logger::GetFilename();
		$fp=@fopen($name,'a');
		if (!$fp) {
			echo 'Warning: could not open logfile '.$name.'!'.LF;
			return;
		}
		fwrite($fp,$line);
		fclose($fp);
	}
}

