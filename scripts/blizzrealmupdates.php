<?php

require_once('incl.php');
do_connect();

$rsets = array('US','EU');
foreach ($rsets as $rset) {
	$statjson = get_url_old('http://'.strtolower($rset).'.battle.net/api/wow/realm/status');
	if ($statjson != '') {
		$json = json_decode($statjson);
		echo ''.count($json->realms)." realms seen.\n";
		$hashes = array();
		foreach ($json->realms as $realm) {
			echo "$rset $realm->name is ".($realm->status?'up':'down')."\n";
			$r = get_single_row('select max(id)+1 newid from tblRealm');
			$sql = 'insert into tblRealm (id, name, realmset, battlegroup, pvp, rp, slug, population) values (\''.sql_esc($r['newid']).'\', \''.sql_esc($realm->name).'\', \''.sql_esc($rset).'\',\''.sql_esc($realm->battlegroup).'\',';
			$sql .= ((stripos($realm->type,'pvp') === false)?'0':'1').',';
			$sql .= ((stripos($realm->type,'rp') === false)?'0':'1').',';
			$sql .= '\''.sql_esc($realm->slug).'\', \''.sql_esc($realm->population).'\') on duplicate key update pvp=values(pvp), rp=values(rp), slug=values(slug), population=values(population), battlegroup=values(battlegroup)';
			run_sql($sql);
			$aucjson = get_url_old('http://' . strtolower($rset) . '.battle.net/api/wow/auction/data/' . $realm->slug);
			if ($aucjson != '') {
				$aucfiles = json_decode($aucjson);
				if (json_last_error() == JSON_ERROR_NONE) {
					$url = $aucfiles->files[0]->url;
					if (preg_match('/\/([0-9a-f]{32})\/auctions\.json$/', $url, $res))
						$hashes[$res[1]][] = $realm->slug;
				}
			}
		}
		foreach ($hashes as $hash => $a) {
			if (count($a) == 1)
				continue;
			$sql = '';
			foreach ($a as $slug) 
				$sql .= (($sql == '') ? '' : ',') . '\'' . sql_esc($slug) . '\'';
			$sql = 'replace into tblConnectedRealm (lookup, connto) (SELECT r1.id, r2.id FROM tblRealm r1, tblRealm r2 WHERE r1.realmset=\''.sql_esc($rset).'\' and r1.slug in ('.$sql.') and r2.realmset=r1.realmset and r2.slug in ('.$sql.') and r1.id != r2.id)';
			run_sql($sql);
		}
	} else {
	    echo "Status JSON empty for realmset $rset\n";
	}
}

cleanup();
?>
