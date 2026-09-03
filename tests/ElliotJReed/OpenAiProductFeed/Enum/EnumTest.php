<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Enum;

use BackedEnum;
use ElliotJReed\OpenAiProductFeed\Enum\AgeGroup;
use ElliotJReed\OpenAiProductFeed\Enum\Availability;
use ElliotJReed\OpenAiProductFeed\Enum\Condition;
use ElliotJReed\OpenAiProductFeed\Enum\Delimiter;
use ElliotJReed\OpenAiProductFeed\Enum\DimensionsUnit;
use ElliotJReed\OpenAiProductFeed\Enum\Gender;
use ElliotJReed\OpenAiProductFeed\Enum\SizeSystem;
use ElliotJReed\OpenAiProductFeed\Enum\WeightUnit;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Each enum has to spell its values exactly as the specification does, since they are written to the feed verbatim.
 */
#[CoversClass(AgeGroup::class)]
#[CoversClass(Availability::class)]
#[CoversClass(Condition::class)]
#[CoversClass(Delimiter::class)]
#[CoversClass(DimensionsUnit::class)]
#[CoversClass(Gender::class)]
#[CoversClass(SizeSystem::class)]
#[CoversClass(WeightUnit::class)]
final class EnumTest extends TestCase
{
    /**
     * @param class-string<BackedEnum> $enum
     * @param list<string>             $expected
     */
    #[DataProvider('enums')]
    public function testItSpellsEveryValueAsTheSpecificationDoes(string $enum, array $expected): void
    {
        $this->assertSame($expected, \array_column($enum::cases(), 'value'));
    }

    /**
     * @return list<array{class-string<BackedEnum>, list<string>}>
     */
    public static function enums(): array
    {
        return [
            [Availability::class, ['in_stock', 'out_of_stock', 'pre_order', 'backorder', 'unknown']],
            [Condition::class, ['new', 'refurbished', 'used']],
            [Gender::class, ['male', 'female', 'unisex']],
            [AgeGroup::class, ['newborn', 'infant', 'toddler', 'kids', 'adult']],
            [DimensionsUnit::class, ['in', 'cm', 'ft', 'm', 'mm']],
            [WeightUnit::class, ['g', 'kg', 'oz', 'lb']],
            [SizeSystem::class, ['US', 'UK', 'EU', 'DE', 'FR', 'JP', 'CN', 'IT', 'BR', 'MEX', 'AU']],
            [Delimiter::class, [',', "\t"]]
        ];
    }

    public function testItSpellsMexicoAsTheSpecificationDoesRatherThanAsAnIsoCountryCode(): void
    {
        $this->assertSame('MEX', SizeSystem::Mexico->value);
    }

    public function testItUsesTheUnderscoredSpellingOfPreOrder(): void
    {
        $this->assertSame('pre_order', Availability::PreOrder->value);
    }
}
