<?php

namespace App\Infrastructure\Adapter;

use App\Domain\Model\Reading;
use App\Domain\Port\ReadingReaderInterface;
use App\Domain\Port\Source;
use App\Infrastructure\Source\FileSource;
use XMLReader;

class XmlReadingReader implements ReadingReaderInterface
{
    public function supports(Source $source): bool
    {
        if (!$source instanceof FileSource) {
            return false;
        }

        return str_ends_with(strtolower($source->path), '.xml');
    }

    public function getReadingsGroupedByClient(Source $source): iterable
    {
        if (!$source instanceof FileSource) {
            throw new \InvalidArgumentException('XmlReadingReader expects a FileSource.');
        }

        $reader = new XMLReader();
        $reader->open($source->path);

        $currentClientId = null;
        $clientReadings = [];

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'reading') {
                $clientId = $reader->getAttribute('clientID');
                $period = $reader->getAttribute('period');
                $value = (float) $reader->readString();

                if ($currentClientId !== null && $clientId !== $currentClientId) {
                    yield $currentClientId => $clientReadings;
                    $clientReadings = [];
                }

                $currentClientId = $clientId;
                $clientReadings[] = new Reading($clientId, $period, $value);
            }
        }

        if (!empty($clientReadings)) {
            yield $currentClientId => $clientReadings;
        }

        $reader->close();
    }
}
