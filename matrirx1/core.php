<?
/* Core file for BETA SERVICES
 *
 */
error_reporting(E_ALL);
set_time_limit(0);

define('MATRIRX_VERSION','0.1.119');

// open includes

$d=getcwd();
$d=str_replace('\\','/',$d);
if (substr($d,-1)!='/') $d.='/';
define('_ROOT',$d);
unset($d);

if (!is_dir(_ROOT.'data')) mkdir(_ROOT.'data');
define('_DATA',_ROOT.'data/');

include('config.php');

$dir=opendir('includes');
while($fil=readdir($dir)) {
	if (substr($fil,-4)==".php") require('includes/'.$fil);
}

$dir=opendir('addons');
while($fil=readdir($dir)) {
	if (substr($fil,-4)==".php") require('addons/'.$fil);
}

while(1) {
	main_loop();
}
