<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\ValueObject;

use ElliotJReed\ValueObject\GeoPrice;
use Error;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GeoPrice::class)]
final class GeoPriceTest extends TestCase
{
    public function testConstructorAndProperties(): void
    {
        $price = new Money('7999', new Currency('USD'));
        $geoPrice = new GeoPrice($price, 'California');

        $this->assertSame($price, $geoPrice->price);
        $this->assertSame('California', $geoPrice->region);
    }

    public function testToString(): void
    {
        $price = new Money('7999', new Currency('USD'));
        $geoPrice = new GeoPrice($price, 'California');

        $this->assertSame('79.99 USD (California)', $geoPrice->toString());
    }

    public function testToStringWithDifferentRegion(): void
    {
        $price = new Money('8999', new Currency('USD'));
        $geoPrice = new GeoPrice($price, 'Texas');

        $this->assertSame('89.99 USD (Texas)', $geoPrice->toString());
    }

    public function testReadonlyProperties(): void
    {
        $price = new Money('7999', new Currency('USD'));
        $geoPrice = new GeoPrice($price, 'California');

        $this->expectException(Error::class);
        $geoPrice->region = 'Texas';
    }
}
