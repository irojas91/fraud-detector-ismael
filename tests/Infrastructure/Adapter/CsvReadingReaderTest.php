<?php

namespace App\Tests\Infrastructure\Adapter;

use App\Infrastructure\Adapter\CsvReadingReader;
use App\Infrastructure\Source\FileSource;
use PHPUnit\Framework\TestCase;

class CsvReadingReaderTest extends TestCase
{
    public function testReadsAndGroupsConsecutiveData(): void
    {
        $csvContent = "client,period,reading\nC1,2016-01,100\nC1,2016-02,200\nC2,2016-01,300\n";
        $file = tempnam(sys_get_temp_dir(), 'test_csv');
        file_put_contents($file, $csvContent);

        $reader = new CsvReadingReader();
        $this->assertTrue($reader->supports(new FileSource('test.csv')));

        $generator = $reader->getReadingsGroupedByClient(new FileSource($file));
        $results = iterator_to_array($generator);

        $this->assertArrayHasKey('C1', $results);
        $this->assertCount(2, $results['C1']);
        $this->assertArrayHasKey('C2', $results);
        
        unlink($file);
    }
}
