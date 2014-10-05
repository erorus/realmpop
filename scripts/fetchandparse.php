<?php

chdir(__DIR__);

$startTime = time();

require_once('incl/incl.php');
require_once('incl/heartbeat.incl.php');
require_once('incl/battlenet.incl.php');

ini_set('memory_limit','512M');

RunMeNTimes(2);
CatchKill();

if (!DBConnect())
    DebugMessage('Cannot connect to db!', E_USER_ERROR);

if (($realm = GetNextRealm()) === false) {
    DebugMessage('No realms to fetch right now.');
    exit;
}

DebugMessage('Working with '.$realm['region'].' '.$realm['name']);

$characterNames = GetCharacterNames($realm);

print_r($characterNames);

DebugMessage('Done! Started '.TimeDiff($startTime));


function GetNextRealm() {
    global $db;
    $db->begin_transaction();

    $stmt = $db->prepare('select * from tblRealm where canonical is not null and ifnull(lastfetch, \'2000-01-01\') < timestampadd(hour, -6, now()) order by lastfetch asc, id asc limit 1 for update');
    $stmt->execute();
    $result = $stmt->get_result();
    $realm = DBMapArray($result, null);
    $stmt->close();

    if (count($realm) == 0) {
        $db->rollback();
        return false;
    }

    $realm = array_pop($realm);

    $stmt = $db->prepare('update tblRealm set lastfetch=now() where id = ?');
    $stmt->bind_param('i', $realm['id']);
    $stmt->execute();
    $stmt->close();

    $db->commit();

    $stmt = $db->prepare('select r.*, ifnull(r.ownerrealm, replace(name, \' \', \'\')) ownerrealm from tblRealm r where house = ?');
    $stmt->bind_param('i', $realm['house']);
    $stmt->execute();
    $result = $stmt->get_result();
    $realm['ownerrealms'] = DBMapArray($result, 'ownerrealm');
    $stmt->close();

    return $realm;
}

function GetCharacterNames($realm) {
    global $db, $caughtKill;

    $result = [];

    heartbeat();
    DebugMessage("Fetching {$realm['region']} {$realm['slug']}");
    $url = GetBattleNetURL($realm['region'], "wow/auction/data/{$realm['slug']}");

    $json = FetchHTTP($url);
    $dta = json_decode($json, true);
    if (!isset($dta['files']))
    {
        DebugMessage("{$realm['region']} {$realm['slug']} returned no files.", E_USER_WARNING);
        return $result;
    }

    heartbeat();
    if ($caughtKill)
        return $result;

    $url = $dta['files'][0]['url'];

    $outHeaders = array();
    $json = FetchHTTP($url, [], $outHeaders);
    if (!$json)
    {
        heartbeat();
        if ($caughtKill)
            return $result;

        DebugMessage("No data from $url, waiting 5 secs");
        http_persistent_handles_clean();

        sleep(5);

        heartbeat();
        if ($caughtKill)
            return $result;

        $json = FetchHTTP($url, [], $outHeaders);
    }

    if (!$json)
    {
        heartbeat();
        if ($caughtKill)
            return $result;

        DebugMessage("No data from $url, waiting 15 secs");
        http_persistent_handles_clean();

        sleep(15);

        heartbeat();
        if ($caughtKill)
            return $result;

        $json = FetchHTTP($url, [], $outHeaders);
    }

    if (!$json)
    {
        DebugMessage("No data from $url, giving up");
        return $result;
    }


    $xferBytes = isset($outHeaders['X-Original-Content-Length']) ? $outHeaders['X-Original-Content-Length'] : strlen($data);
    DebugMessage("{$realm['region']} {$realm['slug']} data file ".strlen($json)." bytes".($xferBytes != strlen($json) ? (' (transfer length '.$xferBytes.', '.round($xferBytes/strlen($json)*100,1).'%)') : ''));

    heartbeat();
    if ($caughtKill)
        return $result;

    $c = preg_match_all('/"owner":"([^"\?]+)","ownerRealm":"([^"\?]+)"/', $json, $res);
    for ($x = 0; $x < $c; $x++) {
        $seller = $res[1][$x];
        $sellerRealm = $res[2][$x];

        if (!isset($realm['ownerrealms'][$sellerRealm]))
            continue;

        $result[$sellerRealm][] = $seller;
    }

    heartbeat();

    foreach (array_keys($result) as $k) {
        $result[$k] = array_unique($result[$k]);
    }

    heartbeat();
    return $result;
}