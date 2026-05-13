<?php

namespace App\Tests\Infrastructure\Adapter;

use App\Infrastructure\Adapter\XmlReadingReader;
use App\Infrastructure\Source\FileSource;
use PHPUnit\Framework\TestCase;

class XmlReadingReaderTest extends TestCase
{
    public function testReadsAndGroupsConsecutiveData(): void
    {
        $xmlContent = '<?xml version="1.0"?><readings><reading clientID="C1" period="2016-01">100</reading><reading clientID="C2" period="2016-01">300</reading></readings>';
        $file = tempnam(sys_get_temp_dir(), 'test_xml');
        file_put_contents($file, $xmlContent);

        $reader = new XmlReadingReader();
        $this->assertTrue($reader->supports(new FileSource('test.xml')));

        $generator = $reader->getReadingsGroupedByClient(new FileSource($file));
        $results = iterator_to_array($generator);

        $this->assertArrayHasKey('C1', $results);
        $this->assertArrayHasKey('C2', $results);
        
        unlink($file);
    }
}
