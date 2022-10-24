<?php

namespace App\Services\Cache;

use Predis\Client;

class Redis
{
    private const SETTINGS = [
        'scheme' => 'tcp',
		'host'   => '127.0.0.1',
		'port'   => 6379,
        'ttl' => 3600,

    ];

    private static function client()
    {
        return new Client(self::SETTINGS['scheme'] . ':' .self::SETTINGS['host'] . ':' . self::SETTINGS['port']);
    }

    private static function arrayToString($name)
    {
        return (is_array($name)) ? implode(":", $name) : $name;
    }

    public static function set($name, $input, $ttlSet = false)
    {
        $client = self::client();
        $client->set(self::arrayToString($name), serialize($input));

        if ($ttlSet) {
            $client->expire(self::arrayToString($name), self::SETTINGS['ttl']);
        }
    }

    public static function get($name)
    {
        $client = self::client();

        if ($client->exists(self::arrayToString($name))) {
            return unserialize($client->get(self::arrayToString($name)));
        }

        return null;
    }

}