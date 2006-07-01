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
	$name=str_replace('/', '', $name);
	return $name;
}

function mk_path($path,$name) {
	$name=mk_name($name);
	if (substr($path,-1)!='/') $path.='/';
	$path.=$name{0}.'/'.$name{0}.$name{1};
	priv_mdir_rmkd($path);
	$path.='/'.$name;
	return $path;
}	
