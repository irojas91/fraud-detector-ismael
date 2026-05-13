<?php

namespace App\Infrastructure\Resolver;

use App\Application\Port\ReaderResolverInterface;
use App\Domain\Port\ReadingReaderInterface;
use App\Domain\Port\Source;

class ReaderResolver implements ReaderResolverInterface
{
    /**
     * @param iterable<ReadingReaderInterface> $readers
     */
    public function __construct(private iterable $readers) {}

    public function resolve(Source $source): ReadingReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader->supports($source)) {
                return $reader;
            }
        }

        throw new \InvalidArgumentException('No supported adapter found for this source.');
    }
}
