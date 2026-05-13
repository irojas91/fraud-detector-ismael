<?php

namespace App\Infrastructure\Source;

use App\Domain\Port\Source;

class FileSource implements Source
{
    public function __construct(public readonly string $path)
    {
    }
}
