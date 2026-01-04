<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\ValueObject;

use ElliotJReed\ValueObject\UnitPricing;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UnitPricing::class)]
final class UnitPricingTest extends TestCase
{
    public function testConstructorAndProperties(): void
    {
        $unitPricing = new UnitPricing(16.0, 'oz', 1.0, 'oz');

        $this->assertSame(16.0, $unitPricing->measure);
        $this->assertSame('oz', $unitPricing->measureUnit);
        $this->assertSame(1.0, $unitPricing->base);
        $this->assertSame('oz', $unitPricing->baseUnit);
    }

    public function testToString(): void
    {
        $unitPricing = new UnitPricing(16.0, 'oz', 1.0, 'oz');

        $this->assertSame('16 oz / 1 oz', $unitPricing->toString());
    }

    public function testToStringWithDifferentUnits(): void
    {
        $unitPricing = new UnitPricing(500.0, 'ml', 100.0, 'ml');

        $this->assertSame('500 ml / 100 ml', $unitPricing->toString());
    }

    public function testReadonlyProperties(): void
    {
        $unitPricing = new UnitPricing(16.0, 'oz', 1.0, 'oz');

        $this->expectException(Error::class);
        $unitPricing->measure = 20.0;
    }
}
