<?php

namespace App\Domain\Service;

use App\Domain\Model\Reading;
use App\Domain\Model\SuspiciousReading;

class FraudDetector
{
    /**
     * @param Reading[] $readings
     * @return SuspiciousReading[]
     */
    public function detect(array $readings): array
    {
        if (empty($readings)) {
            return [];
        }

        [$median, $lowerBound, $upperBound] = $this->calculateMedianBounds($readings);

        $suspicious = [];
        foreach ($readings as $reading) {
            if ($reading->value < $lowerBound || $reading->value > $upperBound) {
                $suspicious[] = new SuspiciousReading(
                    $reading->clientId,
                    $reading->period,
                    $reading->value,
                    $median
                );
            }
        }

        return $suspicious;
    }

    /**
     * @param Reading[] $readings
     * @return array{0: float, 1: float, 2: float}
     */
    private function calculateMedianBounds(array $readings): array
    {
        $values = array_map(fn(Reading $r) => $r->value, $readings);
        sort($values, SORT_NUMERIC);
        $count = count($values);

        $middleIndex = (int) floor($count / 2);
        if ($count % 2 === 0) {
            $median = ($values[$middleIndex - 1] + $values[$middleIndex]) / 2.0; // median of even number of values
        } else {
            $median = (float) $values[$middleIndex];
        }

        $lowerBound = $median * 0.5; // -50% of median
        $upperBound = $median * 1.5; // +50% of median

        return [$median, $lowerBound, $upperBound];
    }
}
