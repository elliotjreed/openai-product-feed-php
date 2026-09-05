<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Internal;

use ElliotJReed\OpenAiProductFeed\Enum\Availability;
use ElliotJReed\OpenAiProductFeed\Enum\Condition;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Internal\Country;
use ElliotJReed\OpenAiProductFeed\Internal\Currency;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(Country::class)]
#[CoversClass(Currency::class)]
#[CoversClass(Validate::class)]
final class ValidateTest extends TestCase
{
    public function testItTrimsSurroundingWhitespaceFromText(): void
    {
        $this->assertSame('Trail running shoes', Validate::text('title', "  Trail running shoes\n", 150));
    }

    public function testItAcceptsTextWhenNoMaximumLengthApplies(): void
    {
        $this->assertSame(\str_repeat('a', 10000), Validate::text('item_id', \str_repeat('a', 10000)));
    }

    public function testItThrowsWhenTextIsBlank(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The title attribute must not be empty.');

        Validate::text('title', '   ', 150);
    }

    public function testItThrowsWhenTextExceedsTheMaximumLength(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The title attribute must not exceed 150 characters, 151 characters given.');

        Validate::text('title', \str_repeat('a', 151), 150);
    }

    public function testItMeasuresTextLengthInCharactersRatherThanBytes(): void
    {
        $this->assertSame('café', Validate::text('title', 'café', 4));
    }

    public function testItThrowsWhenTextIsNotValidUtf8(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The title attribute must be valid UTF-8 text');

        Validate::text('title', "Trail \xB1\x31 shoes", 150);
    }

    public function testItStripsControlCharactersFromText(): void
    {
        $this->assertSame('Trail shoes', Validate::text('title', "Trail \x00\x07shoes", 150));
    }

    public function testItPreservesNewlinesAndTabsWithinText(): void
    {
        $this->assertSame("First line\nSecond\tcolumn", Validate::text('description', "First line\nSecond\tcolumn"));
    }

    #[DataProvider('validUrls')]
    public function testItAcceptsAbsoluteHttpAndHttpsUrls(string $url): void
    {
        $this->assertSame($url, Validate::url('url', $url));
    }

    /**
     * @return list<array{string}>
     */
    public static function validUrls(): array
    {
        return [
            ['https://example.com/products/trail'],
            ['http://example.com/products/trail'],
            ['https://example.com/products/trail?color=black&size=10']
        ];
    }

    #[DataProvider('invalidUrls')]
    public function testItThrowsWhenAUrlIsNotAnAbsoluteHttpUrl(string $url): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The url attribute must be a valid HTTP or HTTPS URL');

        Validate::url('url', $url);
    }

    /**
     * @return list<array{string}>
     */
    public static function invalidUrls(): array
    {
        return [
            ['ftp://example.com/products/trail'],
            ['/products/trail'],
            ['example.com/products/trail'],
            ['javascript:alert(1)']
        ];
    }

    public function testItThrowsWhenAUrlIsBlank(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The url attribute must not be empty.');

        Validate::url('url', '  ');
    }

    public function testItFormatsMoneyFromAFloat(): void
    {
        $this->assertSame('79.99 USD', Validate::money('price', 79.99, 'USD'));
    }

    public function testItFormatsMoneyFromAnInteger(): void
    {
        $this->assertSame('80.00 USD', Validate::money('price', 80, 'usd'));
    }

    public function testItPreservesTheDecimalPrecisionOfMoneyGivenAsAString(): void
    {
        $this->assertSame('79.9 GBP', Validate::money('price', '79.9', 'GBP'));
    }

    public function testItAllowsZeroMoney(): void
    {
        $this->assertSame('0.00 USD', Validate::money('shipping_price', 0, 'USD'));
    }

    public function testItThrowsWhenMoneyIsNegative(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The price attribute must not be negative, "-1.00" given.');

        Validate::money('price', -1, 'USD');
    }

    #[DataProvider('invalidMoneyAmounts')]
    public function testItThrowsWhenMoneyIsNotAPlainDecimal(string $amount): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The price attribute must be a number using a full stop');

        Validate::money('price', $amount, 'USD');
    }

    /**
     * @return list<array{string}>
     */
    public static function invalidMoneyAmounts(): array
    {
        return [
            ['1,299.00'],
            ['7.9e3'],
            ['seventy nine'],
            ['79.99 USD']
        ];
    }

    public function testItThrowsWhenTheCurrencyIsNotAnIsoCode(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('"XYZ" is not a valid ISO 4217 currency code.');

        Validate::money('price', 10, 'XYZ');
    }

    public function testItUppercasesCountryCodes(): void
    {
        $this->assertSame('GB', Validate::country('store_country', 'gb'));
    }

    public function testItThrowsWhenTheCountryIsNotAnIsoCode(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('"ZZ" is not a valid ISO 3166-1 alpha-2 country code for the store_country attribute.');

        Validate::country('store_country', 'ZZ');
    }

    public function testItReturnsADecimalAsAStringPreservingPrecision(): void
    {
        $this->assertSame('0.750', Validate::decimal('weight', '0.750', 0.0, 1000.0));
    }

    public function testItConvertsANumericDecimalToAString(): void
    {
        $this->assertSame('0.75', Validate::decimal('weight', 0.75, 0.0, 1000.0));
    }

    public function testItThrowsWhenADecimalIsOutOfRange(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The weight attribute must be greater than 0, "0" given.');

        Validate::decimal('weight', 0, 0.0, 1000.0, exclusiveMinimum: true);
    }

    public function testItThrowsWhenADecimalIsAboveTheMaximum(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The weight attribute must be between 0 and 1000, 1500 given.');

        Validate::decimal('weight', 1500, 0.0, 1000.0);
    }

    public function testItFormatsAFixedPrecisionDecimalToTheGivenNumberOfPlaces(): void
    {
        $this->assertSame('4.50', Validate::fixedDecimal('star_rating', 4.5, 0.0, 5.0, 2));
    }

    public function testItThrowsWhenAFixedPrecisionDecimalIsOutOfRange(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The star_rating attribute must be between 0 and 5, 5.5 given.');

        Validate::fixedDecimal('star_rating', 5.5, 0.0, 5.0, 2);
    }

    public function testItAcceptsAnIntegerWithinRange(): void
    {
        $this->assertSame(30, Validate::integer('return_deadline_in_days', 30, 1, \PHP_INT_MAX));
    }

    public function testItThrowsWhenAnIntegerIsBelowTheMinimum(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The review_count attribute must not be less than 0, -1 given.');

        Validate::integer('review_count', -1, 0, \PHP_INT_MAX);
    }

    public function testItResolvesAStringToItsEnumCase(): void
    {
        $this->assertSame(Availability::InStock, Validate::enum('availability', 'in_stock', Availability::class));
    }

    public function testItPassesAnEnumCaseStraightThrough(): void
    {
        $this->assertSame(
            Availability::OutOfStock,
            Validate::enum('availability', Availability::OutOfStock, Availability::class)
        );
    }

    public function testItThrowsWhenAStringIsNotAValidEnumCase(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            '"sold_out" is not a valid value for the availability attribute. ' .
            'Allowed values: in_stock, out_of_stock, pre_order, backorder, unknown.'
        );

        Validate::enum('availability', 'sold_out', Availability::class);
    }

    #[DataProvider('validGtins')]
    public function testItAcceptsAGtinWithAValidCheckDigit(string $gtin): void
    {
        $this->assertSame($gtin, Validate::gtin($gtin));
    }

    /**
     * @return list<array{string}>
     */
    public static function validGtins(): array
    {
        return [
            ['09506000134352'],
            ['3234567890126'],
            ['012345678905'],
            ['96385074']
        ];
    }

    public function testItPreservesLeadingZerosInAGtin(): void
    {
        $this->assertSame('012345678905', Validate::gtin('012345678905'));
    }

    public function testItStripsSpacesAndHyphensFromAGtin(): void
    {
        $this->assertSame('3234567890126', Validate::gtin('3-234 567-890126'));
    }

    public function testItThrowsWhenAGtinHasAnUnsupportedLength(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The gtin attribute must be exactly 8, 12, 13 or 14 digits, "1234567890" given.');

        Validate::gtin('1234567890');
    }

    public function testItThrowsWhenAGtinContainsNonDigits(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The gtin attribute must be exactly 8, 12, 13 or 14 digits');

        Validate::gtin('32345678901X6');
    }

    public function testItThrowsWhenAGtinCheckDigitIsWrong(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The gtin attribute "3234567890127" has an invalid check digit.');

        Validate::gtin('3234567890127');
    }

    public function testItThrowsWhenAUrlIsNotValidUtf8(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The url attribute must be valid UTF-8 text.');

        Validate::url('url', "https://example.com/\xB1\x31");
    }

    public function testItThrowsWhenAnIntegerIsAboveTheMaximum(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The return_deadline_in_days attribute must be between 1 and 365, 400 given.');

        Validate::integer('return_deadline_in_days', 400, 1, 365);
    }

    public function testItReadsAnEnumCaseOfAnotherEnumByItsBackingValueAndRejectsIt(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('"new" is not a valid value for the availability attribute.');

        Validate::enum('availability', Condition::New, Availability::class);
    }

    public function testItRejectsAKeyWhichIsNotUsableAsAnObjectKey(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The variant_dict attribute must not be empty.');

        Validate::text('variant_dict', '');
    }
}
