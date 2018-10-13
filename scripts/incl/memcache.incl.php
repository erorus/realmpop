<?php

$memcache = new Memcached;
if (!$memcache->getServerList()) {
    $memcache->addServer('127.0.0.1', 11211);
}
$memcache->setOptions([
    Memcached::OPT_BINARY_PROTOCOL => true,
    Memcached::OPT_PREFIX_KEY => 'realmpop',
]);

function MCGet($key)
{
    global $memcache;

    return is_array($key) ? $memcache->getMulti($key) : $memcache->get($key);
}

function MCSet($key, $val, $expire = 10800)
{
    global $memcache;

    return $memcache->set($key, $val, $expire);
}

function MCAdd($key, $val, $expire = 10800)
{
    global $memcache;

    return $memcache->add($key, $val, $expire);
}

function MCDelete($key)
{
    global $memcache;

    return $memcache->delete($key);
}
