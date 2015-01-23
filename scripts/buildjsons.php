<?php

$startTime = time();

require_once('incl/incl.php');
require_once('incl/heartbeat.incl.php');

$publicDir = realpath(__DIR__.'/../public');

ini_set('memory_limit','320M');

RunMeNTimes(1);
CatchKill();

if (!DBConnect())
    DebugMessage('Cannot connect to db!', E_USER_ERROR);

heartbeat();
if ($caughtKill)
    exit;

$sql = <<<EOF
select
concat_ws('-',lower(r1.region), r1.slug) as lookup,
concat_ws('-',lower(r2.region), r2.slug) as connto
from tblRealm r1
join tblRealm r2 on r1.house = r2.house and r1.id != r2.id
EOF;

$stmt = $db->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$rows = DBMapArray($result, null);
$stmt->close();

$cr = array();
foreach ($rows as $row) 
	$cr[$row['lookup']][] = $row['connto'];
file_put_contents($publicDir.'/connected-realms.json',json_encode($cr));

heartbeat();
if ($caughtKill)
    exit;

if (isset($argv[1])) {
    $stmt = $db->prepare('select distinct region from tblRealm where region = ?');
    $stmt->bind_param('s',$argv[1]);
} else {
    $stmt = $db->prepare('select distinct region from tblRealm');
}
$stmt->execute();
$result = $stmt->get_result();
$regions = DBMapArray($result, null);
$stmt->close();

heartbeat();
if ($caughtKill)
    exit;

foreach ($regions as $region) {
    heartbeat();
    if ($caughtKill)
        break;

	$regionStats = array('realms' => array(), 'demographics' => array());
		
	$sql = <<<EOF
    select r.*,
    case r.pvp when 0 then 'PvE' when 1 then 'PvP' else 'Unknown' end pvpname,
    case r.rp when 0 then 'Normal' when 1 then 'RP' else 'Unknown' end rpname,
	if(region='US',
if(locale='pt_BR', 'Brazil', if(locale='es_MX', 'Latin America', if(timezone like 'Australia/%', 'Oceanic', 'United States'))),
case locale
when 'de_DE' then 'German'
when 'en_GB' then 'English'
when 'es_ES' then 'Spanish'
when 'fr_FR' then 'French'
when 'pt_BR' then 'Portuguese'
when 'it_IT' then 'Italian'
when 'ru_RU' then 'Russian'
else 'Unknown' end) regionname,
	ifnull(timezone, 'Unknown') timezonename
	from tblRealm r
	where r.region = ? 
	order by 1
EOF;

    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $region);
    $stmt->execute();
    $result = $stmt->get_result();
    $realms = DBMapArray($result);
    $stmt->close();

    foreach ($realms as $realmId => $realmRow) {
        heartbeat();
        if ($caughtKill)
            break;

		$fn = strtolower($region).'-'.$realmRow['slug'];
        DebugMessage("Making $fn ".round(memory_get_usage()/1048576)."MB");

		$regionStats['realms'][$realmRow['slug']] = array('name'=>$realmRow['name'],'counts'=>array('Alliance'=>0,'Horde'=>0,'Unknown'=>0,'Neutral'=>0),'stats'=>array('pvp'=>$realmRow['pvpname'],'rp'=>$realmRow['rpname'],'region'=>$realmRow['regionname'],'timezone'=>$realmRow['timezonename']));

		if (!isset($regionStats['demographics'][$realmRow['pvpname']])) $regionStats['demographics'][$realmRow['pvpname']] = array();
		if (!isset($regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']])) $regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']] = array();
		if (!isset($regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']])) $regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']] = array();
		if (!isset($regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']])) $regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']] = array();

        $result = array('characters'=>array(),'guilds'=>array(),'meta'=>array());

        $sql = <<<EOF
select c.name, 
ifnull(c.race,'Unknown') race, 
ifnull(c.class,'Unknown') class,
ifnull(c.gender,'Unknown') gender,
ifnull(c.level,0) level,
ifnull(s.side,'Unknown') side 
from tblCharacter c
left join tblSide s on c.race=s.race
where c.realm = ?
and c.level is not null
order by cast(c.gender as char) desc, cast(c.class as char), s.side, cast(c.race as char), level
EOF;
        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $realmId);
        $stmt->execute();
        $rowName = $rowRace = $rowClass = $rowGender = $rowLevel = $rowSide = '';
        $stmt->bind_result($rowName, $rowRace, $rowClass, $rowGender, $rowLevel, $rowSide);
        $rowCount = 0;
		while ($stmt->fetch()) {
            heartbeat();
            if (++$rowCount % 5000 == 0) {
                echo "\r".str_pad($rowCount, 7, ' ', STR_PAD_LEFT).' '.str_pad(round(memory_get_usage()/1048576), 4, ' ', STR_PAD_LEFT)."MB";
            }
			//if (substr($row['race'],0,8) == 'Pandaren') $row['race'] = 'Pandaren';
			if (!isset($result['characters'][$rowGender])) $result['characters'][$rowGender] = array();
			if (!isset($result['characters'][$rowGender][$rowClass])) $result['characters'][$rowGender][$rowClass] = array();
			if (!isset($result['characters'][$rowGender][$rowClass][$rowRace])) $result['characters'][$rowGender][$rowClass][$rowRace] = array();
			if (!isset($result['characters'][$rowGender][$rowClass][$rowRace][$rowLevel])) $result['characters'][$rowGender][$rowClass][$rowRace][$rowLevel] = array();
			$result['characters'][$rowGender][$rowClass][$rowRace][$rowLevel][] = $rowName; //array('character' => $rowName, 'guild' => $row['guildname']);

			if (!isset($regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender])) $regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender] = array();
			if (!isset($regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender][$rowClass])) $regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender][$rowClass] = array();
			if (!isset($regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender][$rowClass][$rowRace])) $regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender][$rowClass][$rowRace] = array();
			if (!isset($regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender][$rowClass][$rowRace][$rowLevel])) $regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender][$rowClass][$rowRace][$rowLevel] = 0;
			$regionStats['demographics'][$realmRow['pvpname']][$realmRow['rpname']][$realmRow['regionname']][$realmRow['timezonename']][$rowGender][$rowClass][$rowRace][$rowLevel]++;
			
			$regionStats['realms'][$realmRow['slug']]['counts'][$rowSide]++;
		}
        $stmt->close();

        heartbeat();
        if ($caughtKill)
            break;

        echo "\r".str_repeat(' ', 20)."\r";
        DebugMessage("Character rows finished $fn ".round(memory_get_usage()/1048576)."MB");

        $stmt = $db->prepare('select name, side, members from tblGuild where realm=? order by members desc, name asc');
        $stmt->bind_param('i', $realmId);
        $stmt->execute();
        $rst = $stmt->get_result();
		while ($row = $rst->fetch_assoc()) {
            heartbeat();
			if (!isset($result['guilds'][$row['side']])) $result['guilds'][$row['side']] = array();
			$result['guilds'][$row['side']][] = array('guild'=>$row['name'],'membercount'=>intval($row['members']));
		}
        $rst->close();
        $stmt->close();

		$result['meta']['slug']=$realmRow['slug'];
		$result['meta']['realmset']=$region;
		file_put_contents($publicDir.'/'.$fn.'.json',json_encode($result));
		unset($result);
	}

    if (!$caughtKill)
	    file_put_contents($publicDir.'/'.strtolower($region).'.json',json_encode($regionStats));
}

DebugMessage('Done! Started '.TimeDiff($startTime));
