<?php

namespace App\Domain\Model;

readonly class SuspiciousReading
{
    public function __construct(
        public string $clientId,
        public string $month,
        public float $suspiciousValue,
        public float $median
    ) {}
}
