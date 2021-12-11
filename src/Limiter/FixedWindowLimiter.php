<?php

namespace Limiter;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Storage\StorageInterface;
use Window\Window;
use Window\WindowInterface;

class FixedWindowLimiter
{
    private string $id;

    private int $limit;

    private int $timeIntervalSeconds;

    private StorageInterface $storage;

    public function __construct(
        string $id,
        int $limit,
        DateInterval $interval,
        StorageInterface $storage
    ) {
        if ($limit < 1) {
            throw new InvalidArgumentException('Invalid limit value');
        }

        $this->id = $id;
        $this->limit = $limit;
        $this->timeIntervalSeconds = self::intervalToSecond($interval);
        $this->storage = $storage;
    }

    private static function intervalToSecond($interval): int
    {
        $now = new DateTimeImmutable();

        return ($now->add($interval))->getTimestamp() - $now->getTimestamp();
    }

    public function makeRequest(): bool
    {
        $window = $this->storage->fetch($this->id);
        if (!$window instanceof WindowInterface) {
            $window = new Window($this->id, $this->timeIntervalSeconds, $this->limit);
        }

        $availableTokens = $window->getAvailableTokens();
        if ($availableTokens <= 0) {
            throw new LimiterException('Your request was rejected');
        }
        $window->addHit();
        $this->storage->save($window);

        return true;
    }
}
