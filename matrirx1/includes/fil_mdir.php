<?
// multi levels directories

function priv_mdir_rmkd($dir) {
	// recursive mk dir
	if (substr($dir,-1)=='/') $dir=substr($dir,0,strlen($dir)-1);
	$pdir=dirname($dir);
	if (!is_dir($pdir)) priv_mdir_rmkd($pdir);
	if (!is_dir($dir)) mkdir($dir);
	return true;
}

function mk_name($name) {
	if (!is_string($name)) {
		var_dump($name);
		$name=strval($name);
	}
	$res='';
	for($i=0;$i<=2;$i++) {
		if (!isset($name{$i})) $name{$i}='.';
		$res.=priv_mdir_check($name{$i});
	}
	$res.=substr($name,3);
	return $res;
}

function priv_mdir_check($char) {
	if (!$char) return '_';
	if ($char=='.') return '_';
	return $char;
}

function mk_path($path,$name) {
	$name=mk_name($name);
	if (substr($path,-1)!='/') $path.='/';
	$path.=$name{0}.'/'.$name{1};
	priv_mdir_rmkd($path);
	$path.='/'.substr($name,2);
	return $path;
}	
