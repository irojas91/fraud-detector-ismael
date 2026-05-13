<?php

namespace App\Tests\Application\UseCase;

use App\Application\Port\ReaderResolverInterface;
use App\Application\UseCase\DetectFraudUseCase;
use App\Domain\Model\Reading;
use App\Domain\Model\SuspiciousReading;
use App\Domain\Port\ReadingReaderInterface;
use App\Domain\Port\Source;
use App\Domain\Service\FraudDetector;
use PHPUnit\Framework\TestCase;

class DetectFraudUseCaseTest extends TestCase
{
    public function testExecuteResolvesReaderAndAggregatesSuspiciousReadings(): void
    {
        $source = $this->createSource();

        $groupOne = [new Reading('C1', '2016-01', 49.0)];
        $groupTwo = [new Reading('C2', '2016-02', 151.0)];

        $reader = new class($groupOne, $groupTwo) implements ReadingReaderInterface {
            /**
             * @param array<int, Reading> $groupOne
             * @param array<int, Reading> $groupTwo
             */
            public function __construct(
                private array $groupOne,
                private array $groupTwo
            ) {}

            public function getReadingsGroupedByClient(Source $source): iterable
            {
                yield 'C1' => $this->groupOne;
                yield 'C2' => $this->groupTwo;
            }

            public function supports(Source $source): bool
            {
                return true;
            }
        };

        $resolver = $this->createMock(ReaderResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->with($source)
            ->willReturn($reader);

        $detector = $this->createMock(FraudDetector::class);
        $invocations = 0;
        $detector->expects($this->exactly(2))
            ->method('detect')
            ->willReturnCallback(function (array $readings) use (&$invocations, $groupOne, $groupTwo): array {
                if ($invocations === 0) {
                    $this->assertSame($groupOne, $readings);
                    $invocations++;
                    return [new SuspiciousReading('C1', '2016-01', 49.0, 100.0)];
                }

                $this->assertSame($groupTwo, $readings);
                return [new SuspiciousReading('C2', '2016-02', 151.0, 100.0)];
            });

        $useCase = new DetectFraudUseCase($resolver, $detector);
        $result = $useCase->execute($source);

        $this->assertCount(2, $result);
        $this->assertSame('C1', $result[0]->clientId);
        $this->assertSame('C2', $result[1]->clientId);
    }

    public function testExecuteReturnsEmptyArrayWhenReaderYieldsNoGroups(): void
    {
        $source = $this->createSource();

        $reader = new class implements ReadingReaderInterface {
            public function getReadingsGroupedByClient(Source $source): iterable
            {
                return [];
            }

            public function supports(Source $source): bool
            {
                return true;
            }
        };

        $resolver = $this->createMock(ReaderResolverInterface::class);
        $resolver->method('resolve')->willReturn($reader);

        $detector = $this->createMock(FraudDetector::class);
        $detector->expects($this->never())->method('detect');

        $useCase = new DetectFraudUseCase($resolver, $detector);

        $this->assertSame([], $useCase->execute($source));
    }

    public function testExecuteContinuesWhenDetectFailsForOneClientGroup(): void
    {
        $source = $this->createSource();

        $groupOne = [new Reading('C1', '2016-01', 49.0)];
        $groupTwo = [new Reading('C2', '2016-02', 151.0)];

        $reader = new class($groupOne, $groupTwo) implements ReadingReaderInterface {
            /**
             * @param array<int, Reading> $groupOne
             * @param array<int, Reading> $groupTwo
             */
            public function __construct(
                private array $groupOne,
                private array $groupTwo
            ) {}

            public function getReadingsGroupedByClient(Source $source): iterable
            {
                yield 'C1' => $this->groupOne;
                yield 'C2' => $this->groupTwo;
            }

            public function supports(Source $source): bool
            {
                return true;
            }
        };

        $resolver = $this->createMock(ReaderResolverInterface::class);
        $resolver->method('resolve')->willReturn($reader);

        $detector = $this->createMock(FraudDetector::class);
        $invocations = 0;
        $detector->expects($this->exactly(2))
            ->method('detect')
            ->willReturnCallback(function (array $readings) use (&$invocations, $groupOne, $groupTwo): array {
                if ($invocations === 0) {
                    $this->assertSame($groupOne, $readings);
                    $invocations++;
                    throw new \RuntimeException('Cannot process C1');
                }

                $this->assertSame($groupTwo, $readings);
                return [new SuspiciousReading('C2', '2016-02', 151.0, 100.0)];
            });

        $useCase = new DetectFraudUseCase($resolver, $detector);
        $result = $useCase->execute($source);

        $this->assertCount(1, $result);
        $this->assertSame('C2', $result[0]->clientId);
    }

    private function createSource(): Source
    {
        return new class implements Source {
        };
    }
}
