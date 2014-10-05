<?php

require_once('incl.php');


/*
2 pvp
2 rp
4 region
5 timezone
2 gender
10 class
12 race
85 level
*/

do_connect();
$rst = get_rst('select concat_ws(\'-\',lower(r1.realmset), r1.slug) as lookup, concat_ws(\'-\',lower(r2.realmset), r2.slug) as connto from tblConnectedRealm c join tblRealm r1 on c.lookup = r1.id join tblRealm r2 on c.connto = r2.id');
$cr = array();
while ($row = next_row($rst)) 
	$cr[$row['lookup']][] = $row['connto'];
file_put_contents('public/connected-realms.json',json_encode($cr));

$realmsetrst = get_rst('select distinct realmset from tblRealm '.(isset($argv[1])?('where realmset=\''.sql_esc($argv[1]).'\''):''));
while ($realmsetrow = next_row($realmsetrst)) {
	$realmsetstats = array('realms' => array(), 'demographics' => array());
		
	$sql = 'select r.*, ';
	$sql .= ' case r.pvp when 0 then \'PvE\' when 1 then \'PvP\' else \'Unknown\' end pvpname, ';
	$sql .= ' case r.rp when 0 then \'Normal\' when 1 then \'RP\' else \'Unknown\' end rpname, ';
	$sql .= ' ifnull(region, \'Unknown\') regionname, ';
	$sql .= ' ifnull(timezone, \'Unknown\') timezonename ';
	$sql .= ' from tblRealm r, (SELECT distinct realmid FROM tblGuild) g where r.id=g.realmid and r.realmset=\''.sql_esc($realmsetrow['realmset']).'\' order by 1';
	$realmrst = get_rst($sql);
	while ($realmrow = next_row($realmrst)) { 
		$fn = strtolower($realmrow['realmset']).'-'.$realmrow['slug'];
		echo Date('Y-m-d H:i:s')." Making $fn\n";

		$realmsetstats['realms'][$realmrow['slug']] = array('name'=>$realmrow['name'],'counts'=>array('Alliance'=>0,'Horde'=>0,'Unknown'=>0,'Neutral'=>0),'stats'=>array('pvp'=>$realmrow['pvpname'],'rp'=>$realmrow['rpname'],'region'=>$realmrow['region'],'timezone'=>$realmrow['timezonename']));

		if (!isset($realmsetstats['demographics'][$realmrow['pvpname']])) $realmsetstats['demographics'][$realmrow['pvpname']] = array();
		if (!isset($realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']])) $realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']] = array();
		if (!isset($realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']])) $realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']] = array();
		if (!isset($realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']])) $realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']] = array();

		$rst = get_rst('select c.name, ifnull(c.race,\'Unknown\') race, ifnull(class,\'Unknown\') class, ifnull(gender,\'Unknown\') gender, ifnull(level,0) level, ifnull(s.side,\'Unknown\') side from (tblCharacter c left join tblSide s on c.race=s.race) where c.realmid=\''.sql_esc($realmrow['id']).'\' order by cast(gender as char) desc, cast(class as char), s.side, cast(c.race as char), level');
		$result = array('characters'=>array(),'guilds'=>array(),'meta'=>array());
		while ($row = next_row($rst)) {
			//if (substr($row['race'],0,8) == 'Pandaren') $row['race'] = 'Pandaren';
			if (!isset($result['characters'][$row['gender']])) $result['characters'][$row['gender']] = array();
			if (!isset($result['characters'][$row['gender']][$row['class']])) $result['characters'][$row['gender']][$row['class']] = array();
			if (!isset($result['characters'][$row['gender']][$row['class']][$row['race']])) $result['characters'][$row['gender']][$row['class']][$row['race']] = array();
			if (!isset($result['characters'][$row['gender']][$row['class']][$row['race']][$row['level']])) $result['characters'][$row['gender']][$row['class']][$row['race']][$row['level']] = array();
			$result['characters'][$row['gender']][$row['class']][$row['race']][$row['level']][] = $row['name']; //array('character' => $row['name'], 'guild' => $row['guildname']);

			if (!isset($realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']])) $realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']] = array();
			if (!isset($realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']][$row['class']])) $realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']][$row['class']] = array();
			if (!isset($realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']][$row['class']][$row['race']])) $realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']][$row['class']][$row['race']] = array();
			if (!isset($realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']][$row['class']][$row['race']][$row['level']])) $realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']][$row['class']][$row['race']][$row['level']] = 0;
			$realmsetstats['demographics'][$realmrow['pvpname']][$realmrow['rpname']][$realmrow['regionname']][$realmrow['timezonename']][$row['gender']][$row['class']][$row['race']][$row['level']]++;
			
			$realmsetstats['realms'][$realmrow['slug']]['counts'][$row['side']]++;
		}
		$rst = get_rst('select name, side, members from tblGuild where realmid=\''.sql_esc($realmrow['id']).'\' order by members desc, name asc');
		while ($row = next_row($rst)) {
			if (!isset($result['guilds'][$row['side']])) $result['guilds'][$row['side']] = array();
			$result['guilds'][$row['side']][] = array('guild'=>$row['name'],'membercount'=>intval($row['members']));
		}
		$result['meta']['slug']=$realmrow['slug'];
		$result['meta']['realmset']=$realmrow['realmset'];
		file_put_contents('public/'.$fn.'.json',json_encode($result));
		unset($result);
	}
	file_put_contents('public/'.strtolower($realmsetrow['realmset']).'.json',json_encode($realmsetstats));
}


echo Date('Y-m-d H:i:s')." Done!\n";


cleanup();
?>
