<?php

@ini_set('precision','16');

date_default_timezone_set('UTC');

error_reporting(E_ALL);

require_once __DIR__.'/database.credentials.php';
require_once __DIR__ . '/NewsstandHTTP.incl.php';

use Newsstand\HTTP;

$db = false;

function nvl($n,$v) {
	return (is_null($n))?$v:$n;
}

function DebugMessage($message, $debugLevel = E_USER_NOTICE)
{
    static $myPid = false;
    if (!$myPid)
        $myPid = str_pad(getmypid(),5," ",STR_PAD_LEFT);

    if ($debugLevel == E_USER_NOTICE)
        echo Date('Y-m-d H:i:s')." $myPid $message\n";
    else
        trigger_error("\n".Date('Y-m-d H:i:s')." $myPid $message\n", $debugLevel);
}

function DBConnect($alternate = false)
{
    global $db;

    static $connected = false;

    if ($connected && !$alternate)
        return $db;

    $host = DATABASE_HOST;
    $user = DATABASE_USERNAME;
    $pass = DATABASE_PASSWORD;
    $database = DATABASE_SCHEMA;

    $thisDb = new mysqli($host, $user, $pass, $database);
    if ($thisDb->connect_error)
        $thisDb = false;
    else
    {
        $thisDb->set_charset("utf8");
        $thisDb->query('SET time_zone=\'+0:00\'');
    }

    if (!$alternate)
    {
        $db = $thisDb;
        $connected = !!$db;
    }

    return $thisDb;
}

// key = false, use 1st column as key
// key = 'abc', use col 'abc' as key
// key = null, no key
// key = array('abc', 'def'), use abc as first key, def as second
// key = array('abc', false), use abc as first key, no key for second

function DBMapArray(&$result, $key = false, $autoClose = true)
{
    $tr = array();
    $singleCol = null;

    while ($row = $result->fetch_assoc())
    {
        if (is_null($singleCol))
        {
            $singleCol = false;
            if (count(array_keys($row)) == 1)
            {
                $singleCol = array_keys($row);
                $singleCol = array_shift($singleCol);
            }
        }
        if ($key === false)
        {
            $key = array_keys($row);
            $key = array_shift($key);
        }
        if (is_array($key))
            switch (count($key))
            {
                case 1:
                    $tr[$row[$key[0]]] = $singleCol ? $row[$singleCol] : $row;
                    break;
                case 2:
                    if($key[1])
                        $tr[$row[$key[0]]][$row[$key[1]]] = $singleCol ? $row[$singleCol] : $row;
                    else
                        $tr[$row[$key[0]]][] = $singleCol ? $row[$singleCol] : $row;
                    break;
            }
        elseif (is_null($key))
            $tr[] = $singleCol ? $row[$singleCol] : $row;
        else
            $tr[$row[$key]] = $singleCol ? $row[$singleCol] : $row;
    }

    if ($autoClose)
        $result->close();

    return $tr;
}

function FetchHTTP($url, $inHeaders = array(), &$outHeaders = array()) {
    static $curlOpts = false;
    if ($curlOpts === false) {
        $curlOpts = [];
        if (defined('RP_CURL_INTERFACE')) {
            $curlOpts[CURLOPT_INTERFACE] = RP_CURL_INTERFACE;
        }
    }

    return HTTP::Get($url, $inHeaders, $outHeaders, $curlOpts);
}

function TimeDiff($time, $opt = array()) {
    if (is_null($time)) return '';

    // The default values
    $defOptions = array(
        'to' => 0,
        'parts' => 2,
        'precision' => 'minute',
        'distance' => TRUE,
        'separator' => ', '
    );
    $opt = array_merge($defOptions, $opt);
    // Default to current time if no to point is given
    (!$opt['to']) && ($opt['to'] = time());
    // Init an empty string
    $str = '';
    // To or From computation
    $diff = ($opt['to'] > $time) ? $opt['to']-$time : $time-$opt['to'];
    // An array of label => periods of seconds;
    $periods = array(
        'decade' => 315569260,
        'year' => 31556926,
        'month' => 2629744,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    );
    // Round to precision
    if ($opt['precision'] != 'second')
        $diff = round(($diff/$periods[$opt['precision']])) * $periods[$opt['precision']];
    // Report the value is 'less than 1 ' precision period away
    (0 == $diff) && ($str = 'less than 1 '.$opt['precision']);
    // Loop over each period
    foreach ($periods as $label => $value) {
        // Stitch together the time difference string
        (($x=floor($diff/$value))&&$opt['parts']--) && $str.=($str?$opt['separator']:'').($x.' '.$label.($x>1?'s':''));
        // Stop processing if no more parts are going to be reported.
        if ($opt['parts'] == 0 || $label == $opt['precision']) break;
        // Get ready for the next pass
        $diff -= $x*$value;
    }
    $opt['distance'] && $str.=($str&&$opt['to']>=$time)?' ago':' away';
    return $str;
}

function CatchKill($use = true) {
    static $caughtKill = false;
    static $setCatch = false;

    if (PHP_SAPI != 'cli') {
        DebugMessage('Cannot catch kill if not CLI', E_USER_WARNING);
        return false;
    }

    if (!$use) {
        $setCatch = $caughtKill = false;
        return pcntl_signal(SIGTERM, SIG_DFL);
    }

    if ($setCatch) {
        pcntl_signal_dispatch();
        return $caughtKill;
    }

    $setCatch = true;

    pcntl_signal(SIGTERM, function($sig) use (&$caughtKill) {
        if ($sig == SIGTERM) {
            $caughtKill = true;
            DebugMessage('Caught kill message, exiting soon..');
        }
    });

    return false;
}

function RunMeNTimes($howMany = 1)
{
    global $argv;

    if (php_sapi_name() != 'cli')
    {
        DebugMessage('Cannot run once if not CLI', E_USER_WARNING);
        return;
    }

    if (intval(shell_exec('ps -o args -C php | grep '.escapeshellarg(implode(' ',$argv)).' | wc -l')) > $howMany) die();
}

function mb_trim($s) {
	return mb_ereg_replace('^\s+|\s+$','',$s);
}

