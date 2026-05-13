<?php

namespace App\Infrastructure\Adapter;

use App\Domain\Model\Reading;
use App\Domain\Port\ReadingReaderInterface;
use App\Domain\Port\Source;
use App\Infrastructure\Source\FileSource;

class CsvReadingReader implements ReadingReaderInterface
{
    public function supports(Source $source): bool
    {
        if (!$source instanceof FileSource) {
            return false;
        }

        return str_ends_with(strtolower($source->path), '.csv');
    }

    public function getReadingsGroupedByClient(Source $source): iterable
    {
        if (!$source instanceof FileSource) {
            throw new \InvalidArgumentException('CsvReadingReader expects a FileSource.');
        }

        $handle = fopen($source->path, 'r');
        fgetcsv($handle, null, ',', '"', '\\'); // Skip header row

        $currentClientId = null;
        $clientReadings = [];

        // Leveraging the fact that readings are consecutive to yield per client.
        while (($data = fgetcsv($handle, null, ',', '"', '\\')) !== false) {
            if (count($data) < 3) continue;

            [$clientId, $period, $value] = $data;
            
            //TODO: Apply validation rules here
            
            if ($currentClientId !== null && $clientId !== $currentClientId) {
                yield $currentClientId => $clientReadings;
                $clientReadings = [];
            }
            
            $currentClientId = $clientId;
            $clientReadings[] = new Reading($clientId, $period, (float) $value);
        }

        if (!empty($clientReadings)) {
            yield $currentClientId => $clientReadings;
        }

        fclose($handle);
    }
}
