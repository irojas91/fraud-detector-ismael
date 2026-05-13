<?php

namespace App\Tests\Domain\Service;

use App\Domain\Model\Reading;
use App\Domain\Service\FraudDetector;
use PHPUnit\Framework\TestCase;

class FraudDetectorTest extends TestCase
{
    public function testDetectsSuspiciousReadingsCorrectly(): void
    {
        $detector = new FraudDetector();
        
        $readings = [
            new Reading('C1', '2016-01', 100),
            new Reading('C1', '2016-02', 100),
            new Reading('C1', '2016-03', 100),
            new Reading('C1', '2016-04', 100),
            new Reading('C1', '2016-05', 49),  // Suspicious ( < 50% of median 100 )
            new Reading('C1', '2016-06', 151), // Suspicious ( > 150% of median 100 )
            new Reading('C1', '2016-07', 100),
        ];

        $suspicious = $detector->detect($readings);

        $this->assertCount(2, $suspicious);
        $this->assertEquals(49, $suspicious[0]->suspiciousValue);
        $this->assertEquals(151, $suspicious[1]->suspiciousValue);
    }
}
