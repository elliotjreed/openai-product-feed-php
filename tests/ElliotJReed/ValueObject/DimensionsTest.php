<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\ValueObject;

use ElliotJReed\ValueObject\Dimensions;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Dimensions::class)]
final class DimensionsTest extends TestCase
{
    public function testConstructorAndProperties(): void
    {
        $dimensions = new Dimensions(12.5, 8.0, 5.25, 'in');

        $this->assertSame(12.5, $dimensions->length);
        $this->assertSame(8.0, $dimensions->width);
        $this->assertSame(5.25, $dimensions->height);
        $this->assertSame('in', $dimensions->unit);
    }

    public function testToString(): void
    {
        $dimensions = new Dimensions(12.5, 8.0, 5.25, 'in');

        $this->assertSame('12.5x8x5.25 in', $dimensions->toString());
    }

    public function testToStringWithCentimeters(): void
    {
        $dimensions = new Dimensions(30.0, 20.0, 15.5, 'cm');

        $this->assertSame('30x20x15.5 cm', $dimensions->toString());
    }

    public function testReadonlyProperties(): void
    {
        $dimensions = new Dimensions(10.0, 5.0, 2.0, 'mm');

        $this->expectException(Error::class);
        $dimensions->length = 15.0;
    }
}
