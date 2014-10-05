<?php

require_once('incl.php');

$htmlheader = <<<PHPEOF
<html>
<head>
<title>PRETTYREALM Realm Pop</title>
<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'></link>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css" type="text/css"></link>
<style type="text/css">
.nobr {white-space: nowrap}
#tblCharts {width: 900px}
#tblCharts td {padding-bottom: 25px}
.ttl {text-align: center; font-size: larger}
.google-visualization-table-table * {font-family: 'Abel', sans-serif}
.google-table-row {background-color: white}
.google-table-row-hover {background-color: #EEEEEE}
.google-table-cell {border: 1px solid #EEEEEE}
.google-table-header-row {background-color: white; font-size: larger; background-color: #EEEEEE}
#chtList div div, #chtGuildList div div {border-bottom: 1px solid #EEEEEE}
a {color: #000099}
</style>
</head>
<body style="margin: 0; font-family: 'Abel', sans-serif">
<div style="margin: 0 auto; width: 900px; vertical-align: bottom; padding-top: 10px; padding-bottom: 10px; position: relative">
<h1>PRETTYREALM <span class="nobr">Realm Pop</span></h1>
<div id="divRealmBio" style="margin-bottom: 1.5em">REALMBIO</div>
Click a pie wedge to narrow down, click again to branch out. Find lists and navigation at the bottom of the page.
PHPEOF;

// <div style="position: absolute; left: -50px; height:1000px; border-right: 1px dashed #999"></div>


$htmlrealm = <<<PHPEOF
<div style="position: relative">
<div id="divLoading" style="position: fixed; width: 900px; text-align: center; margin-top: 4em; font-size: larger">
	<div id="divLoadingText">Loading data..</div>
	<div id="divLoadingBar" style="width: 450px; margin-left: 225px"></div>
</div>
<div id="divAllCharts" style="margin-top: 4em; visibility: hidden">
<div class="ttl" style="position: absolute; left: 350px; width: 200px; top: 350px; text-align: center; z-index: 2" id="divResults"></div>
<table border="0" cellspacing="0" cellpadding="0" id="tblCharts">
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
			<div style="position: absolute; width: 220px; left: 700px">85</div>

		</div>
	</td>
</tr>
</table>
<table border="0" cellspacing="0" cellpadding="0" id="tblLists" style="margin-bottom: 25px">
<tr><td width="500">
	<div id="divTtlList" class="ttl">Characters</div>
	<div id="chtList" style="width: 500px; height: 450px"></div>
</td><td width="400">
	<div id="divTtlGuildList" class="ttl">Guilds</div>
	<div id="chtGuildList" style="width: 350px; margin-left: 50px; height: 450px"></div>
</td></tr>
</table>
</div>
</div>
<a href="REALMSET.html" class="ttl">Back to All Realms</a>
PHPEOF;

$htmlrealmset = <<<PHPEOF
<div style="position: relative">
<div id="divLoading" style="position: fixed; width: 900px; text-align: center; margin-top: 4em; font-size: larger">
	<div id="divLoadingText">Loading data..</div>
	<div id="divLoadingBar" style="width: 450px; margin-left: 225px"></div>
</div>
<div id="divAllCharts" style="margin-top: 4em; visibility: hidden">
<div class="ttl" style="position: absolute; left: 350px; width: 200px; top: 500px; text-align: center; z-index: 2" id="divResults"></div>
<table border="0" cellspacing="0" cellpadding="0" id="tblCharts">
	<tr>
	<td width="225">
		<div id="divTtlPvP" class="ttl">PvP</div>
		<div id="chtByPvP" style="width: 225px; height: 175px"></div>
	</td>
	<td width="225">
		<div id="divTtlRP" class="ttl">RP</div>
		<div id="chtByRP" style="width: 225px; height: 175px"></div>
	</td>
	<td width="225">
		<div id="divTtlRegion" class="ttl">Region</div>
		<div id="chtByRegion" style="width: 225px; height: 175px"></div>
	</td>
	<td width="225">
		<div id="divTtlTimezone" class="ttl">Time Zone</div>
		<div id="chtByTimezone" style="width: 225px; height: 175px"></div>
	</td>
	</tr><tr>
	<td width="450" colspan="2">
		<div id="divTtlFaction" class="ttl">Faction</div>
		<div id="chtByFaction" style="width: 450px; height: 250px"></div>
	</td>
	<td width="450" colspan="2">
		<div id="divTtlGender" class="ttl">Gender</div>
		<div id="chtByGender" style="width: 450px; height: 250px"></div>
	</td>
	</tr><tr>
	<td width="450" colspan="2">
		<div id="divTtlRace" class="ttl">Race</div>
		<div id="chtByRace" style="width: 450px; height: 350px"></div>
	</td>
	<td width="450" colspan="2">
		<div id="divTtlClass" class="ttl">Class</div>
		<div id="chtByClass" style="width: 450px; height: 350px"></div>
	</td>
	</tr><tr>
	<td width="900" colspan="4">
		<div id="divTtlLevel" class="ttl">Level</div>
		<div style="position: relative; height: 1.5em">
			<div style="position: absolute; width: 200px; text-align: right">0</div>
			<div id="divLvlSlider" style="width: 450px; position: absolute; left: 225px"></div>
			<div style="position: absolute; width: 220px; left: 700px">85</div>

		</div>
	</td>
</tr>
</table>
<div id="chtList" style="width: 900px; height: 450px"></div>
</div>
</div>
PHPEOF;

$htmlend = <<<PHPEOF
</div>
<script type="text/javascript">
	var __realmset="REALMSET";
	var __slug="SLUG";
</script>
<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript" src="realmcharts.min.js"></script>
</body></html>
PHPEOF;

// <script type="text/javascript" src="realmpop.min.js"></script>

do_connect();
$realmsetrst = get_rst('select distinct realmset from tblRealm');
while ($realmsetrow = next_row($realmsetrst)) {
	file_put_contents('public/'.strtolower($realmsetrow['realmset']).'.html', cookhtml($realmsetrow['realmset']));
	$realmrst = get_rst('select distinct slug from tblRealm where realmset=\''.sql_esc($realmsetrow['realmset']).'\'');
	while ($realmrow = next_row($realmrst)) file_put_contents('public/'.strtolower($realmsetrow['realmset']).'-'.$realmrow['slug'].'.html', cookhtml($realmsetrow['realmset'],$realmrow['slug']));
}


// PRETTYREALM SLUG REALMBIO REALMSET

function cookhtml($realmset, $slug='') {
	global $htmlheader,$htmlrealm,$htmlend,$htmlrealmset;

	if ($slug != '') {
		$html = $htmlheader.$htmlrealm.$htmlend;

		do_connect();

		$realmset = substr(strtoupper($realmset),0,2);
		$realmslug = substr($slug, 0, 45);

		$row = get_single_row('select * from tblRealm where realmset=\''.sql_esc($realmset).'\' and slug=\''.sql_esc($realmslug).'\'');
		if (!isset($row['id'])) return '';

		if ($realmslug != $row['slug']) {
			$realmslug = $row['slug'];
		}

		$realmbio = $row['realmset'].' '.$row['name'].' is a '.(($row['rp']=='1')?'RP ':'Normal ').(($row['pvp']=='1')?'PvP':'PvE').' realm';
		if (nvl($row['region'],'') != '') $realmbio .= ' in the '.$row['region'].' region';
		if (nvl($row['timezone'],'') != '') $realmbio .= ' in the '.$row['timezone'].' time zone';
		$realmbio .= '.';
		//if (nvl($row['forumid'],'') != '') $realmbio .= ' <a href="http://'.strtolower($row['realmset']).'.battle.net/wow/
		if (nvl($row['population'],'') != '') $realmbio .= ' Blizzard calls it a '.$row['population'].' population realm.';
	
		//header('Content-type: text/html; charset=utf8');
		$html = str_replace('REALMSET', strtolower($row['realmset']), str_replace('REALMBIO', $realmbio, str_replace('PRETTYREALM', $row['realmset']." ".$row['name'], str_replace('SLUG', $row['slug'], $html))));

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

		$html = str_replace('REALMSET', strtolower($realmset), str_replace('REALMBIO', $realmbio, str_replace('PRETTYREALM', $realmset, str_replace('SLUG', '', $html))));
	
		return $html;
	}
}

cleanup();
?>
