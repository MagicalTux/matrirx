<?php
define('LF',"\n");

echo 'Checking functions...'.LF;
if (!function_exists('chroot')) die('ERROR: chroot() missing !'.LF);
if (!function_exists('pcntl_fork')) die('ERROR: pcntl_fork() missing !'.LF);
if (!function_exists('stream_socket_pair')) die('ERROR: stream_socket_pair() missing !'.LF);

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
	$sock=stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, 0);
	$res=pcntl_fork();
	if ($res==0) {
		fclose($sock[1]);
		$sock=$sock[0];
		$parent=false;
		break;
	} elseif ($res>0) {
		fclose($sock[0]);
		echo 'Child '.$res.' created !'.LF;
		$child[$i]=array($res,$sock[1]);
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
		if (@stream_select($r=array($sock),$w=array(),$e=array(),null)>0) {
			$str=fgets($sock,4096);
			if($str) echo $str;
		}
	}
} else {
	foreach($child as $id=>$info) {
		$pid=$info[0];
		$sock=$info[1];
		fputs($sock,'PIPE stream message to child '.$pid.' from master!'.LF);
		sleep(1);
		echo 'USR1 to child#'.$id.' : ';
		posix_kill($pid,SIGUSR1);
		sleep(1);
		echo 'TERM to child#'.$id.' : ';
		posix_kill($pid,SIGTERM);
		fclose($sock);
		sleep(1);
		echo LF;
	}
}

exit();
