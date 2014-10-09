<?php

chdir(__DIR__);

$startTime = time();

require_once('incl/incl.php');
require_once('incl/heartbeat.incl.php');
require_once('incl/battlenet.incl.php');

//ini_set('memory_limit','512M');

RunMeNTimes(3);
CatchKill();

if (!DBConnect())
    DebugMessage('Cannot connect to db!', E_USER_ERROR);

$allRealms = [];
if (($realm = GetNextRealm()) === false) {
    DebugMessage('No realms to fetch right now.');
    exit;
}

DebugMessage('Working with '.$realm['region'].' '.$realm['name']);

$characterNames = GetCharacterNames($realm);

while (count($characterNames)) {
    heartbeat();
    if ($caughtKill)
        exit;

    GetNextCharacter($characterNames);
}

$characterNames = GetChallengeModeCharacters($realm);

while (count($characterNames)) {
    heartbeat();
    if ($caughtKill)
        exit;

    GetNextCharacter($characterNames);
}

DebugMessage('Done! Started '.TimeDiff($startTime));


function GetNextRealm() {
    global $db, $allRealms;
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

    $stmt = $db->prepare('select r.*, ifnull(r.ownerrealm, replace(name, \' \', \'\')) ownerrealm from tblRealm r where r.region = ?');
    $stmt->bind_param('s', $realm['region']);
    $stmt->execute();
    $result = $stmt->get_result();
    $allRealms = DBMapArray($result, 'name');
    $stmt->close();

    return $realm;
}

function GetCharacterNames($realm) {
    global $caughtKill;

    $result = [];

    heartbeat();
    DebugMessage("Fetching {$realm['region']} {$realm['canonical']}");
    $url = GetBattleNetURL($realm['region'], "wow/auction/data/{$realm['canonical']}");

    $json = FetchHTTP($url);
    $dta = json_decode($json, true);
    if (!isset($dta['files']))
    {
        DebugMessage("{$realm['region']} {$realm['canonical']} returned no files.", E_USER_WARNING);
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


    $xferBytes = isset($outHeaders['X-Original-Content-Length']) ? $outHeaders['X-Original-Content-Length'] : strlen($json);
    DebugMessage("{$realm['region']} {$realm['canonical']} data file ".strlen($json)." bytes".($xferBytes != strlen($json) ? (' (transfer length '.$xferBytes.', '.round($xferBytes/strlen($json)*100,1).'%)') : ''));

    heartbeat();
    if ($caughtKill)
        return $result;

    $c = preg_match_all('/"owner":"([^"\?]+)","ownerRealm":"([^"\?]+)"/', $json, $res);
    for ($x = 0; $x < $c; $x++) {
        $seller = $res[1][$x];
        $sellerRealm = $res[2][$x];

        if (!isset($realm['ownerrealms'][$sellerRealm]))
            continue;

        $result[$sellerRealm][$seller] = 0;
    }

    heartbeat();
    return $result;
}

function GetChallengeModeCharacters($realm) {
    global $caughtKill, $allRealms;

    $result = [];

    heartbeat();
    DebugMessage("Fetching {$realm['region']} {$realm['canonical']} challenge mode");
    $url = GetBattleNetURL($realm['region'], "wow/challenge/{$realm['canonical']}");

    $json = FetchHTTP($url);
    $dta = json_decode($json, true);
    if (!isset($dta['challenge']))
    {
        DebugMessage("{$realm['region']} {$realm['canonical']} returned challenge records.", E_USER_WARNING);
        return $result;
    }

    foreach ($dta['challenge'] as $challenge) {
        foreach ($challenge['groups'] as $group) {
            foreach ($group['members'] as $member) {
                heartbeat();
                if ($caughtKill)
                    return $result;

                if (isset($member['character'])) {
                    $c = $member['character']['name'];
                    $r = $member['character']['realm'];
                    if (isset($allRealms[$r])) {
                        $result[$r][$c] = 0;
                    }
                }
            }

        }
    }

    heartbeat();
    return $result;
}

function GetNextCharacter(&$characterNames) {
    global $realm, $db, $caughtKill;

    $sellerRealms = array_keys($characterNames);
    do {
        $sellerRealm = array_pop($sellerRealms);
        if (count($characterNames[$sellerRealm]) == 0) {
            unset($characterNames[$sellerRealm]);
            $sellerRealm = '~';
        }
    } while (!isset($realm['ownerrealms'][$sellerRealm]) && count($sellerRealms));
    if (!isset($realm['ownerrealms'][$sellerRealm]) && count($sellerRealms) == 0) {
        if (count($characterNames))
            DebugMessage('The following realms were not matched:'."\n\"".implode('", "', array_keys($characterNames))."\"\n against \"".implode('", "', array_keys($realm['ownerrealms'])).'"', E_USER_WARNING);
        $characterNames = [];
        return;
    }
    unset($sellerRealms);

    $realmRow = $realm['ownerrealms'][$sellerRealm];

    reset($characterNames[$sellerRealm]);
    $character = key($characterNames[$sellerRealm]);
    unset($characterNames[$sellerRealm][$character]);

    $c = 0;
    $stmt = $db->prepare('select count(*) from tblCharacter where name=? and realm=? and scanned > timestampadd(week, if(level is null, -1, -4), now())');
    $stmt->bind_param('si', $character, $realmRow['id']);
    $stmt->execute();
    $stmt->bind_result($c);
    $stmt->fetch();
    $stmt->close();

    if ($c > 0)
        return;

    $totalChars = 0;
    foreach (array_keys($characterNames) as $k)
        $totalChars += count($characterNames[$k]);

    DebugMessage("Getting character $character on {$realmRow['name']} ($totalChars remaining)");
    $url = GetBattleNetURL($realmRow['region'], "wow/character/".$realmRow['slug']."/".rawurlencode($character)."?fields=guild");
    $json = FetchHTTP($url);
    if (!$json) {
        $stmt = $db->prepare('insert into tblCharacter (name, realm, scanned) values (?, ?, NOW()) on duplicate key update scanned=values(scanned)');
        $stmt->bind_param('si', $character, $realmRow['id']);
        $stmt->execute();
        $stmt->close();

        return;
    }

    heartbeat();
    if ($caughtKill)
        return;

    $dta = json_decode($json, true);
    if (json_last_error() != JSON_ERROR_NONE)
        return;

    if (!isset($dta['name']))
        return;

    $dta['gender']++; // line up with db enum

    $stmt = $db->prepare('replace into tblCharacter (name, realm, guild, scanned, race, class, gender, level) values (?, ?, null, NOW(), ?, ?, ?, ?)');
    $stmt->bind_param('siiiii', $dta['name'], $realmRow['id'], $dta['race'], $dta['class'], $dta['gender'], $dta['level']);
    $stmt->execute();
    $stmt->close();

    if (isset($dta['guild']) && isset($dta['guild']['name']) && $dta['guild']['name'])
        GetGuild($characterNames, $dta['guild']['name'], $dta['guild']['realm']);

}

function GetGuild(&$characterNames, $guild, $realmName) {
    global $db, $realm, $caughtKill, $allRealms;

    heartbeat();
    if ($caughtKill)
        return;

    if (!isset($allRealms[$realmName])) {
        DebugMessage('Could not find realm '.$realmName);
        return;
    }

    $guildId = 0;
    $scanned = 0;
    $stmt = $db->prepare('select id, ifnull(scanned,\'2000-01-01\') from tblGuild where realm = ? and name = ?');
    $stmt->bind_param('is', $allRealms[$realmName]['id'], $guild);
    $stmt->execute();
    $stmt->bind_result($guildId, $scanned);
    $hasRow = ($stmt->fetch() === true);
    $stmt->close();

    if ($hasRow) {
        if (strtotime($scanned) >= time() - (14*24*60*60))
            return;

    } else {
        $stmt = $db->prepare('insert into tblGuild (realm, name) values (?, ?)');
        $stmt->bind_param('is', $allRealms[$realmName]['id'], $guild);
        $stmt->execute();
        $stmt->close();

        $guildId = $db->insert_id;
    }

    DebugMessage("Getting guild <$guild> on $realmName");
    $url = GetBattleNetURL($allRealms[$realmName]['region'], "wow/guild/".$allRealms[$realmName]['slug']."/".rawurlencode($guild)."?fields=members");
    $json = FetchHTTP($url);
    if (!$json)
        return;

    heartbeat();
    if ($caughtKill)
        return;

    $dta = json_decode($json, true);
    if (json_last_error() != JSON_ERROR_NONE)
        return;

    if (!isset($dta['members']))
        return;

    $charCount = 0;
    $side = $dta['side']+1;

    foreach ($dta['members'] as $member) {
        if (!isset($member['character']))
            continue;

        if (!isset($member['character']['name']))
            continue;

        if (!isset($member['character']['realm']))
            continue;

        if (!isset($allRealms[$member['character']['realm']]))
            continue;

        $charCount++;
        $member['character']['gender']++; // line up with db enum

        $stmt = $db->prepare('replace into tblCharacter (name, realm, guild, scanned, race, class, gender, level) values (?, ?, ?, NOW(), ?, ?, ?, ?)');
        $stmt->bind_param('siiiiii',
            $member['character']['name'],
            $allRealms[$member['character']['realm']]['id'],
            $guildId,
            $member['character']['race'],
            $member['character']['class'],
            $member['character']['gender'],
            $member['character']['level']);
        $stmt->execute();
        $stmt->close();

        unset($characterNames[$allRealms[$member['character']['realm']]['ownerrealm']][$member['character']['name']]);
    }

    $stmt = $db->prepare('update tblGuild set scanned=now(), side=?, members=? where id = ?');
    $stmt->bind_param('iii', $side, $charCount, $guildId);
    $stmt->execute();
    $stmt->close();
}
