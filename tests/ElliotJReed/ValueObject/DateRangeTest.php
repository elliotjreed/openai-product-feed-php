<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\ValueObject;

use DateTime;
use ElliotJReed\ValueObject\DateRange;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DateRange::class)]
final class DateRangeTest extends TestCase
{
    public function testConstructorAndProperties(): void
    {
        $startDate = new DateTime('2025-07-01');
        $endDate = new DateTime('2025-07-15');
        $dateRange = new DateRange($startDate, $endDate);

        $this->assertSame($startDate, $dateRange->startDate);
        $this->assertSame($endDate, $dateRange->endDate);
    }

    public function testToString(): void
    {
        $startDate = new DateTime('2025-07-01');
        $endDate = new DateTime('2025-07-15');
        $dateRange = new DateRange($startDate, $endDate);

        $this->assertSame('2025-07-01 / 2025-07-15', $dateRange->toString());
    }

    public function testToStringWithDifferentDates(): void
    {
        $startDate = new DateTime('2026-01-01');
        $endDate = new DateTime('2026-12-31');
        $dateRange = new DateRange($startDate, $endDate);

        $this->assertSame('2026-01-01 / 2026-12-31', $dateRange->toString());
    }

    public function testReadonlyProperties(): void
    {
        $dateRange = new DateRange(new DateTime('2025-01-01'), new DateTime('2025-12-31'));

        $this->expectException(Error::class);
        $dateRange->startDate = new DateTime('2026-01-01');
    }
}
