<?php

namespace Limiter;

use Storage\RedisStorage;

class LeakyBucketLimiter
{
    private string $id;

    private RedisStorage $storage;

    private array $bucket;

    private int $capacity;

    private float $leak;

    public function __construct(string $id, int $capacity, float $leak)
    {
        $this->id = $id;
        $this->capacity = $capacity;
        $this->leak = $leak;
        $this->storage = new RedisStorage();

        // Initialize the bucket
        $this->bucket = $this->getBucket();
        if (!isset($this->bucket['drops'], $this->bucket['time'])) {
            $this->updateTime();
            $this->bucket['drops'] = 0;
        }
    }

    public function fill()
    {
        $this->bucket['drops'] = $this->bucket['drops'] ?: 0;
        $this->bucket['drops']++;
        if ($this->bucket['drops'] > $this->capacity) {
            $this->bucket['drops'] = $this->capacity;
        }
    }

    public function spill()
    {
        $this->bucket['drops'] = $this->bucket['drops'] ?: 0;
        $this->bucket['drops']--;
        if ($this->bucket['drops'] < 0) {
            $this->bucket['drops'] = 0;
        }
    }

    public function leak()
    {
        $elapsed = microtime(true) - $this->bucket['time'];
        $leakage = $elapsed * $this->leak;

        $this->bucket['drops'] = $this->bucket['drops'] ?: 0;
        $this->bucket['drops'] -= $leakage;

        if ($this->bucket['drops'] < 0) {
            $this->bucket['drops'] = 0;
        }
    }

    public function isFull()
    {
        return (ceil((float) $this->bucket['drops']) >= $this->capacity);
    }

    public function save()
    {
        $this->saveBucket($this->capacity / $this->leak);
    }

    private function updateTime()
    {
        $this->bucket['time'] = microtime(true);
    }

    private function getBucket()
    {
        return $this->storage->fetch($this->id);
    }

    private function saveBucket($ttl = 0)
    {
        $this->updateTime();
        $this->storage->save($this->id, $this->bucket, $ttl);
    }
}
