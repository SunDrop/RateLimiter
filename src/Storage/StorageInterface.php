<?php

namespace Storage;

use Window\WindowInterface;

interface StorageInterface
{
    public function save(WindowInterface $limiterWindow): void;

    public function fetch(string $limiterWindowId): ?WindowInterface;

    public function delete(string $limiterWindowId): void;
}
