<?

// Module for arrayfile management

function load_arrayfile($fil) {
	if (!@file_exists($fil)) return array();
	$fh=@fopen($fil,'r');
	if (!$fh) return array();
	$data=fread($fh,filesize($fil));
	fclose($fh);
	$data=unserialize($data);
	if (!is_array($data)) $data=array();
	return $data;
}

function save_arrayfile($fil,$data) {
	$fh=@fopen($fil.'~','w');
	if (!$fh) return false;
	fwrite($fh,serialize($data));
	fclose($fh);
	rename($fil.'~', $fil);
	return true;
}
