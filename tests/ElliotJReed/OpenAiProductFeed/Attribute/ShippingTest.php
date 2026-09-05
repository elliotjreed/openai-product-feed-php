<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Attribute;

use ElliotJReed\OpenAiProductFeed\Attribute\Shipping;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(Shipping::class)]
final class ShippingTest extends TestCase
{
    public function testItRendersTheFourPartTupleWithoutARegion(): void
    {
        $this->assertSame(
            'US::Standard:5.00 USD',
            new Shipping('US', 'Standard', 5.00, 'USD')->toString()
        );
    }

    public function testItRendersTheFourPartTupleWithARegion(): void
    {
        $this->assertSame(
            'US:CA:Overnight:16.00 USD',
            new Shipping('US', 'Overnight', 16.00, 'USD')->setRegion('CA')->toString()
        );
    }

    public function testItUppercasesTheCountryCode(): void
    {
        $this->assertSame('GB::Standard:0.00 GBP', new Shipping('gb', 'Standard', 0, 'gbp')->toString());
    }

    public function testItPreservesTheDecimalPrecisionOfAPriceGivenAsAString(): void
    {
        $this->assertSame('US::Standard:5.5 USD', new Shipping('US', 'Standard', '5.5', 'USD')->toString());
    }

    public function testItThrowsWhenTheCountryIsNotAnIsoCode(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('"ZZ" is not a valid ISO 3166-1 alpha-2 country code for the shipping attribute.');

        new Shipping('ZZ', 'Standard', 5.00, 'USD');
    }

    public function testItThrowsWhenThePriceIsNegative(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The shipping attribute must not be negative, "-5.00" given.');

        new Shipping('US', 'Standard', -5.00, 'USD');
    }

    public function testItThrowsWhenTheServiceClassContainsTheTupleSeparator(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The shipping service class attribute must be free of the ":" separator, "Next:Day" given.'
        );

        new Shipping('US', 'Next:Day', 5.00, 'USD');
    }

    public function testItThrowsWhenTheRegionContainsTheTupleSeparator(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The shipping region attribute must be free of the ":" separator');

        new Shipping('US', 'Standard', 5.00, 'USD')->setRegion('CA:SF');
    }
}
