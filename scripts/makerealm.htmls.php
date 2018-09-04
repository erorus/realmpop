<?php

$startTime = time();

require_once 'incl/incl.php';
require_once 'incl/constants.php';

$htmlheader = <<<PHPEOF
<!DOCTYPE html>
<html>
<head>
<title>PRETTYREALM Realm Pop</title>
<link rel="shortcut icon" href="/favicon.ico"></link>
<link href='https://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'></link>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css" type="text/css"></link>
<style type="text/css">
.nobr {white-space: nowrap}
#tblCharts td {padding-bottom: 25px}
.ttl {text-align: center; font-size: larger}
.tablesorter {width: 100%; border: 1px solid #EEE; border-spacing: 0; border-collapse: collapse}
.tablesorter th {background-color: #DEDEDE; padding: 2px; border: 1px solid #DEDEDE; cursor: pointer; padding-right: 20px; background-image: url(images/sort.bg.gif); background-repeat: no-repeat; background-position: center right}
.tablesorter th.headerSortUp { background-image: url(images/sort.asc.gif); }
.tablesorter th.headerSortDown { background-image: url(images/sort.desc.gif); }
.tablesorter td {border: 1px solid #EEE; padding: 2px}
a {color: #000099}
</style>
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
</head>
<body style="margin: 0; font-family: 'Abel', sans-serif">
<div style="margin: 0 auto; width: 900px; vertical-align: bottom; padding-top: 10px; padding-bottom: 10px; position: relative">
<h1>PRETTYREALM <span class="nobr">Realm Pop</span></h1>
<div id="divRealmBio" style="margin-bottom: 1.5em">REALMBIO</div>
<div align="center">
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1018837251546750"
     data-ad-slot="6730965026"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<br>
PHPEOF;

// <div style="position: absolute; left: -50px; height:1000px; border-right: 1px dashed #999"></div>


$htmlrealm = <<<PHPEOF
Click a pie wedge to narrow down, click again to branch out. Find navigation at the bottom of the page.
<div style="position: relative">
<div id="divLoading" style="position: fixed; width: 900px; text-align: center; margin-top: 4em; font-size: larger">
	<div id="divLoadingText">Loading code..</div>
	<div id="divLoadingBar" style="width: 450px; margin-left: 225px"></div>
</div>
<div id="divAllCharts" style="margin-top: 4em; visibility: hidden">
<div class="ttl" style="position: absolute; left: 350px; width: 200px; top: 350px; text-align: center; z-index: 2" id="divResults"></div>
<table border="0" cellspacing="0" cellpadding="0" id="tblCharts" style="width: 900px">
	<tr>
	<td width="450">
		<div id="divTtlFaction" class="ttl">Faction</div>
		<div id="chtByFaction" style="width: 450px; height: 350px"></div>
	</td>
	<td width="450">
		<div id="divTtlGender" class="ttl">Gender</div>
		<div id="chtByGender" style="width: 450px; height: 350px"></div>
	</td>
	</tr><tr>
	<td width="450">
		<div id="divTtlRace" class="ttl">Race</div>
		<div id="chtByRace" style="width: 450px; height: 350px"></div>
	</td>
	<td width="450">
		<div id="divTtlClass" class="ttl">Class</div>
		<div id="chtByClass" style="width: 450px; height: 350px"></div>
	</td>
	</tr><tr>
	<td width="900" colspan="2">
		<div id="divTtlLevel" class="ttl">Level</div>
		<div style="position: relative; height: 1.5em">
			<div style="position: absolute; width: 200px; text-align: right">0</div>
			<div id="divLvlSlider" style="width: 450px; position: absolute; left: 225px"></div>
			<div style="position: absolute; width: 220px; left: 700px">$MAX_LEVEL</div>

		</div>
	</td>
</tr>
</table>
</div>
</div>
<a href="REALMSET.html" class="ttl">Back to All Realms</a>
PHPEOF;

$htmlrealmset = <<<PHPEOF
Click a pie wedge to narrow down, click again to branch out. Find lists and navigation at the bottom of the page.
<div style="position: relative">
<div id="divLoading" style="position: fixed; width: 900px; text-align: center; margin-top: 4em; font-size: larger">
	<div id="divLoadingText">Loading code..</div>
	<div id="divLoadingBar" style="width: 450px; margin-left: 225px"></div>
</div>
<div id="divAllCharts" style="margin-top: 4em; visibility: hidden">
<div class="ttl" style="position: absolute; left: 350px; width: 200px; top: 500px; text-align: center; z-index: 2" id="divResults"></div>
<table border="0" cellspacing="0" cellpadding="0" id="tblCharts" style="width: 900px">
	<tr>
	<td width="450">
		<div id="divTtlRP" class="ttl">RP</div>
		<div id="chtByRP" style="width: 450px; height: 175px"></div>
	</td>
	<td width="450">
		<div id="divTtlRegion" class="ttl">Region</div>
		<div id="chtByRegion" style="width: 450px; height: 175px"></div>
	</td>
	</tr><tr>
	<td width="450">
		<div id="divTtlFaction" class="ttl">Faction</div>
		<div id="chtByFaction" style="width: 450px; height: 250px"></div>
	</td>
	<td width="450">
		<div id="divTtlGender" class="ttl">Gender</div>
		<div id="chtByGender" style="width: 450px; height: 250px"></div>
	</td>
	</tr><tr>
	<td width="450">
		<div id="divTtlRace" class="ttl">Race</div>
		<div id="chtByRace" style="width: 450px; height: 350px"></div>
	</td>
	<td width="450">
		<div id="divTtlClass" class="ttl">Class</div>
		<div id="chtByClass" style="width: 450px; height: 350px"></div>
	</td>
	</tr><tr>
	<td width="900" colspan="2">
		<div id="divTtlLevel" class="ttl">Level</div>
		<div style="position: relative; height: 1.5em">
			<div style="position: absolute; width: 200px; text-align: right">0</div>
			<div id="divLvlSlider" style="width: 450px; position: absolute; left: 225px"></div>
			<div style="position: absolute; width: 220px; left: 700px">$MAX_LEVEL</div>
		</div>
	</td>
</tr>
</table>
<div id="chtList" style="width: 900px; height: 450px; overflow-y: auto"></div>
</div>
</div>
<br>
<a href="./" class="ttl">Back to Front Page</a>
PHPEOF;

$htmlend = <<<PHPEOF
</div>
<br>
<div align="center">
<ins class="adsbygoogle"
     style="display:inline-block;width:728px;height:90px"
     data-ad-client="ca-pub-1018837251546750"
     data-ad-slot="6591093962"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
</div>

<div align="center" style="font-size: 9px; margin-top: 20px; font-family: sans-serif">Realm Pop uses names and images from World of Warcraft, and data proprietary to Blizzard Entertainment, Inc.<br>World of Warcraft, Warcraft and Blizzard Entertainment are trademarks or registered trademarks of Blizzard Entertainment, Inc. in the U.S. and/or other countries.</div>

<script type="text/javascript">
	var __realmset="REALMSET";
	var __slug="SLUG";
	var __jsonsize="JSONSIZE";
	var __lookups=LOOKUPJSON;
</script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
<script type="text/javascript" src="jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="realmcharts.js"></script>
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-28451741-1']);
  _gaq.push(['_setDomainName', 'realmpop.com']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
</body></html>
PHPEOF;

$siteMap = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url><loc>https://realmpop.com/</loc><priority>1.0</priority><changefreq>monthly</changefreq></url>

EOF;

$publicDir = realpath(__DIR__.'/../public');

RunMeNTimes(1);
CatchKill();

if (!DBConnect())
    DebugMessage('Cannot connect to db!', E_USER_ERROR);

$stmt = $db->prepare('select distinct region from tblRealm');
$stmt->execute();
$result = $stmt->get_result();
$regions = DBMapArray($result, null);
$stmt->close();

foreach($regions as $region) {
    if (CatchKill())
        break;

	file_put_contents($publicDir.'/'.strtolower($region).'.html', cookhtml($region));
    $siteMap .= '<url><loc>https://realmpop.com/'.strtolower($region).'.html</loc><priority>0.7</priority><changefreq>weekly</changefreq></url>'."\n";

    $stmt = $db->prepare('select distinct slug from tblRealm where region = ?');
    $stmt->bind_param('s',$region);
    $stmt->execute();
    $result = $stmt->get_result();
    $slugs = DBMapArray($result, null);
    $stmt->close();

	foreach($slugs as $slug) {
        if (CatchKill())
            break;

        file_put_contents($publicDir.'/'.strtolower($region).'-'.$slug.'.html', cookhtml($region,$slug));
        $siteMap .= '<url><loc>https://realmpop.com/'.strtolower($region).'-'.$slug.'.html</loc><priority>0.5</priority><changefreq>weekly</changefreq></url>'."\n";
    }
}

$siteMap .= "</urlset>";
file_put_contents($publicDir.'/sitemap.xml', $siteMap);

DebugMessage('Done! Started '.TimeDiff($startTime));


// PRETTYREALM SLUG REALMBIO REALMSET JSONSIZE LOOKUPJSON

function cookhtml($realmset, $slug='') {
	global $publicDir,$db;
    global $htmlheader,$htmlrealm,$htmlend,$htmlrealmset;

	$jsonfn = $publicDir.'/'.strtolower($realmset).(($slug != '')?'-':'').$slug.'.json';
	$jsonsize = file_exists($jsonfn)?filesize($jsonfn):0;

	if ($slug != '') {
		$html = $htmlheader.$htmlrealm.$htmlend;

		$realmset = substr(strtoupper($realmset),0,2);
		$realmslug = substr($slug, 0, 45);

        $sql = <<<EOF
select r.*, if(r.region='US',if(locale='pt_BR', 'Brazil', if(locale='es_MX', 'Latin America', if(timezone like 'Australia/%', 'Oceanic', 'United States'))),
case locale
when 'de_DE' then 'German'
when 'en_GB' then 'English'
when 'es_ES' then 'Spanish'
when 'fr_FR' then 'French'
when 'pt_BR' then 'Portuguese'
when 'it_IT' then 'Italian'
when 'ru_RU' then 'Russian'
else 'Unknown' end) regionname from tblRealm r where region = ? and slug = ?
EOF;

        $stmt = $db->prepare($sql);
        $stmt->bind_param('ss',$realmset,$slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = DBMapArray($result, null);
        $stmt->close();

        if (count($rows) == 0) {
            return '';
        }

        $row = array_pop($rows);

		if ($realmslug != $row['slug']) 
			$realmslug = $row['slug'];
		
		$realmbio = $row['region'].' '.$row['name'].' is a '.(($row['rp']=='1')?'RP ':'Normal ').' realm';
		if (nvl($row['regionname'],'') != '') $realmbio .= ' in the '.$row['regionname'].' region';
		if (nvl($row['timezone'],'') != '') $realmbio .= ' in the '.$row['timezone'].' time zone';
		$realmbio .= '.';
		//if (nvl($row['forumid'],'') != '') $realmbio .= ' <a href="http://'.strtolower($row['realmset']).'.battle.net/wow/
		if (nvl($row['population'],'') != '') $realmbio .= ' Blizzard calls it a '.$row['population'].' population realm.';
	
		//header('Content-type: text/html; charset=utf8');

        $sql = <<<EOF
select
concat_ws('-', lower(region), slug) as connslug,
concat_ws(' ', region, name) as connname
from tblRealm
where house = ? and id != ?
EOF;

        $stmt = $db->prepare($sql);
        $stmt->bind_param('ii',$row['house'],$row['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $connectedRows = DBMapArray($result, null);
        $stmt->close();

		$connnames = '';
		foreach ($connectedRows as $connrow) {
			$jsonsize += file_exists($publicDir.'/' . $connrow['connslug'] . '.json') ? filesize($publicDir.'/' . $connrow['connslug'] . '.json') : 0;
			$connnames .= (($connnames == '') ? '' : ', ') . $connrow['connname'];
		}
		if ($connnames != '')
			$realmbio .= ' These statistics include its <a href="http://wowpedia.org/Connected_Realm">Connected Realm' . ((strpos($connnames, ',') !== false) ? 's' : '') . '</a>: ' . $connnames . '.';

        $html = str_replace('LOOKUPJSON', buildLookupJson(),
                str_replace('JSONSIZE', $jsonsize,
                str_replace('REALMSET', strtolower($realmset),
                str_replace('REALMBIO', $realmbio,
                str_replace('PRETTYREALM', $realmset . " " . $row['name'],
                str_replace('SLUG', $row['slug'], $html))))));

		return $html;
	} else {
		//$realmset = substr(strtoupper($_GET['realmset']),0,2);
		$realmset = strtoupper($realmset);
		switch ($realmset) {
			case 'US':
			case 'EU':
				break;
			default:
				return '';
		}
		$html = $htmlheader.$htmlrealmset.$htmlend;

		$realmbio = '';

		$html = str_replace('LOOKUPJSON', buildLookupJson(),
                str_replace('JSONSIZE', $jsonsize,
                str_replace('REALMSET', strtolower($realmset),
                str_replace('REALMBIO', $realmbio,
                str_replace('PRETTYREALM', $realmset,
                str_replace('SLUG', '', $html))))));

		return $html;
	}
}

function buildLookupJson() {
    global $RACES, $RACE_TO_SIDE, $MAX_LEVEL;

    $lookup = [
        'maxLevel' => $MAX_LEVEL,
        'factions' => [
            'Unknown' => ['', 'Unknown'],
        ],
        'colorSet' => [
            'Death Knight' => '#C41F3B',
            'Demon Hunter' => '#A330C9',
            'Druid'        => '#FF7D0A',
            'Hunter'       => '#ABD473',
            'Mage'         => '#69CCF0',
            'Monk'         => '#00FF96',
            'Paladin'      => '#F58CBA',
            'Priest'       => '#FFFFFF',
            'Rogue'        => '#FFF569',
            'Shaman'       => '#0070DE',
            'Warlock'      => '#9482C9',
            'Warrior'      => '#C79C6E',

            'Male'   => '#338833',
            'Female' => '#883388',

            'Horde'    => '#883333',
            'Alliance' => '#223355',

            'Blood Elf'    => '#cc3333',
            'Draenei'      => '#9a73b6',
            'Dwarf'        => '#6dc161',
            'Undead'       => '#335566',
            'Gnome'        => '#f19759',
            'Goblin'       => '#aacc66',
            'Human'        => '#3377BB',
            'Night Elf'    => '#492d7a',
            'Orc'          => '#3b4006',
            'Tauren'       => '#775005',
            'Troll'        => '#bb6633',
            'Worgen'       => '#755259',
            'Pandaren (A)' => '#444466',
            'Pandaren (H)' => '#664444',

            'Nightborne'          => '#321f72',
            'Highmountain Tauren' => '#593c04',
            'Zandalari Troll'     => '#195953',
            'Mag\'har Orc'        => '#4a2b00',

            'Void Elf'            => '#3333cc',
            'Lightforged Draenei' => '#ffff33',
            'Kul Tiran'           => '#034d73',
            'Dark Iron Dwarf'     => '#333333',


            'Normal' => '#338833',
            'RP'     => '#883388'
        ],
    ];

    foreach ($RACE_TO_SIDE as $raceId => $sideName) {
        $lookup['factions'][$sideName][] = $RACES[$raceId];
    }

    /*
     * {
        'Death Knight':'#C41F3B','Demon Hunter':'#A330C9','Druid':'#FF7D0A','Hunter':'#ABD473','Mage':'#69CCF0','Monk':'#00FF96',
        'Paladin':'#F58CBA','Priest':'#FFFFFF','Rogue':'#FFF569','Shaman':'#0070DE','Warlock':'#9482C9','Warrior':'#C79C6E',
        'Male':'#338833','Female':'#883388','Horde':'#883333','Alliance':'#223355',
        'Blood Elf':'#cc3333','Draenei':'#9a73b6','Dwarf':'#6dc161','Undead':'#335566','Gnome':'#f19759','Goblin':'#aacc66',
        'Human':'#3377BB','Night Elf':'#492d7a','Orc':'#3b4006','Tauren':'#775005','Troll':'#bb6633','Worgen':'#755259',
        'PandarenA':'#444466','PandarenH':'#664444',
        'Normal':'#338833','RP':'#883388'
    }
     */
    
    return json_encode($lookup);
}
