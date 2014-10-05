<?php

if ((!isset($_SERVER['REMOTE_ADDR'])) || ($_SERVER['REMOTE_ADDR'] != '192.168.188.202')) {
	die();
}

$auctioncensuspath = '/home/erorus/public_html/wowcensus/auctioncensus/';
if (is_dir($auctioncensuspath) && ($aucdir = opendir($auctioncensuspath))) {
	while (($fn = readdir($aucdir)) !== false) {
		if (!is_dir($auctioncensuspath.$fn)) {
			$tfn = tempnam('/tmp','censuscloud');
			if (rename($auctioncensuspath.$fn,$tfn)) {
				echo $fn."\n";
				readfile($tfn);
			}
			unlink($tfn);
			break;
		}
	}
	closedir($aucdir);
}

?>
