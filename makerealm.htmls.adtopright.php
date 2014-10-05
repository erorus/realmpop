<?php

require_once('incl.php');

$htmlheader = <<<PHPEOF
<!DOCTYPE html>
<html>
<head>
<title>PRETTYREALM Realm Pop</title>
<link rel="shortcut icon" href="/favicon.ico"></link>
<link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'></link>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/smoothness/jquery-ui.css" type="text/css"></link>
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
</head>
<body style="margin: 0; font-family: 'Abel', sans-serif">
<div style="margin: 0 auto; width: 900px; vertical-align: bottom; padding-top: 10px; padding-bottom: 10px; position: relative">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tr><td valign="bottom" style="padding-right: 10px">
	<h1>PRETTYREALM <span class="nobr">Realm Pop</span></h1>
</td><td width="468" align="right" valign="top">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-1018837251546750";
/* RealmpopTopRight */
google_ad_slot = "3250878183";
google_ad_width = 468;
google_ad_height = 60;
//-->
</script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
</td></tr></table>
<div id="divRealmBio" style="margin-bottom: 1.5em">REALMBIO</div>
Click a pie wedge to narrow down, click again to branch out. Find lists and navigation at the bottom of the page.
PHPEOF;

// <div style="position: absolute; left: -50px; height:1000px; border-right: 1px dashed #999"></div>


$htmlrealm = <<<PHPEOF
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
			<div style="position: absolute; width: 220px; left: 700px">90</div>

		</div>
	</td>
</tr>
</table>
<table border="0" cellspacing="0" cellpadding="0" id="tblLists" style="margin-bottom: 25px">
<tr><td width="500">
	<div id="divTtlList" class="ttl">Characters</div>
	<div id="chtList" style="width: 500px; height: 450px; overflow-y: auto"></div>
</td><td width="400">
	<div id="divTtlGuildList" class="ttl">Guilds</div>
	<div id="chtGuildList" style="width: 350px; margin-left: 50px; height: 450px; overflow-y: auto"></div>
</td></tr>
</table>
</div>
</div>
<a href="REALMSET.html" class="ttl">Back to All Realms</a>
PHPEOF;

$htmlrealmset = <<<PHPEOF
<div style="position: relative">
<div id="divLoading" style="position: fixed; width: 900px; text-align: center; margin-top: 4em; font-size: larger">
	<div id="divLoadingText">Loading code..</div>
	<div id="divLoadingBar" style="width: 450px; margin-left: 225px"></div>
</div>
<div id="divAllCharts" style="margin-top: 4em; visibility: hidden">
<div class="ttl" style="position: absolute; left: 350px; width: 200px; top: 500px; text-align: center; z-index: 2" id="divResults"></div>
<table border="0" cellspacing="0" cellpadding="0" id="tblCharts" style="width: 900px">
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
			<div style="position: absolute; width: 220px; left: 700px">90</div>

		</div>
	</td>
</tr>
</table>
<div id="chtList" style="width: 900px; height: 450px; overflow-y: auto"></div>
</div>
</div>
<a href="./" class="ttl">Back to Front Page</a>
PHPEOF;

$htmlend = <<<PHPEOF
</div>
<div align="center">
<script type="text/javascript"><!--
google_ad_client = "ca-pub-1018837251546750";
/* RealmpopBottomCenter */
google_ad_slot = "6591093962";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>
</div>

<div align="center" style="font-size: 9px; margin-top: 20px; font-family: sans-serif">Realm Pop uses names and images from World of Warcraft, and data proprietary to Blizzard Entertainment, Inc.<br>World of Warcraft, Warcraft and Blizzard Entertainment are trademarks or registered trademarks of Blizzard Entertainment, Inc. in the U.S. and/or other countries.</div>

<script type="text/javascript">
	var __realmset="REALMSET";
	var __slug="SLUG";
	var __jsonsize="JSONSIZE";
</script>
<script type="text/javascript" src="//www.google.com/jsapi"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
<script type="text/javascript" src="jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="cloud.min.js"></script>
<script type="text/javascript" src="realmcharts.min.js"></script>
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

// <script type="text/javascript" src="realmpop.min.js"></script>

do_connect();
$realmsetrst = get_rst('select distinct realmset from tblRealm');
while ($realmsetrow = next_row($realmsetrst)) {
	file_put_contents('public/'.strtolower($realmsetrow['realmset']).'.html', cookhtml($realmsetrow['realmset']));
	$realmrst = get_rst('select distinct slug from tblRealm where realmset=\''.sql_esc($realmsetrow['realmset']).'\'');
	while ($realmrow = next_row($realmrst)) file_put_contents('public/'.strtolower($realmsetrow['realmset']).'-'.$realmrow['slug'].'.html', cookhtml($realmsetrow['realmset'],$realmrow['slug']));
}
cleanup();

// PRETTYREALM SLUG REALMBIO REALMSET JSONSIZE

function cookhtml($realmset, $slug='') {
	global $htmlheader,$htmlrealm,$htmlend,$htmlrealmset;

	$jsonfn = 'public/'.strtolower($realmset).(($slug != '')?'-':'').$slug.'.json';
	$jsonsize = file_exists($jsonfn)?filesize($jsonfn):0;

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
		$html = str_replace('JSONSIZE',$jsonsize,str_replace('REALMSET', strtolower($row['realmset']), str_replace('REALMBIO', $realmbio, str_replace('PRETTYREALM', $row['realmset']." ".$row['name'], str_replace('SLUG', $row['slug'], $html)))));

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

		$html = str_replace('JSONSIZE',$jsonsize,str_replace('REALMSET', strtolower($realmset), str_replace('REALMBIO', $realmbio, str_replace('PRETTYREALM', $realmset, str_replace('SLUG', '', $html)))));
	
		return $html;
	}
}

?>
