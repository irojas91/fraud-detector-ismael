<?php

namespace App\Domain\Port;

interface ReadingReaderInterface
{
    /**
     * @return iterable<string, array<int, \App\Domain\Model\Reading>>
     */
    public function getReadingsGroupedByClient(Source $source): iterable;

    public function supports(Source $source): bool;
}
