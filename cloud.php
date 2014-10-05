<?php

require_once('incl.php');

function cloudinsertresource($realmset,$resource,$realmslug,$name) {
	static $cloudrowcount = -1;
	global $db_connected;
	if ($db_connected == '') do_connect();
	if ($cloudrowcount < 0) {
		$row = get_single_row('select count(*) c from edj.tblCloud');
		$cloudrowcount = intval($row['c']);
	}
	if ($cloudrowcount > 10000) return false;
	if ($resource=='') return true;
	$cloudrowcount++;
	run_sql('insert ignore into edj.tblCloud (realmset, resource, realmslug, name, requested) values (\''.sql_esc($realmset).'\',\''.sql_esc($resource).'\',\''.sql_esc($realmslug).'\',\''.sql_esc($name).'\',now())');
	return true;
}

function cloudreturndata($url,$data) {
	global $db_connected;
	if (preg_match('/^\/\/(us|eu)\.battle\.net\/api\/wow\/(character|guild)\/([^\/]+)\/([^\/]+)\?fields=(?:guild|members)$/',$url,$ret) == 0) return;

	if ($db_connected == '') do_connect();
	list(,$realmset,$resource,$realmslug,$name) = $ret;
	$name = rawurldecode($name);

	if (strlen($data) <= 10) $hash = sha1($data);
	// verified if(ifnull(fromip,inet_aton(\''.sql_esc($_SERVER['REMOTE_ADDR']).'\'))=inet_aton(\''.sql_esc($_SERVER['REMOTE_ADDR']).'\'), 0, if(sha(data)=\''.sql_esc($hash).'\',verified+1,0))
	$verifiedsql = (strlen($data) > 10)?'1':('if(ifnull(fromip,inet_aton(\''.sql_esc($_SERVER['REMOTE_ADDR']).'\'))=inet_aton(\''.sql_esc($_SERVER['REMOTE_ADDR']).'\'), 0, if(sha(data)=\''.sql_esc($hash).'\',verified+1,verified))');
	run_sql('update edj.tblCloud set data=ifnull(data,\''.sql_esc($data).'\'), verified='.$verifiedsql.', fromip=inet_aton(\''.sql_esc($_SERVER['REMOTE_ADDR']).'\') where realmset=\''.sql_esc($realmset).'\' and resource=\''.sql_esc($resource).'\' and realmslug=\''.sql_esc($realmslug).'\' and name=\''.sql_esc($name).'\'');
	
}

function cloudrequesturl() {
	global $db_connected;
	if ($db_connected == '') do_connect();
	run_sql('start transaction with consistent snapshot;');
	//and 1500 > (select count(*) from tblCloud c2 where c2.verified > 0)
	$row = get_single_row('select realmset,resource,realmslug,name from edj.tblCloud where verified=0 and ifnull(fromip,0) != inet_aton(\''.sql_esc($_SERVER['REMOTE_ADDR']).'\') and resource=\'guild\' order by requested asc limit 1');
	if (!isset($row['name'])) $row = get_single_row('select realmset,resource,realmslug,name from edj.tblCloud where verified=0 and ifnull(fromip,0) != inet_aton(\''.sql_esc($_SERVER['REMOTE_ADDR']).'\') and resource=\'character\' order by requested asc limit 1');

	//if (!isset($row['name'])) $row = get_single_row('select realmset,resource,realmslug,name from edj.tblCloud where verified=0 and ifnull(fromip,0) != inet_aton(\''.sql_esc($_SERVER['REMOTE_ADDR']).'\') and 1500 > (select count(*) from tblCloud c2 where c2.verified > 0) and resource=\'character\' order by requested asc limit 1');
	if (isset($row['name'])) {
		run_sql('update edj.tblCloud set requested = timestampadd(second,10,requested) where realmset=\''.sql_esc($row['realmset']).'\' and resource=\''.sql_esc($row['resource']).'\' and realmslug=\''.sql_esc($row['realmslug']).'\' and name=\''.sql_esc($row['name']).'\'');
		$url = '//'.strtolower($row['realmset']).'.battle.net/';
		switch ($row['resource']) {
			case 'character':
				$url .= 'api/wow/character/'.rawurlencode($row['realmslug']).'/'.rawurlencode($row['name']).'?fields=guild';
				break;
			case 'guild':
				$url .= 'api/wow/guild/'.rawurlencode($row['realmslug']).'/'.rawurlencode($row['name']).'?fields=members';
				break;
			default:
				run_sql('commit');
				return '';
		}	
	} else $url = '';
	run_sql('commit');	
	return $url;
}

function cloudpulldata($resource='') {
	global $db_connected;
	if ($db_connected == '') do_connect();
	run_sql('start transaction with consistent snapshot;');
	$sql = 'select * from edj.tblCloud where verified > 0 '.(($resource != '')?(' and resource=\''.sql_esc($resource).'\' '):'').' order by realmset, realmslug limit 1';
	$row = get_single_row($sql);
	if (isset($row['name'])) {
		run_sql('delete from edj.tblCloud where realmset=\''.sql_esc($row['realmset']).'\' and resource=\''.sql_esc($row['resource']).'\' and realmslug=\''.sql_esc($row['realmslug']).'\' and name=\''.sql_esc($row['name']).'\'');
	} else $row = array();
	run_sql('commit');
	return $row;
}

function varwidth_base64_decode($enc) {
	$keystr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_=";
	for ($x = 0; $x < strlen($keystr); $x++) $key[substr($keystr,$x,1)] = $x;
	$bitwidth = $key[substr($enc,0,1)];
	//echo "Bitwidth: $bitwidth\n";
	$ret = array();

	$widthmask = 0;
	for ($x = 0; $x < $bitwidth; $x++) 
		$widthmask = $widthmask << 1 | 1;
	//echo "widthmask: $widthmask\n";

	$curbits = 0; $curword = 0;	
	$i = 1; $enclen = strlen($enc);
	while ($i < $enclen) {
		while (($curbits < $bitwidth) && ($i < $enclen)) {
			$v = $key[substr($enc,$i++,1)];
			$curword = $curword << 5 | $v;
			$curbits += 5;
		//	echo "Position: $i Word: $curword Bits: $curbits\n";
		}
		// get top bitwidth bits
		$curbyte = ($curword & ($widthmask << ($curbits - $bitwidth))) >> ($curbits - $bitwidth);
		//echo "New Byte: $curbyte\n";

		// remove top bitwidth bits
		$mask = 0;
		for ($y = $curbits; $y > $bitwidth; $y--) $mask = $mask << 1 | 1;
		$curbits -= $bitwidth;
		$curword = $curword & $mask;

		//echo "New Word: $curword Bits: $curbits\n";

		$ret[] = $curbyte;
	}
	return $ret;
}

function delzw($comp) {
	$dic = array();
	for ($x = 0; $x < 256; $x++) $dic[$x]=chr($x);
	$dictsize = 256;

	$w = chr($comp[0]);
	$result = $w;

	for ($i = 1; $i < count($comp); $i++) {
		$k = $comp[$i];
		if (isset($dic[$k])) {
			$entry = $dic[$k];
		} else {
			if ($k == $dictsize) {
				$entry = $w.substr($w,0,1);
			} else {
				//echo "what?\n";
				return '';
			}
		}

		$result .= $entry;
		$dic[$dictsize++] = $w.substr($entry,0,1);

		$w = $entry;
	}

	return $result;
}

function checkorigin() {
	if (!isset($_SERVER['HTTP_ORIGIN'])) return;
	switch ($_SERVER['HTTP_ORIGIN']) {
		case 'http://192.168.0.5':
		case 'http://origin.realmpop.com':
		case 'http://wow.realmpop.com':
		case 'http://realmpop.com':
		case 'http://theunderminejournal.com':
		case 'http://eu.theunderminejournal.com':
			header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
			return;
		default:
			cleanup();
	}
}

if (isset($_GET['cloudreturndata']) && isset($_POST['url']) && isset($_POST['data'])) {
	checkorigin();
	if (!isset($_GET['cloudrequesturl'])) header('HTTP/1.1 204 No Content');
	$dta = $_POST['data'];
	if (isset($_POST['lzw'])) $dta = delzw(varwidth_base64_decode($dta));
	cloudreturndata($_POST['url'],$dta);
}

if (isset($_GET['cloudrequesturl'])) {
	checkorigin();
	$url = cloudrequesturl();
	if ($url != '') { 
		header('HTTP/1.1 200 OK');
		header('Content-type: text/plain');
		echo $url;
	} else {
		header('HTTP/1.1 204 No Content');
	}
	cleanup();
}

?>
