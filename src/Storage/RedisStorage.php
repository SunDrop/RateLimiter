<?php

namespace Storage;

use Redis;

class RedisStorage
{
    /** @var Redis */
    private $client;

    public function __construct()
    {
        $this->client = new Redis();
        $this->client->connect(
            'redis',
            6379
        );
    }

    public function __destruct()
    {
        $this->client->close();
    }

    public function fetch($key)
    {
        return unserialize($this->client->get($key));
    }

    public function save($key, $value, $ttl = 0)
    {
        if (isset($ttl) && $ttl > 0) {
            $this->client->set($key, serialize($value), ['EX' => $ttl]);
        } else {
            $this->client->set($key, serialize($value));
        }
    }

    public function delete($key)
    {
        $this->client->del($key);
    }
}
