<?php

namespace Storage;

use Window\WindowInterface;

class MemoryStorage implements StorageInterface
{
    /**
     * @var array
     * Save pairs: expireAt date and window object
     */
    private array $storage = [];

    public function save(WindowInterface $limiterWindow): void
    {
        if (isset($this->storage[$limiterWindow->getId()])) {
            [$expireAt,] = $this->storage[$limiterWindow->getId()];
        }

        if (null !== ($expireSeconds = $limiterWindow->getExpirationTime())) {
            $expireAt = microtime(true) + $expireSeconds;
        }

        $this->storage[$limiterWindow->getId()] = [$expireAt, serialize($limiterWindow)];
    }

    public function fetch(string $limiterWindowId): ?WindowInterface
    {
        if (!isset($this->storage[$limiterWindowId])) {
            return null;
        }

        [$expireAt, $limiterWindow] = $this->storage[$limiterWindowId];
        if (null !== $expireAt && $expireAt <= microtime(true)) {
            unset($this->storage[$limiterWindowId]);

            return null;
        }

        return unserialize($limiterWindow);
    }

    public function delete(string $limiterWindowId): void
    {
        if (!isset($this->storage[$limiterWindowId])) {
            return;
        }

        unset($this->storage[$limiterWindowId]);
    }
}
