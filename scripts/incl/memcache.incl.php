<?php

$memcache = new Memcache;
if (!$memcache->connect('127.0.0.1', 11211))
    DebugMessage('Cannot connect to memcached!', E_USER_ERROR);
$memcache->setCompressThreshold(50*1024);

function MCGet($key)
{
    global $memcache;

    return $memcache->get('rp_'.$key);
}

function MCSet($key, $val, $expire = 10800)
{
    global $memcache;

    return $memcache->set('rp_'.$key, $val, false, $expire);
}

function MCAdd($key, $val, $expire = 10800)
{
    global $memcache;

    return $memcache->add('rp_'.$key, $val, false, $expire);
}

function MCDelete($key)
{
    global $memcache;

    return $memcache->delete('rp_'.$key);
}