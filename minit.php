<?php

require_once('../undermine/jsmin.incl.php');

function errecho($s) {
	fwrite(STDERR,$s);
}

$bytessaved = 0;

function preg_js($m) {
	global $bytessaved;
	$js = JSMin::minify($m);
	$x = (strlen($m)-strlen($js));
	$bytessaved += $x;
	//errecho("Saved ".$x." bytes!\n");

	return $js;
}

$str = ''; while (!feof(STDIN)) $str .= fread(STDIN,8192); 
$firstlength = strlen($str);
$str = preg_js($str);
errecho("Saved $bytessaved bytes with minifying js\n");

echo $str;


?>
