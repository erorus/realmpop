<?php

chdir(__DIR__);

define('SNAPSHOT_PATH', '/var/newsstand/snapshots/realmpop/');

$startTime = time();

require_once('incl/incl.php');
require_once('incl/heartbeat.incl.php');
require_once('incl/battlenet.incl.php');

//ini_set('memory_limit','512M');

RunMeNTimes(3);
CatchKill();

if (!DBConnect()) {
    DebugMessage('Cannot connect to db!', E_USER_ERROR);
}

$allRealms = [];
$ownerRealms = [];

$stmt = $db->prepare('select r.*, ifnull(r.ownerrealm, name) ownerrealm from tblRealm r');
$stmt->execute();
$result = $stmt->get_result();
$ownerRealms = DBMapArray($result, ['region', 'ownerrealm']);
$stmt->close();

$stmt = $db->prepare('select r.*, ifnull(r.ownerrealm, name) ownerrealm from tblRealm r');
$stmt->execute();
$result = $stmt->get_result();
$allRealms = DBMapArray($result, ['region', 'name']);
$stmt->close();

$toSleep = 0;
while ((!CatchKill()) && (time() < ($startTime + 60 * 30))) {
    heartbeat();
    sleep(min($toSleep, 10));
    if (CatchKill()) {
        break;
    }
    $toSleep = NextDataFile();
    if ($toSleep === false) {
        break;
    }
}

/*
$characterNames = GetChallengeModeCharacters($realm);

while (count($characterNames)) {
    heartbeat();
    if (CatchKill())
        exit;

    GetNextCharacter($characterNames);
}
*/

DebugMessage('Done! Started '.TimeDiff($startTime));

function NextDataFile() {
    $dir = scandir(substr(SNAPSHOT_PATH, 0, -1), SCANDIR_SORT_ASCENDING);
    $lockFail = false;
    $gotFile = false;
    foreach ($dir as $fileName) {
        if (!preg_match('/^(\d+)-(\w+)-(\d+)\.json$/', $fileName, $res)) {
            continue;
        }
        if (($handle = fopen(SNAPSHOT_PATH . $fileName, 'rb')) === false) {
            continue;
        }

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            $lockFail = true;
            fclose($handle);
            continue;
        }

        if (feof($handle)) {
            fclose($handle);
            unlink(SNAPSHOT_PATH . $fileName);
            continue;
        }

        $snapshot = intval($res[1], 10);
        $region = $res[2];
        $maxId = intval($res[3], 10);

        $gotFile = $fileName;
        break;
    }
    unset($dir);

    if (!$gotFile) {
        return $lockFail ? 3 : 10;
    }

    DebugMessage(
        "{$region} data file from " . TimeDiff(
            $snapshot, array(
                'parts'     => 2,
                'precision' => 'second'
            )
        )
    );
    $json = stream_get_contents($handle);

    fclose($handle);
    unlink(SNAPSHOT_PATH . $fileName);

    if (substr($json, 0, 2) === "\037\213") {
        $json = gzdecode($json);
    }

    ParseAuctionData($region, $maxId, $json);
    return 0;
}

function ParseAuctionData($region, $maxId, $json) {
    $characterNames = GetCharacterNames($region, $maxId, $json);
    unset($json);

    while (count($characterNames)) {
        heartbeat();
        if (CatchKill())
            exit;

        GetNextCharacter($region, $characterNames);
    }
}

function GetCharacterNames($region, $maxId, $json) {
    global $ownerRealms;

    $alertedRealms = [];

    $rolloverMid = pow(2, 30);
    $canRollover = $maxId > $rolloverMid;

    $result = [];
    $c = preg_match_all('/"auc":(\d+),[^\}]*?"owner":"([^"\?]+)","ownerRealm":"([^"\?]+)"/', $json, $res);
    for ($x = 0; $x < $c; $x++) {
        $aucId = intval($res[1][$x], 10);
        if ($aucId <= $maxId && (!$canRollover || $aucId > $rolloverMid)) {
            continue;
        }
        $seller = $res[2][$x];
        $sellerRealm = $res[3][$x];

        if (!isset($ownerRealms[$region][$sellerRealm])) {
            if (!isset($alertedRealms[$sellerRealm])) {
                DebugMessage("Could not find sellerRealm {$sellerRealm} in {$region}");
                $alertedRealms[$sellerRealm] = true;
            }
            continue;
        }

        $result[$sellerRealm][$seller] = 0;
    }

    heartbeat();
    return $result;
}

function GetNextCharacter($region, &$characterNames) {
    global $db, $ownerRealms;

    $sellerRealms = array_keys($characterNames);
    do {
        $sellerRealm = array_pop($sellerRealms);
        if (count($characterNames[$sellerRealm]) == 0) {
            unset($characterNames[$sellerRealm]);
            $sellerRealm = '~';
        }
    } while (!isset($ownerRealms[$region][$sellerRealm]) && count($sellerRealms));
    if (!isset($ownerRealms[$region][$sellerRealm]) && count($sellerRealms) == 0) {
        if (count($characterNames))
            DebugMessage("The following {$region} realms were not matched:\n\"".implode('", "', array_keys($characterNames))."\"\n against \"".implode('", "', array_keys($ownerRealms)).'"', E_USER_WARNING);
        $characterNames = [];
        return;
    }
    unset($sellerRealms);

    $realmRow = $ownerRealms[$region][$sellerRealm];

    $character = false;
    while (!$character && $characterNames[$sellerRealm]) {
        reset($characterNames[$sellerRealm]);
        $character = key($characterNames[$sellerRealm]);
        unset($characterNames[$sellerRealm][$character]);

        $c = 0;
        $stmt = $db->prepare('select count(*) from tblCharacter where name=? and realm=? and scanned > timestampadd(week, -1, now())');
        $stmt->bind_param('si', $character, $realmRow['id']);
        $stmt->execute();
        $stmt->bind_result($c);
        $stmt->fetch();
        $stmt->close();

        if ($c != 0) {
            $character = false;
        }
    }

    if (!$character) {
        DebugMessage("No more characters on {$region} {$realmRow['name']}");
        return;
    }

    $totalChars = 0;
    foreach ($characterNames as $realm => $chars) {
        $totalChars += count($chars);
    }
    DebugMessage("Getting {$region} {$realmRow['name']} character {$character} ($totalChars remaining)");

    $guild = false;

    $url = GetBattleNetURL($realmRow['region'], "wow/character/".$realmRow['slug']."/".rawurlencode($character)."?locale=" . $realmRow['locale'] . "&fields=guild");
    $json = $url ? FetchHTTP($url[0], $url[1]) : false;

    $dta = json_decode($json, true);
    if (json_last_error() != JSON_ERROR_NONE || !isset($dta['name'])) {
        $stmt = $db->prepare('insert into tblCharacter (name, realm, scanned) values (?, ?, NOW()) on duplicate key update scanned=values(scanned)');
        $stmt->bind_param('si', $character, $realmRow['id']);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $db->prepare('insert into tblCharacter (name, realm, scanned, race, class, gender, level) values (?, ?, NOW(), ?, ?, ?, ?) on duplicate key update scanned=values(scanned), race=values(race), class=values(class), gender=values(gender), level=values(level)');
        $stmt->bind_param('siiiii', $dta['name'], $realmRow['id'], $dta['race'], $dta['class'], $dta['gender'], $dta['level']);
        $stmt->execute();
        $stmt->close();

        if (isset($dta['guild']) && isset($dta['guild']['name']) && $dta['guild']['name']) {
            $guild = $dta['guild'];
        }
    }

    if ($guild) {
        heartbeat();
        if (CatchKill()) {
            return;
        }
        GetGuild($region, $characterNames, $guild['name'], $guild['realm']);
    }
}

function GetGuild($region, &$characterNames, $guild, $realmName) {
    global $db, $allRealms;

    heartbeat();
    if (CatchKill())
        return;

    if (!isset($allRealms[$region][$realmName])) {
        DebugMessage("Could not find realm {$region} {$realmName}");
        return;
    }

    $guildId = 0;
    $scanned = 0;
    $stmt = $db->prepare('select id, ifnull(scanned,\'2000-01-01\') from tblGuild where realm = ? and name = ?');
    $stmt->bind_param('is', $allRealms[$region][$realmName]['id'], $guild);
    $stmt->execute();
    $stmt->bind_result($guildId, $scanned);
    $hasRow = ($stmt->fetch() === true);
    $stmt->close();

    if ($hasRow) {
        if (strtotime($scanned) >= time() - (14*24*60*60))
            return;

    } else {
        $stmt = $db->prepare('insert into tblGuild (realm, name) values (?, ?)');
        $stmt->bind_param('is', $allRealms[$region][$realmName]['id'], $guild);
        $stmt->execute();
        $stmt->close();

        $guildId = $db->insert_id;
    }

    DebugMessage("Getting {$region} $realmName guild <$guild>");
    $url = GetBattleNetURL($allRealms[$region][$realmName]['region'], "wow/guild/".$allRealms[$region][$realmName]['slug']."/".rawurlencode($guild)."?locale=" . $allRealms[$region][$realmName]['locale'] . "&fields=members");
    $json = $url ? FetchHTTP($url[0], $url[1]) : false;
    if (!$json)
        return;

    heartbeat();
    if (CatchKill())
        return;

    $dta = json_decode($json, true);
    if (json_last_error() != JSON_ERROR_NONE)
        return;

    if (!isset($dta['members']))
        return;

    $charCount = 0;
    $side = $dta['side'];

    foreach ($dta['members'] as $member) {
        if (!isset($member['character']))
            continue;

        if (!isset($member['character']['name']))
            continue;

        if (!isset($member['character']['realm']))
            continue;

        if (!isset($allRealms[$region][$member['character']['realm']]))
            continue;

        $charCount++;

        $stmt = $db->prepare('insert into tblCharacter (name, realm, scanned, race, class, gender, level) values (?, ?, NOW(), ?, ?, ?, ?) on duplicate key update scanned=values(scanned), race=values(race), class=values(class), gender=values(gender), level=values(level)');
        $stmt->bind_param('siiiii',
            $member['character']['name'],
            $allRealms[$region][$member['character']['realm']]['id'],
            $member['character']['race'],
            $member['character']['class'],
            $member['character']['gender'],
            $member['character']['level']);
        $stmt->execute();
        $stmt->close();

        unset($characterNames[$allRealms[$region][$member['character']['realm']]['ownerrealm']][$member['character']['name']]);
    }

    $stmt = $db->prepare('update tblGuild set scanned=now(), side=?, members=? where id = ?');
    $stmt->bind_param('iii', $side, $charCount, $guildId);
    $stmt->execute();
    $stmt->close();
}
