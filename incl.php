<?php

@ini_set('precision','16');

if (function_exists('date_default_timezone_set')) {
	date_default_timezone_set('GMT');
} else putenv('TZ=GMT');
@ini_set('date.timezone','GMT');

if (!isset($_SERVER['HTTP_USER_AGENT'])) $_SERVER['HTTP_USER_AGENT'] = '';

if (!isset($_GET['debugtime'])) error_reporting(0);
if (php_sapi_name() == 'cli') error_reporting(E_ALL);
ini_set('session.use_cookies','0');
ini_set('mysql.connect_timeout','10');

$db_connected = '';
$mysqlqueries=0;
$incltime = microtime_float();

if (!isset($dodebug)) $dodebug = isset($_GET['debugtime']);
if ($dodebug) header('Content-type: text/plain; charset=utf-8');

function microtime_float() {
	return microtime(true);
}

function cleanup($msg='') {
	global $db_connected,$dodebug,$incltime,$mysqlqueries,$urlsockets,$getcount; 
	if ($db_connected != '') mysql_close();
	if ($dodebug) echo "\n".'<!-- '.$mysqlqueries.' Queries in '.(microtime_float()-$incltime)." seconds. $getcount gets. -->\n";
	echo $msg;
	$hosts = array_keys($urlsockets);
	for ($x = 0; $x < count($hosts); $x++) close_url_socket($hosts[$x]);

	exit();
}

function start_gzip() {
	global $dodebug;

	if ((!$dodebug) && (!headers_sent())) {
		ini_set('zlib.output_compression','On');
	}
}

function nvl($n,$v) {
	return (is_null($n))?$v:$n;
}

function do_connect() {
	global $db_connected,$dodebug,$incltime;

	$dbusername = "wowcensus";
	$dbpassword = "mZtcenYrtNxzy7WC";
	$dbschema = "edj";
	

	/*
	$dbusername = "undermine";
	$dbpassword = "VmApMjx8HfqqPYeJ";
	$dbschema = "edj";
	*/

	$olderr = error_reporting();
	error_reporting(0);

	//$host = 'localhost';
	$host = '192.168.188.202';

	if ($db_connected != '') {
		return $db_connected;
	}

	$db_connected = $host;
	if ($dodebug) echo "\n".Date('H:i:s')." Opening new connection to $host";
	$success = ($cn = mysql_connect($host,$dbusername,$dbpassword,false,MYSQL_CLIENT_COMPRESS));

	if (!$success) {
		$errno = mysql_errno();
		if ($dodebug) echo "\n".Date('H:i:s')." Unsuccessful connection to database: $errno ".mysql_error();
		$db_connected = '';
		switch ($errno) {
			case 1040:
			case 1203:
				send_test_pattern('There are too many active connections to our '.$hostnm.' database at the moment. Please try again later.');
				break;
			case 1037:
			case 1041:
				send_test_pattern('We ran out of memory on our '.$hostnm.' database at the moment. Please try again later.');
				break;
			default:
				send_test_pattern('We are unable to connect to the database at the moment. Please try again later.');
				break;				
		}
	}
	mysql_select_db($dbschema,$cn) or $db_connected = '';
	if ($db_connected != '') {
		if (function_exists('mysql_set_charset')) mysql_set_charset('utf8',$cn);
		else mysql_query('SET NAMES \'utf8\'',$cn);
		//mysql_query('set session transaction isolation level read uncommitted');
		mysql_query('set time_zone = \'+0:00\'',$cn);
		//if ($startpdo) $pdo = new PDO('mysql:host=localhost;dbname='.$dbschema,$dbusername,$dbpassword,array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
	}

	error_reporting($olderr);

	return $db_connected;
}

function get_single_row($query,$clidebug=false) {
	global $mysqlqueries,$dodebug, $db_connected;
	$mysqlqueries++;
	static $iscli = 0; if ($iscli == 0) $iscli = (php_sapi_name() == 'cli')?1:-1;
	if ($dodebug && ($clidebug || ($iscli != 1))) echo "\n".Date('H:i:s')." Single Row Query: $query ";
	if ($db_connected == '') do_connect();
	$recordset = mysql_query($query) or cleanup("query failed - " . mysql_error());
	$row = mysql_fetch_assoc($recordset);
	mysql_free_result($recordset);
	if ($dodebug && ($clidebug || ($iscli != 1))) echo "\n".Date('H:i:s')." Query Returned ";
	return $row;
}

function get_rst($query,$clidebug=false) {
	global $mysqlqueries,$dodebug, $db_connected;
	static $iscli = 0; if ($iscli == 0) $iscli = (php_sapi_name() == 'cli')?1:-1;
	if ($dodebug && ($clidebug || ($iscli != 1))) echo "\n".Date('H:i:s')." Multi-row Query: $query";
	$mysqlqueries++;
	if ($db_connected == '') do_connect();
	$recordset = mysql_query($query) or cleanup("query failed - " . mysql_error());
	if ($dodebug && ($clidebug || ($iscli != 1))) echo "\n".Date('H:i:s')." Query Returned ";
	return $recordset;
}

function next_row($recordset) {
	$row = mysql_fetch_assoc($recordset);
	if (!$row) mysql_free_result($recordset);
	return $row;
}

function run_sql($query,$clidebug=false) {
	global $db_connected,$dodebug;
	static $iscli = 0;
	if ($iscli == 0) $iscli = (php_sapi_name() == 'cli')?1:-1;
	global $mysqlqueries;
	$mysqlqueries++;
	$toreturn = "";
	if ($db_connected == '') do_connect();
	if ($dodebug && ($clidebug || ($iscli != 1))) echo "\n".Date('H:i:s')." Query run: $query";
	mysql_query($query) or $toreturn = mysql_error();
	if ($dodebug && ($clidebug || ($iscli != 1))) echo "\n".Date('H:i:s')." Query returned ";
	if (($clidebug || ($iscli == 1)) && ($toreturn != '')) echo "\nMySQL Error: $toreturn\n"; 
	return $toreturn;
}

function sql_esc($arg) {
	if (!is_scalar($arg)) {
		echo "sql_esc asked to escape type ".gettype($arg)."\n";
		print_r($arg);
		cleanup();
	} 
	return mysql_real_escape_string($arg);
}

function xml_esc($arg) {
	return str_replace('<','&lt;',str_replace('"','&quot;',str_replace('&','&amp;',$arg)));
}

$getcount = 0; 

$urlsockets = array();
$prevheader = '';
$prevhttpinfo = array();
function get_url_old($url,$topost='',&$etag='',$addlheaders='') {
	global $dodebug,$urlsockets,$prevheader,$prevhttpinfo,$getcount;
	$getcount++;
	if (($topost == '') && function_exists('http_get')) {
		if ($dodebug) echo "\n".Date('H:i:s')." Using pecl_http for $url";
		$hdrs = array();
		if ($addlheaders != '') {
			$h = explode("\r\n",$addlheaders);
			foreach ($h as $hl) if ($hl != '') {
				$ha = explode(": ",$hl,2);
				$hdrs[$ha[0]] = $ha[1];
			}
		}
		if (!isset($hdrs['Connection'])) $hdrs['Connection']='Keep-Alive';
		if ($topost != '') {
			if (!isset($hdrs['Content-Type'])) $hdrs['Content-Type'] = 'application/x-www-form-urlencoded';
			if (!isset($hdrs['Content-Length'])) $hdrs['Content-Length'] = strlen($topost);
		}
		$http_opt = array(
			'timeout' => 20,
			'connecttimeout' => 6,
			'headers' => $hdrs,
			'compress' => true,
			'redirect' => (preg_match('/^https?:\/\/(?:[a-z]+\.)?battle\.net\//',$url) > 0)?0:6
		);
		if ($etag != '') $http_opt['etag'] = $etag;
	//if ($dodebug) echo "\n".Date('H:i:s')." http_opt: ".print_r($http_opt,true);
		$http_info = array();
		if ($topost == '') {
			$data = http_get($url,$http_opt,$http_info);
		} else {
			$data = http_post_data($url,$topost,$http_opt,$http_info);
		}
		$data = http_parse_message($data);
		if ($dodebug) echo "\n".Date('H:i:s')." ".$http_info['size_download'].' bytes in '.$http_info['total_time'].' seconds ('.round($http_info['size_download']/1024/$http_info['total_time']).'kBps), '.$http_info['num_connects'].' connect'.($http_info['num_connects']==1?'':'s');
		$prevhttpinfo = $http_info;
		if (isset($data->headers)) {
			$prevheader = 'HTTP/'.$data->httpVersion.' '.$data->responseCode.' '.$data->responseStatus."\r\n";
			foreach ($data->headers as $k => $v) {
				$prevheader .= "$k: $v\r\n";
				if (strtolower($k) == 'etag') $etag = $v;
			}
		} else $prevheader = '';
		if (preg_match('/^2\d\d$/',$http_info['response_code']) > 0) return $data->body; else return '';
	} else $prevhttpinfo = array();

	static $urlstack = array();
	static $nogzip = false;
	static $cookies = array();
	$nogzip |= !function_exists('gzinflate');
	//$nogzip = true;
	static $opencounts = array();
	if (in_array($url,$urlstack)) {
		if ($dodebug) echo "\n".Date('H:i:s')." $url already on the stack!";
		return '';
	}
	$filedata = '';
	$https = (strtolower(substr($url, 0, 8)) == 'https://');
	array_push($urlstack,$url);
	if (function_exists('mb_substr')) {
		$path = mb_ereg_replace(' ','%20',mb_substr($url, mb_strpos($url, '/', 12)));
		$host = mb_substr($url, mb_strpos($url, '://')+3);
		$host = mb_substr($host, 0, mb_strpos($host, '/'));
	} else {
		$path = str_replace(' ','%20',substr($url, strpos($url, '/', 12)));
		$host = substr($url, strpos($url, '://')+3);
		$host = substr($host, 0, strpos($host, '/'));
	}
	$port = $https?443:80;
	if (strpos($host,':') !== false) {
		$port = intval(substr($host, strpos($host,':')+1));
		$host = substr($host, 0, strpos($host,':'));
	}
	$hostport = $host.':'.$port;

	if (count($urlsockets) > 0)
		foreach ($urlsockets as $k => $v) 
			if ($v['lastused'] < (time()-8))
				close_url_socket($k);

	if (isset($urlsockets[$hostport])) {
		$streaminfo = stream_get_meta_data($urlsockets[$hostport]['handle']);
		if (($urlsockets[$hostport]['lastused'] < (time()-9)) || ($streaminfo['timed_out']) || ($urlsockets[$hostport]['hits'] >= 100)) {
			if ($dodebug) echo "\n".Date('H:i:s')." Socket to $hostport expired, closing first";
			close_url_socket($hostport);
		} else {
			if ($dodebug) echo "\n".Date('H:i:s')." Reusing socket to $hostport";
			$handle = $urlsockets[$hostport]['handle'];
			if (fputs($handle, ($topost==''?'GET ':'POST ').$path." HTTP/1.1\r\n") === false) {
				if ($dodebug) echo "\n".Date('H:i:s')." Write failed to reused socket";
				close_url_socket($hostport);
				unset($handle);
			}
		}
	}
	if (!isset($handle)) {
		if ($dodebug) echo "\n".Date('H:i:s')." Opening socket to $hostport";
		if (!isset($opencounts[$hostport])) $opencounts[$hostport] = 0;
		$opencounts[$hostport]++;
		if ($opencounts[$hostport] > 4) sleep(1.5);
		if (($handle = fsockopen(($https?'ssl://':'').$host, $port, $errno, $errstr,6)) === false) {
			if ($dodebug) echo "\n".Date('H:i:s').' Socket open failed: '.$errstr;
			$header = '';
			unset($handle);
		} else {
			if ($dodebug) echo "\n".Date('H:i:s').' Socket open successful.';
			fputs($handle, ($topost==''?'GET ':'POST ').$path." HTTP/1.1\r\n");
		}
	}
	if (isset($handle)) {
		$sendheaders = 'Host: '.$host."\r\n";
		$sendheaders .= $addlheaders;
		if (stripos($addlheaders,'User-Agent: ') === false) switch ($host) {
			case '192.168.137.238':
			case '127.0.0.1':
				$sendheaders .= "User-Agent: KezanBot\r\n";
				break;
			default:
				$sendheaders .= "User-Agent: Mozilla/5.0 (en-US; rv:1.8.1.3; IJustWantXML) Gecko/20070309 Firefox/2.0.0.3\r\n";
				break;
		}
		if ($topost != '') {
			if (stripos($addlheaders,'Content-Type: ') === false) $sendheaders .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$sendheaders .= "Content-Length: ".strlen($topost)."\r\n";
		}
		if (!$nogzip) {
			if ($dodebug) echo "\n".Date("H:i:s")." Allowing gzip on this ".($topost==''?'GET ':'POST ');
			$sendheaders .= "Accept-Encoding: gzip\r\n";
		}
		$cookieset = '';
		foreach ($cookies as $cookie) {
			if ((strtolower(substr($host, -1 * strlen($cookie['domain']))) == strtolower($cookie['domain'])) && (substr($path, 0, strlen($cookie['path'])) == $cookie['path'])) {
				//if ($dodebug) echo "\n".Date("H:i:s")." Cookie: ".$cookie['VALUE'];
				$cookieset .= (($cookieset=='')?'':'; ').$cookie['VALUE'];
			}
		}
		if ($cookieset != '') {
			if ($dodebug) echo "\n".Date("H:i:s")." Cookie: ".$cookieset;
			$sendheaders .= "Cookie: ".$cookieset."\r\n";
		} //else if ($host == 'eu.wowarmory.com') fputs($handle, "Cookie: loginChecked=1\r\n");
		if ($etag != '') $sendheaders .= "If-None-Match: $etag\r\n";
		$sendheaders .= "Connection: Keep-Alive\r\n";
		if ($dodebug) echo "\n".Date("H:i:s")." Outgoing headers:\n".str_replace("\r",'',$sendheaders);

		fputs($handle, $sendheaders."\r\n");

		if ($topost != '') fputs($handle, $topost);
		if ($dodebug) echo "\n".Date("H:i:s")." Starting reads, path: $path";
		$gotheaders = false; $inchunks = false; $isgzipped = false;
		$header = ''; $filedata = '';
		$chunksize = 8192; $datalength = 0;
		$speedtime = microtime(true);
		$readstarttime = time();
//$debugfile = tempnam('/tmp','geturlold.output.');
		do {
			stream_set_timeout($handle, 10);
			stream_set_blocking($handle, false);
			if (!$gotheaders) {
				$_data = fgets($handle);
//file_put_contents($debugfile, $_data, FILE_APPEND);
				$_data = trim($_data);
				if ($_data != '') {
					$header .= $_data."\r\n";
					$readstarttime = time();
				} else usleep(10000);
				if (($header != '') && ($_data == '')) {
					$gotheaders = true;
					$prevheader = $header;
					$inchunks = (strpos(strtolower($header), 'transfer-encoding: chunked') !== FALSE);
					$isgzipped = (strpos(strtolower($header), 'content-encoding: gzip') !== FALSE);
					$cnt = preg_match('/Content-Length: *([0-9]+)/i',$header,$mc); $datalength=($cnt>0)?intval($mc[1]):0;
					if ($dodebug) echo "\n".Date("H:i:s")." Got header:\n$header";
				}
			} else {
				if ($inchunks) {
					do {
						$_data = fgets($handle);
//file_put_contents($debugfile, $_data, FILE_APPEND);
						$curline = trim($_data);
						if (strpos($curline, ';') !== FALSE) $curline = trim(substr($curline, 0, strpos($curline, ';')));
						if ($curline != '') $readstarttime = time(); else usleep(10000);
						if ((time() - $readstarttime) >= 8) $curline = '0';
					} while ($curline == ''); // && isset($_data{0}));
					if ((strlen($curline) > 8) || (preg_match('/[^a-f0-9]/i',$curline) > 0)) {
						$header = ''; $isgzipped = false; $filedata = '';
						if ($dodebug) echo "\n".Date("H:i:s")." Bad chunk size! Length: ".strlen($curline);
						break;
					}
					$chunksize = hexdec($curline); 
//					if ($dodebug) echo "\n".Date("H:i:s")." Chunk size: $chunksize";
					
				} else if ($datalength > 0) $chunksize = min(8192, $datalength - strlen($filedata)); else $chunksize = 8192;
				if ($chunksize > 0) {
					$_data = '';
					$readsuccess = false;
//					if ($dodebug) echo "\n".Date("H:i:s")." Read ".strlen($_data)." of $chunksize";
					do {
						$readsuccess = ($_data .= fread($handle, $chunksize-strlen($_data)));
						if ($_data != '') {
//							if ($dodebug) echo "\n".Date("H:i:s")." Read ".strlen($_data)." of $chunksize";
							$readstarttime = time(); 
						} else usleep(10000);
						if ((time() - $readstarttime) >= 8) break;
					
					} while ($inchunks && (($chunksize-strlen($_data)) > 0) && (!feof($handle)) && ($readsuccess !== false));
//file_put_contents($debugfile, $_data, FILE_APPEND);
					$filedata .= $_data;
				}
			}
			if ((time() - $readstarttime) >= 8) {
				$header = ''; $isgzipped = false;
				if ($dodebug) echo "\n".Date("H:i:s")." Read timeout";
				break;
			}
		} while ((!$gotheaders) || ($inchunks && ($chunksize > 0)) || (strlen($filedata) < $datalength));
		if ($dodebug) echo "\n".Date("H:i:s")." Reads complete. ".strlen($filedata).' bytes';
		if ($dodebug && (microtime(true) > $speedtime)) echo ' at '.round(strlen($filedata)/(microtime(true)-$speedtime)/1024,2).'kBps';

		if ($dodebug) echo "\n".Date("H:i:s")." Saving handle to $hostport";
		$urlsockets[$hostport]['handle'] = $handle;
		$urlsockets[$hostport]['lastused'] = time();
		if (isset($urlsockets[$hostport]['hits'])) {
			$urlsockets[$hostport]['hits']++;
		} else $urlsockets[$hostport]['hits'] = 1;
		if (strpos(strtolower($header), 'connection: close') !== FALSE) {
			if ($dodebug) echo "\n".Date("H:i:s")." Closing connection as requested";
			close_url_socket($hostport);
		}
		$cc = preg_match_all('/\n\s*Set-Cookie: ([^\n\r]+)/i', $header, $cres);
		if ($cc > 0) {
			if ($dodebug) echo "\n".Date("H:i:s")." Parsing $cc cookies";
			for ($x = 0; $x < $cc; $x++) {
				$cook = array();
				$cparts = explode(';',$cres[1][$x]);
				$cook['VALUE'] = $cparts[0];
				$cook['domain'] = $host;
				$cook['path'] = substr($path, 0, strrpos($path,'/'));
				for ($y = 1; $y < count($cparts); $y++) {
					$cparts[$y] = trim($cparts[$y]);
					if (strpos($cparts[$y],'=')===false) {
						$cook[strtolower($cparts[$y])] = true;
						continue;
					}
					$cparts[$y] = explode('=',$cparts[$y],2);
					$cook[strtolower($cparts[$y][0])] = $cparts[$y][1];
				}
						
				for ($y = 0; $y < count($cookies); $y++) 
					if ((substr($cookies[$y]['VALUE'], 0, strpos($cookies[$y]['VALUE'],'='))==substr($cook['VALUE'], 0, strpos($cook['VALUE'],'='))) && ($cook['domain'] == $cookies[$y]['domain']) && ($cook['path'] == $cookies[$y]['path'])) {
						if (strpos($cook['VALUE'],'=') == (strlen($cook['VALUE'])-1)) {
							if ($dodebug) echo "\n".Date("H:i:s")." Removing cookie: ".serialize($cook);
							array_splice($cookies, $y, 1);
						} else {
							if ($dodebug) echo "\n".Date("H:i:s")." Updating cookie: ".serialize($cook);
							array_splice($cookies, $y, 1, array($cook));
						}
						unset($cook);
						break;
					}
				if (isset($cook)) {
					if ($dodebug) echo "\n".Date("H:i:s")." Adding cookie: ".serialize($cook);
					$cookies[] = $cook;
					unset($cook);
				}
			}
			//if ($dodebug) print_r($cookies);
		}
	}

	$etag = (preg_match('/[\r\n]ETag: ?([^\r\n]+)[\r\n]/',$header,$res) > 0)?$res[1]:$etag;

	switch ((preg_match('/\b\d\d\d\b/',$header,$res) > 0)?$res[0]:'500') {
		case '301': //moved permanently
		case '302': //found
		case '303': //see other
		case '307': //temporary redirect
			$newloc = (preg_match('/Location: ([^\n\r]+)[\n\r]/',$header,$res) > 0)?$res[1]:'';
			if ($newloc == '') {
				if ($dodebug) echo "\n".Date("H:i:s")." Bad header, 302 without Location";
				array_pop($urlstack);	
				return '';
			}
			if (preg_match('/^http(s)?:\/\//',$newloc) == 0) $newloc = 'http'.($https?'s':'').'://'.$hostport.$newloc;
			if ($dodebug) echo "\n".Date("H:i:s")." Redirect to $newloc";
			$ret = get_url_old($newloc);
			array_pop($urlstack);
			return $ret;
		case '304': //not modified
			//array_pop($urlstack);
			if ($dodebug) echo "\n".Date("H:i:s")." Good header, 304 not modified";
			//return $filedata;
		case '200': //ok
		case '201': //created
		case '202': //accepted
		case '203': //non-authoritative
		case '204': //no content
		case '205': //reset content'
			array_pop($urlstack);
			if ($dodebug) echo "\n".Date("H:i:s")." Good header";
			if ($isgzipped && (strlen($filedata) > 0)) {
				if ($dodebug) echo "\n".Date("H:i:s")." Was gzipped, extracting";
				$filedata2 = mygzuncompress($filedata);
				if ($filedata2 == '') {
					$filedata = '';
					if ($dodebug) echo "\n".Date("H:i:s")." Couldn't ungzip";
					if ((!$nogzip) && ($topost == '')) {
						if ($dodebug) echo ", retrying w/o gzip";
						array_pop($urlstack);
						$nogzip = true;
						$filedata2 = get_url_old($url);
						$nogzip = false;
						array_push($urlstack,$url);
						$header = $prevheader;
						if ($filedata2 != '') if ($dodebug) echo "\n".Date("H:i:s")." Data returned without gzip";
					}
				} else if ($dodebug) echo "\n".Date("H:i:s")." Ungzip successful. ".strlen($filedata)." -> ".strlen($filedata2).", ".round(strlen($filedata)/strlen($filedata2)*100,1).'%';
				$filedata = $filedata2;
			}
			return $filedata;
		default:
			array_pop($urlstack);
			if ($dodebug) echo "\n".Date("H:i:s")." Bad header";
			if ($dodebug && (strlen($filedata) < 1025)) {
				if ($isgzipped) {
					//if ($dodebug) echo "\n".Date("H:i:s")." Was gzipped, extracting";
					$filedata2 = mygzuncompress($filedata);
					if ($filedata2 == '') {
						$filedata = '';
						if ($dodebug) echo "\n".Date("H:i:s")." Couldn't ungzip";
						if ((!$nogzip) && ($topost == '')) {
							if ($dodebug) echo ", retrying w/o gzip";
							array_pop($urlstack);
							$nogzip = true;
							$filedata2 = get_url_old($url);
							$nogzip = false;
							array_push($urlstack,$url);
							$header = $prevheader;
							if ($filedata2 != '') if ($dodebug) echo "\n".Date("H:i:s")." Data returned without gzip";
						}
					} //else if ($dodebug) echo "\n".Date("H:i:s")." Ungzip successful";
					$filedata = $filedata2;
				}
				if (strlen($filedata) < 1025) echo "\n$filedata\n";
			}
			if (isset($urlsockets[$hostport])) close_url_socket($hostport);
			return '';
	}
}

function close_url_socket($host) {
	global $urlsockets,$dodebug;
	if (isset($urlsockets[$host])) {
		if ($dodebug) echo "\n".Date("H:i:s")." Closing socket to $host";
		@fclose($urlsockets[$host]['handle']);
		unset($urlsockets[$host]);
	}
}


function check_utf8($str) {
	$len = strlen($str);
	for($i = 0; $i < $len; $i++){
		$c = ord($str[$i]);
		if ($c > 128) {
			if (($c > 247)) return false;
			elseif ($c > 239) $bytes = 4;
			elseif ($c > 223) $bytes = 3;
			elseif ($c > 191) $bytes = 2;
			else return false;
			if (($i + $bytes) > $len) return false;
			while ($bytes > 1) {
				$i++;
				$b = ord($str[$i]);
				if ($b < 128 || $b > 191) return false;
				$bytes--;
			}
		}
	}
	return true;
}

function mb_trim($s) {
	return mb_ereg_replace('^\s+|\s+$','',$s);
}

function mygzuncompress($d) {
	if (substr($d,0,3) != (chr(31).chr(139).chr(8))) return '';
	$flags = ord(substr($d,3,1));
	$fhcrc = ($flags & 2 > 0);
	$fextra = ($flags & 4 > 0);
	$fname = ($flags & 8 > 0);
	$fcomment = ($flags & 16 > 0);

	$datastart = 10;
	if ($fextra) $datastart += ord(substr($d,$datastart,1)) + (256 * ord(substr($d,$datastart,2))) + 2;
	if ($fname) while (substr($d,$datastart++,1) != "\0");
	if ($fcomment) while (substr($d,$datastart++,1) != "\0");
	if ($fhcrc) $datastart += 2;

	$cb = substr($d, $datastart, -8);
	return gzinflate($cb);
}

function timeDiff($time, $opt = array()) {
	if (nvl($time,0)==0) return '';
	global $dodebug;
	//if ($dodebug) echo "\n".Date('H:i:s').' Finding timeDiff for '.$time;
	// The default values
	$defOptions = array(
		'to' => 0,
		'parts' => 2,
		'precision' => 'minute',
		'distance' => TRUE,
		'separator' => ', '
	);
	$opt = array_merge($defOptions, $opt);
	// Default to current time if no to point is given
	(!$opt['to']) && ($opt['to'] = time());
	// Init an empty string
	$str = '';
	// To or From computation
	$diff = ($opt['to'] > $time) ? $opt['to']-$time : $time-$opt['to'];
	// An array of label => periods of seconds;
	$periods = array(
		'decade' => 315569260,
		'year' => 31556926,
		'month' => 2629744,
		'week' => 604800,
		'day' => 86400,
		'hour' => 3600,
		'minute' => 60,
		'second' => 1
	);
	// Round to precision
	if ($opt['precision'] != 'second')
	$diff = round(($diff/$periods[$opt['precision']])) * $periods[$opt['precision']];
	// Report the value is 'less than 1 ' precision period away
	(0 == $diff) && ($str = 'less than 1 '.$opt['precision']);
	// Loop over each period
	foreach ($periods as $label => $value) {
	// Stitch together the time difference string
	(($x=floor($diff/$value))&&$opt['parts']--) && $str.=($str?$opt['separator']:'').($x.' '.$label.($x>1?'s':''));
	// Stop processing if no more parts are going to be reported.
	if ($opt['parts'] == 0 || $label == $opt['precision']) break;
	// Get ready for the next pass
	$diff -= $x*$value;
	}
	$opt['distance'] && $str.=($str&&$opt['to']>=$time)?' ago':' away';
	return $str;
}

function send_test_pattern($msg = '') {
	if (!headers_sent()) header('HTTP/1.0 503 Service Unavailable');
	start_gzip();
	?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Please stand by</title>
<style type="text/css">
html, body{margin:0; padding:0; float:left; width:100%; height:100%; overflow:hidden}
</style>
</head>
<body><!-- img src="images/press.jpg" style="position: absolute; width: 100%; z-index: -1"/ -->
<div style="margin: 10%; text-align: center; font-size: 24px">
<big style="font-size: 48px">WoW Census</big><br/><br/>
<?php
	//$msg = 'Currently performing some standard maintenance. We should be back online soon. ('.timeDiff(strtotime('20:00')).')';
	//$msg = 'Working on switching to Blizzard\'s new Auction House API. We should be back online soon. <nobr>('.timeDiff(strtotime('05:30')).')</nobr>';
	//$msg = 'Performing some good old-fashioned database maintenance. Back soon.';
	if ($msg == '') {
		echo "Please stand by. We're working to restore service as soon as possible.";
	}	
	//  We should be back online soon timeDiff(strtotime('22:30'))
	echo $msg;

	echo '</div></body></html>';
	cleanup();
}

function fixtimezone($tz) {
	switch ($tz) {
		case 'CET':
			return 'Europe/Paris';
		case 'EST':
			return 'America/New_York';
		case 'CST':
			return 'America/Chicago';
		case 'MST':
			return 'America/Boise';
		case 'PST':
			return 'America/Los_Angeles';
		case 'AEST':
			return 'Australia/Victoria';
	}
	return 'UTC';
}

?>
