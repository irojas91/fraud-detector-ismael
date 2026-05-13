<?php

namespace App\Application\UseCase;

use App\Application\Port\ReaderResolverInterface;
use App\Domain\Port\Source;
use App\Domain\Service\FraudDetector;

class DetectFraudUseCase
{
    public function __construct(
        private ReaderResolverInterface $readerResolver,
        private FraudDetector $fraudDetector
    ) {}

    public function execute(Source $source): array
    {
        $reader = $this->readerResolver->resolve($source);

        $suspiciousReadings = [];
        $groupedReadings = $reader->getReadingsGroupedByClient($source);

        // Process line by line grouped by client without loading all file in memory
        foreach ($groupedReadings as $readings) {
            $suspicious = $this->fraudDetector->detect($readings);
            $suspiciousReadings = array_merge($suspiciousReadings, $suspicious);
        }

        return $suspiciousReadings;
    }
}
