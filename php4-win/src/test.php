<?php
define('LF',"\n");

echo 'Checking functions...'.LF;
if (!function_exists('chroot')) die('ERROR: chroot() missing !'.LF);
if (!function_exists('pcntl_fork')) die('ERROR: pcntl_fork() missing !'.LF);

echo 'Testing chroot() ...'.LF;
chroot('.');
$dir=opendir('/');
if (!$dir) die('Opendir failed!'.LF);
echo 'Content of / :'.LF;
while($fil=readdir($dir)) echo $fil.LF;
closedir($dir);

echo LF.'Testing childs :'.LF;
// test fork()
// generates some of processes
$parent=true;
$child=array();
for($i=1;$i<=5;$i++) {
	$res=pcntl_fork();
	if ($res==0) {
		$parent=false;
		break;
	} elseif ($res>0) {
		echo 'Child '.$res.' created !'.LF;
		$child[$i]=$res;
	} else {
		echo 'Fork failed!'.LF;
	}
}

if (!$parent) {
	function child_handle($sig) {
		global $i;
		switch($sig) {
			case SIGUSR1:
				echo '['.getmypid().'] HELLO THERE I\'M CHILD '.$i.' !'.LF;
				break;
			case SIGTERM:
				echo '['.getmypid().'] CHILD '.$i.' DYING !'.LF;
				exit($i);
				break;
			#
		}
	}
	declare(ticks = 1);
	pcntl_signal(SIGUSR1,'child_handle');
	pcntl_signal(SIGTERM,'child_handle');
	while(1) {
		sleep(1);
	}
} else {
	foreach($child as $id=>$pid) {
		echo 'USR1 to child#'.$id.' : ';
		posix_kill($pid,SIGUSR1);
		sleep(1);
		echo 'TERM to child#'.$id.' : ';
		posix_kill($pid,SIGTERM);
		sleep(1);
		echo LF;
	}
}

exit();
