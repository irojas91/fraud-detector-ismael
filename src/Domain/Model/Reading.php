<?php

namespace App\Domain\Model;

readonly class Reading
{
    public function __construct(
        public string $clientId,
        public string $period,
        public float $value
    ) {}
}
