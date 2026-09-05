<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Internal;

use ElliotJReed\OpenAiProductFeed\Attribute\VariantOptions;
use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Attributes::class)]
final class AttributesTest extends TestCase
{
    public function testItDropsAttributesWhichHaveNotBeenSet(): void
    {
        $this->assertSame(
            ['item_id' => 'SKU-1', 'title' => 'Trail running shoes'],
            Attributes::set(['item_id' => 'SKU-1', 'title' => 'Trail running shoes', 'brand' => null])
        );
    }

    public function testItKeepsAttributesInTheOrderTheyAreGiven(): void
    {
        $this->assertSame(
            ['title', 'item_id'],
            \array_keys(Attributes::set(['title' => 'Trail running shoes', 'brand' => null, 'item_id' => 'SKU-1']))
        );
    }

    public function testItKeepsAttributesWhichAreExplicitlyFalse(): void
    {
        $this->assertSame(
            ['is_eligible_search' => false],
            Attributes::set(['is_eligible_search' => false, 'is_ads_eligible' => null])
        );
    }

    public function testItKeepsAttributesWhichAreZero(): void
    {
        $this->assertSame(['review_count' => 0], Attributes::set(['review_count' => 0]));
    }

    public function testItKeepsAttributesWhichAreAnEmptyString(): void
    {
        $this->assertSame(['material' => ''], Attributes::set(['material' => '']));
    }

    public function testItUnwrapsStructuredAttributesIntoTheirArrayForm(): void
    {
        $this->assertSame(
            ['variant_dict' => ['color' => 'Black', 'size' => '10']],
            Attributes::set([
                'variant_dict' => new VariantOptions()->add('color', 'Black')->add('size', '10')
            ])
        );
    }

    public function testItReturnsNothingWhenNothingIsSet(): void
    {
        $this->assertSame([], Attributes::set(['item_id' => null, 'title' => null]));
    }
}
