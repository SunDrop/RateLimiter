<?php

namespace Window;

final class Window implements WindowInterface
{
    private string $id;

    private int $hitCount = 0;

    private int $windowIntervalSeconds;

    private int $maxSize;

    private float $timer;

    public function __construct(string $id, int $windowIntervalSeconds, int $maxSize)
    {
        $this->id = $id;
        $this->windowIntervalSeconds = $windowIntervalSeconds;
        $this->maxSize = $maxSize;
        $this->timer = microtime(true);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getExpirationTime(): ?int
    {
        return $this->windowIntervalSeconds;
    }

    public function addHit(): void
    {
        if ((microtime(true) - $this->timer) > $this->windowIntervalSeconds) {
            $this->reset();
        }

        $this->hitCount++;
    }

    public function getAvailableTokens(): int
    {
        $now = (microtime(true));

        if ($this->timer > $now) {
            return 0;
        }

        if (($now - $this->timer) > $this->windowIntervalSeconds) {
            return $this->maxSize;
        }

        return $this->maxSize - $this->hitCount;
    }

    private function reset(): void
    {
        $this->timer = microtime(true);
        $this->hitCount = 0;
    }
}
