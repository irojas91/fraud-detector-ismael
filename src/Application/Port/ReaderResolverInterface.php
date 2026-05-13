<?php

namespace App\Application\Port;

use App\Domain\Port\ReadingReaderInterface;
use App\Domain\Port\Source;

interface ReaderResolverInterface
{
    public function resolve(Source $source): ReadingReaderInterface;
}
