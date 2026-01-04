<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\ValueObject;

use ElliotJReed\ValueObject\Shipping;
use Error;
use Money\Currency;
use Money\Money;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Shipping::class)]
final class ShippingTest extends TestCase
{
    public function testConstructorAndProperties(): void
    {
        $price = new Money('1600', new Currency('USD'));
        $shipping = new Shipping('US', 'CA', 'Overnight', $price);

        $this->assertSame('US', $shipping->country);
        $this->assertSame('CA', $shipping->region);
        $this->assertSame('Overnight', $shipping->serviceClass);
        $this->assertSame($price, $shipping->price);
    }

    public function testToString(): void
    {
        $price = new Money('1600', new Currency('USD'));
        $shipping = new Shipping('US', 'CA', 'Overnight', $price);

        $this->assertSame('US:CA:Overnight:16.00 USD', $shipping->toString());
    }

    public function testToStringWithDifferentValues(): void
    {
        $price = new Money('500', new Currency('GBP'));
        $shipping = new Shipping('GB', 'LDN', 'Standard', $price);

        $this->assertSame('GB:LDN:Standard:5.00 GBP', $shipping->toString());
    }

    public function testReadonlyProperties(): void
    {
        $price = new Money('1600', new Currency('USD'));
        $shipping = new Shipping('US', 'CA', 'Overnight', $price);

        $this->expectException(Error::class);
        $shipping->country = 'GB';
    }
}
