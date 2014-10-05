<?php

if (intval(shell_exec('ps -o args -C php | grep '.escapeshellarg(implode(' ',$argv)).' | wc -l')) > 1) die();
if (intval(shell_exec('ps -o args -C php | grep buildjsons | wc -l')) > 0) die();

require_once('incl.php');
require_once('cloud.php');

/*
$awsset = array();
$achwatched = array();
function achsetpush($key,$achset) {
	global $awsset;
	if ($key != '') $awsset[$key] = $achset;
	if (!((($key == '') && (count($awsset) > 0)) || (count($awsset) >= 25))) return;
	$awsfn = tempnam('aws/','aws');
	if ($awsfn == '') return;
	file_put_contents($awsfn,serialize($awsset),LOCK_EX);
	$awsset = array();
}
*/

function realmid_lookup($rn) {
	static $seen = array();
	if (isset($seen[$rn]))
		return $seen[$rn];
	
	$r = explode('-',$rn,2);
	if (count($r) < 2) {
		echo "Got $rn for realmid lookup\n";
		cleanup();
	}
		
	$realmrow = get_single_row('select id from tblRealm where realmset=\''.sql_esc($r[0]).'\' and name=\''.sql_esc($r[1]).'\'');
	if (!isset($realmrow['id'])) 
		$realmrow['id'] = 0;
	
	$seen[$rn] = $realmrow['id'];
	
	return $realmrow['id'];
}

function connrealm_lookup($lookupid) {
	static $seen = array();
	if (isset($seen[$lookupid]))
		return $seen[$lookupid];
		
	$tr = array();
	$rst = get_rst('select connto from tblConnectedRealm where lookup=\''.sql_esc($lookupid).'\'');
	while ($row = next_row($rst))
		$tr[] = $row['connto'];
	
	$seen[$lookupid] = $tr;
	
	return $tr;
}

function insert_character($json,$realmid,$guildid='null') {
//	global $achwatched;
	if (!is_numeric($realmid)) 
		$realmid = realmid_lookup($realmid);
	if (isset($json['level'])) {
		$sql = 'insert into tblCharacter (name, realmid, guildid, scanned, race, class, gender, level) values (\''.sql_esc($json['name']).'\', \''.$realmid.'\', ';
		$sql .= $guildid.', now(), \''.sql_esc($json['race']).'\',\''.sql_esc($json['class']).'\',\''.fixgender($json['gender']).'\',\''.sql_esc($json['level']).'\')';
		$sql .= ' on duplicate key update guildid=values(guildid),scanned=values(scanned),race=values(race),class=values(class),gender=values(gender),level=values(level)';
		run_sql($sql);

		/*
		if (isset($json['achievementsCompleted'])) {
			$a = array_combine($json['achievementsCompleted'],$json['achievementsCompletedTimestamp']);
			$achset = array();
			foreach ($achwatched as $achid) 
				if (isset($a[$achid]))
					$achset['a'.$achid] = preg_replace('/:\d+([\+\-])/','$1',Date('c',substr($json['achievementsCompletedTimestamp'],0,-3)));

			if (


		}
		*/

	} else if (isset($json['name'])) {
		if ($guildid != 'null') {
			echo "Got guild ID $guildid but not level, doing insert ignore: \n";
			print_r($json);
		}
		$sql = 'insert ignore into tblCharacter (name, realmid, guildid) values (\''.sql_esc($json['name']).'\', \''.$realmid.'\', '.$guildid.')';
		run_sql($sql);
	}
}

function get_guild($json,$realmid,$doinsert=true) {
	static $guildids = array();
	if (!is_numeric($realmid)) 
		$realmid = realmid_lookup($realmid);
	if (!isset($guildids[$realmid])) $guildids[$realmid] = array();
	if (!isset($json['name'])) return 'null';
	if (isset($guildids[$realmid][$json['name']])) return $guildids[$realmid][$json['name']];
	
	$guildrow = get_single_row('select * from tblGuild where realmid=\''.sql_esc($realmid).'\' and name=\''.sql_esc($json['name']).'\'');
	if (!isset($guildrow['id'])) {
		if (!$doinsert) return 'null';
		run_sql('insert ignore into tblGuild (realmid,name,scanned) values (\''.sql_esc($realmid).'\', \''.sql_esc($json['name']).'\',null)');
		$guildrow = get_single_row('select * from tblGuild where realmid=\''.sql_esc($realmid).'\' and name=\''.sql_esc($json['name']).'\'');
	}

	$lastmod = isset($json['lastModified'])?(',lastmodified=\''.Date('Y-m-d H:i:s',intval(substr($json['lastModified'],0,10))).'\''):'';
	$emblemdata = '';
/*	if (isset($json['emblem'])) {
		$emblemdata = ',iconid=\''.sql_esc($json['emblem']['icon']).'\'';
		$emblemdata .= ',borderid=\''.sql_esc($json['emblem']['border']).'\'';
		$emblemdata .= ',iconcolor=\''.fixcolor($json['emblem']['iconColor']).'\'';
		$emblemdata .= ',bordercolor=\''.fixcolor($json['emblem']['borderColor']).'\'';
		$emblemdata .= ',backgroundcolor=\''.fixcolor($json['emblem']['backgroundColor']).'\'';
	}
*/	
	if (isset($json['side'])) $emblemdata .= ',side=\''.fixside($json['side']).'\'';
	if (isset($json['members'])) $emblemdata .= ',members=\''.sql_esc(is_scalar($json['members'])?$json['members']:count($json['members'])).'\'';
		
	if (isset($json['members'])) run_sql('update tblGuild set scanned=now()'.$lastmod.$emblemdata.' where id=\''.$guildrow['id'].'\'');

    if (nvl($guildrow['side'],'') != '') {
            $guildrow = get_single_row('select * from tblGuild where realmid=\''.sql_esc($realmid).'\' and name=\''.sql_esc($json['name']).'\'');
            if (nvl($guildrow['side'],'') == '') file_put_contents('/tmp/nullside.txt',print_r($json,true));
    }
        
	$guildids[$realmid][$json['name']] = $guildrow['id'];
	return $guildrow['id'];
}

function fixcolor($colcode) {
	if (preg_match('/^[0-9A-Fa-f]{8}$/',$colcode) == 0) return '';
	return hexdec(substr($colcode,2));
}

function fixgender($jsongender) {
	//echo "fixgender got $jsongender\n";
	switch(strval($jsongender)) {
		case '1':
		case 'female':
		case 'Female':
			return 'Female';
			break;
		default:
			return 'Male';
			break;
	}
}

function fixside($jsonside) {
	switch(strval($jsonside)) {
		case '1':
		case 'horde':
		case 'Horde':
			return 'Horde';
			break;
		default:
			return 'Alliance';
			break;
	}
}

$dodebug = true;
do_connect();

/*
$rst = get_rst('select achid from tblAcctAchs');
while ($row = next_row($rst)) $achwatched[] = $row['achid'];
*/

// pull parsed guilds
// save guilds, push guild members (supply guild id)
// pull parsed chars
// get guild id for chars, save chars
// pull ah chars
// save chars, no guild id
// send requests for unknowns/olds

echo "\n".Date('H:i:s')." Start! Finding parsed guilds.";

$cloudrow = cloudpulldata('guild');
while (isset($cloudrow['name'])) {
	while (1==1) {
		$realmrow = get_single_row('select id from tblRealm where realmset=\''.sql_esc($cloudrow['realmset']).'\' and slug=\''.sql_esc($cloudrow['realmslug']).'\'');
		if (!isset($realmrow['id'])) break;

		$debugtext = $cloudrow['realmset'].' '.$cloudrow['realmslug'].' <'.$cloudrow['name'].'> '.str_repeat(' ',25-mb_strlen($cloudrow['name'],'UTF-8'));

		$json = json_decode($cloudrow['data'],true,12);
		if ((json_last_error() != JSON_ERROR_NONE) || (!isset($json['name']))) {
			$guildrow = get_single_row('select id, members from tblGuild where realmid=\''.sql_esc($realmrow['id']).'\' and name=\''.sql_esc($cloudrow['name']).'\'');
			if (!isset($guildrow['id'])) break;
			/*
			$url = 'http://'.strtolower($cloudrow['realmset']).'.battle.net/api/wow/guild/'.rawurlencode($cloudrow['realmslug']).'/'.rawurlencode($cloudrow['name']).'?fields=members';
			echo "\n".Date('H:i:s')." Pulling guild roster: $url";
			$dodebug = false;
			$jsontxt = get_url_old($url);
			$dodebug = true;
			$goodjson = ($jsontxt != '') && (!(is_null($json = json_decode($jsontxt,true,12))));
			if (!$goodjson) {
				if (((preg_match('/\b\d\d\d\b/',$prevheader,$res) > 0)?$res[0]:'500') == '404') {
					echo " (Not Found)";
					run_sql('update tblCharacter set guildid=null where realmid=\''.$realmrow['id'].'\' and guildid='.$guildrow['id']);
					run_sql('delete from tblGuild where id='.$guildrow['id']);
				} else {
					echo " (Error: ".$res[0].')';
			*/
			if ($guildrow['members'] == 0) {
				echo "\n".Date('H:i:s')." $debugtext (Not Found)";
				run_sql('update tblCharacter set guildid=null where realmid=\''.$realmrow['id'].'\' and guildid='.$guildrow['id']);
				run_sql('delete from tblGuild where id='.$guildrow['id']);
			} else {
				echo "\n".Date('H:i:s')." $debugtext (skipping)";
				run_sql('update tblGuild set members=0, scanned=\''.Date('Y-m-d H;i:s',strtotime('7 days ago')).'\' where id='.$guildrow['id']);
			}
			/*
				}
			}
			*/
			$goodjson = false;
		} else $goodjson = true;
		if ($goodjson) {
			$guildidjson = array('name' => isset($json['name'])?$json['name']:$cloudrow['name']);
			if (isset($json['lastModified'])) $guildidjson['lastModified'] = $json['lastModified'];
			if (isset($json['side'])) $guildidjson['side'] = $json['side'];
			//if (isset($json['emblem'])) $guildidjson['emblem'] = $json['emblem'];
			if (isset($json['members'])) $guildidjson['members'] = count($json['members']);

			$guildid = get_guild($guildidjson,$realmrow['id'],isset($json['name']));
			unset($guildidjson);

			if (isset($json['members']) && is_array($json['members'])) {
				run_sql('update tblCharacter set guildid=null where realmid='.$realmrow['id'].' and guildid='.$guildid);
				$crids = connrealm_lookup($realmrow['id']);
				foreach ($crids as $crid)
					run_sql('update tblCharacter set guildid=null where realmid='.$crid.' and guildid='.$guildid);
				
				$chc = 0; $chd = 0;
				foreach ($json['members'] as $mem) 
					if ($mem['character']) {
						$chc++;
						insert_character($mem['character'],isset($mem['character']['realm']) ? ($cloudrow['realmset'].'-'.$mem['character']['realm']) : $realmrow['id'],$guildid);
					}
				$debugtext .= " ".str_pad($chc,4," ",STR_PAD_LEFT).' seen';
			}
			echo "\n".Date('H:i:s')." $debugtext";
		}
		break;
	}
	$cloudrow = cloudpulldata('guild');
}
unset($cloudrow);

$hosts = array_keys($urlsockets);
for ($x = 0; $x < count($hosts); $x++) close_url_socket($hosts[$x]);

echo "\n".Date('H:i:s')." Finding parsed characters.";

$cloudrow = cloudpulldata('character');
while (isset($cloudrow['name'])) {
	while (1==1) {
		$realmrow = get_single_row('select id from tblRealm where realmset=\''.sql_esc($cloudrow['realmset']).'\' and slug=\''.sql_esc($cloudrow['realmslug']).'\'');
		if (!isset($realmrow['id'])) break;

		$json = json_decode($cloudrow['data'],true,12);
		if ((json_last_error() != JSON_ERROR_NONE)  || (!isset($json['name']))) {
			run_sql('delete from tblCharacter where realmid=\''.sql_esc($realmrow['id']).'\' and name=\''.sql_esc($cloudrow['name']).'\' and (ifnull(level,0) >= 10 or level=0)');
			if (mysql_affected_rows() == 0) run_sql('update tblCharacter set level=0,scanned=now() where realmid=\''.sql_esc($realmrow['id']).'\' and name=\''.sql_esc($cloudrow['name']).'\'');
		} else {
			$guildid = (isset($json['guild']))?get_guild($json['guild'],isset($json['guild']['realm']) ? ($cloudrow['realmset'].'-'.$json['guild']['realm']) : $realmrow['id']):'0';
			insert_character($json,$realmrow['id'],$guildid);
		}
		break;
	}
	$cloudrow = cloudpulldata('character');
}
unset($cloudrow);

echo "\n".Date('H:i:s')." Finding auction census characters.";

$dodebug = false;
$fd = get_url_old("http://192.168.137.238/census/getauctioncensus.php");
$dodebug = true;
while ($fd != '') {
	$names = explode("\n",$fd);
	unset($fd);
	$fn = array_shift($names);	
	echo "\n".Date('H:i:s')." Realm $fn has ".count($names)." names";
	$names = array_unique($names, SORT_STRING);
	echo " (".count($names)." unique)";
	unset($realmrow);
	if (preg_match('/^(US|EU)_([\w\W]+)$/i',$fn,$res) > 0) 
		$realmrow = get_single_row('select id from tblRealm where realmset=\''.sql_esc($res[1]).'\' and slug=\''.sql_esc($res[2]).'\'');
	if (isset($realmrow['id'])) {
		if (count($names) > 0) {
			$sql = ''; $x = 0;
			sort($names); // sort is there just to re-key the array
			//foreach ($names as $name) {
			for ($nx = 0; $nx < count($names); $nx++) { $name = $names[$nx];
				if (($name != '') && ($name != '???')) {
					$x++;
					$sql .= ($sql != ''?',':'insert ignore into tblCharacter (name, realmid) values ').'(\''.sql_esc($name).'\','.$realmrow['id'].')';
					if ($x % 250 == 0) {
						run_sql($sql);
						$sql = '';
					}
				}
			}
			if ($sql != '') run_sql($sql);
			unset($sql);
		}
	}
	$dodebug = false;
	$fd = get_url_old("http://192.168.137.238/census/getauctioncensus.php");
	$dodebug = true;
}

$hosts = array_keys($urlsockets);
for ($x = 0; $x < count($hosts); $x++) close_url_socket($hosts[$x]);

	
/*
$auctioncensuspath = '/home/erorus/public_html/wowcensus/auctioncensus/';
if (is_dir($auctioncensuspath) && ($aucdir = opendir($auctioncensuspath))) {
	while (($fn = readdir($aucdir)) !== false) {
		unset($realmrow);
		if (preg_match('/^(US|EU)_([\w\W]+)$/i',$fn,$res) > 0) 
			$realmrow = get_single_row('select id from tblRealm where realmset=\''.sql_esc($res[1]).'\' and slug=\''.sql_esc($res[2]).'\'');
		if (isset($realmrow['id'])) {
			$tfn = tempnam('/tmp','censuscloud');
			if (rename($auctioncensuspath.$fn,$tfn)) {
				$names = array_unique(file($tfn, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES), SORT_STRING);
				if (count($names) > 0) {
					$sql = ''; $x = 0;
					foreach ($names as $name) {
						$x++;
						$sql .= ($sql != ''?',':'insert ignore into tblCharacter (name, realmid) values ').'(\''.sql_esc($name).'\','.$realmrow['id'].')';
						if ($x % 250 == 0) {
							run_sql($sql);
							$sql = '';
						}
					}
					if ($sql != '') run_sql($sql);
					unset($sql);
				}
			}
			unlink($tfn);
		}
	}
	closedir($aucdir);
}
*/

while (cloudinsertresource('','','','')) {
	echo "\n".Date('H:i:s')." Requesting 100 new/dated guilds.";
	$sql = 'select r.realmset, r.slug, g.name from tblRealm r, tblGuild g where r.id=g.realmid and ifnull(g.scanned,\'2001-01-01\') < timestampadd(day,-15,now()) ';
	$sql .= excluderealmset().' and 0 = (select count(*) from tblCloud cl where cl.realmset=r.realmset and cl.resource=\'guild\' and cl.realmslug=r.slug and cl.name=g.name) limit 100';
	$rst = get_rst($sql);
	$inserts = 0;
	while ($row = next_row($rst)) {
		if (!cloudinsertresource($row['realmset'],'guild',$row['slug'],$row['name'])) break;
		$inserts++;
		//run_sql('update tblGuild set scanned=now() where id=\''.sql_esc($row['guildid']).'\'');
	}
	if ($inserts == 0) break;
}

while (cloudinsertresource('','','','')) {
	echo "\n".Date('H:i:s')." Requesting 100 new/dated characters from ";
	$row = get_single_row('select id, name, realmset from tblRealm where 1=1 '.excluderealmset().' order by charupdates asc limit 1');
	echo $row['realmset'].' '.$row['name'];
	run_sql('update tblRealm set charupdates=now() where id=\''.sql_esc($row['id']).'\'');

	// delete unguilded characters we've seen before
	run_sql('delete from tblCharacter where scanned < timestampadd(day,-14,now()) and ifnull(guildid,0)=0 and realmid=\''.sql_esc($row['id']).'\'');
	
	$sql = 'select r.realmset, r.slug, g.name ';
	$sql .= ' from tblRealm r, tblCharacter g ';
	$sql .= ' where r.id=g.realmid and ifnull(g.scanned,\'2001-01-01\') < timestampadd(day,if(ifnull(g.guildid,0)=0,-30,-20),now()) ';
	$sql .= excluderealmset().' and r.id=\''.sql_esc($row['id']).'\' ';
	$sql .= ' and 0=(select count(*) from tblCloud cl where cl.realmset=r.realmset and cl.resource=\'character\' and cl.realmslug=r.slug and cl.name=g.name) ';
	$sql .= ' order by ifnull(g.scanned,\'2001-01-01\') asc limit 100';
	$rst = get_rst($sql);
	$inserts = 0;
	while ($row = next_row($rst)) {
		if (!cloudinsertresource($row['realmset'],'character',$row['slug'],$row['name'])) break;
		$inserts++;
		//run_sql('update tblCharacter set scanned=now() where realmid=\''.sql_esc($row['realmid']).'\' and name=\''.sql_esc($row['name']).'\'');
	}
	if ($inserts == 0) break;
}

echo "\n".Date('H:i:s')." Done.";

cleanup();

function excluderealmset() {
	static $tr = '?';
	if ($tr != '?') return $tr;
	$tr = '';
	$sets = array();
	if (get_url_old('https://eu.battle.net/api/wow/realm/status?realms=aegwynn') == '') $sets[] = "'EU'";
	if (get_url_old('https://us.battle.net/api/wow/realm/status?realms=aegwynn') == '') $sets[] = "'US'";
	if (count($sets) > 0) {
		echo "\n".Date('H:i:s')." Realmsets down: ".implode(',',$sets);
		$tr = ' and realmset not in ('.implode(',',$sets).') ';
		run_sql('delete from tblCloud where realmset in ('.implode(',',$sets).') and verified=0');
	}
	return $tr;
}

?>
