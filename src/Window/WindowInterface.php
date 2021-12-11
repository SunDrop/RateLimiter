<?php

namespace Window;

interface WindowInterface
{
    public function getId(): string;

    public function getExpirationTime(): ?int;
}
