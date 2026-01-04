<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\ValueObject;

use ElliotJReed\Enum\Availability;
use ElliotJReed\ValueObject\GeoAvailability;
use Error;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GeoAvailability::class)]
final class GeoAvailabilityTest extends TestCase
{
    public function testConstructorAndProperties(): void
    {
        $geoAvailability = new GeoAvailability(Availability::IN_STOCK, 'Texas');

        $this->assertSame(Availability::IN_STOCK, $geoAvailability->availability);
        $this->assertSame('Texas', $geoAvailability->region);
    }

    public function testToString(): void
    {
        $geoAvailability = new GeoAvailability(Availability::IN_STOCK, 'Texas');

        $this->assertSame('in_stock (Texas)', $geoAvailability->toString());
    }

    public function testToStringWithOutOfStock(): void
    {
        $geoAvailability = new GeoAvailability(Availability::OUT_OF_STOCK, 'New York');

        $this->assertSame('out_of_stock (New York)', $geoAvailability->toString());
    }

    public function testToStringWithPreorder(): void
    {
        $geoAvailability = new GeoAvailability(Availability::PREORDER, 'California');

        $this->assertSame('preorder (California)', $geoAvailability->toString());
    }

    public function testReadonlyProperties(): void
    {
        $geoAvailability = new GeoAvailability(Availability::IN_STOCK, 'Texas');

        $this->expectException(Error::class);
        $geoAvailability->region = 'California';
    }
}
