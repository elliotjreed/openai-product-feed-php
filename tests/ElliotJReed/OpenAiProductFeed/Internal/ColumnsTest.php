<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Internal;

use ElliotJReed\OpenAiProductFeed\Internal\Columns;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Columns::class)]
final class ColumnsTest extends TestCase
{
    public function testItBeginsWithTheRequiredFieldsInSpecificationOrder(): void
    {
        $this->assertSame(
            ['item_id', 'title', 'description', 'url', 'brand', 'image_url', 'price', 'sale_price', 'availability'],
            \array_slice(Columns::all(), 0, 9)
        );
    }

    public function testItContainsEveryDocumentedField(): void
    {
        $this->assertCount(53, Columns::all());
    }

    public function testItContainsNoDuplicates(): void
    {
        $this->assertSame(Columns::all(), \array_values(\array_unique(Columns::all())));
    }

    public function testItRecognisesADocumentedField(): void
    {
        $this->assertTrue(Columns::exists('is_ads_eligible'));
    }

    public function testItDoesNotRecogniseALegacyAlias(): void
    {
        $this->assertFalse(Columns::exists('enable_search'));
    }

    public function testItPointsALegacyAliasAtTheFieldNameWhichReplacedIt(): void
    {
        $this->assertSame('is_eligible_search', Columns::closestTo('enable_search'));
        $this->assertSame('is_eligible_checkout', Columns::closestTo('enable_checkout'));
        $this->assertSame('item_id', Columns::closestTo('id'));
        $this->assertSame('item_id', Columns::closestTo('sku'));
        $this->assertSame('group_id', Columns::closestTo('item_group_id'));
        $this->assertSame('is_ads_eligible', Columns::closestTo('is_eligible_ads'));
        $this->assertSame('return_deadline_in_days', Columns::closestTo('return_window'));
    }

    public function testItPointsANameFromThePreviousSpecificationAtItsReplacement(): void
    {
        $this->assertSame('url', Columns::closestTo('link'));
        $this->assertSame('image_url', Columns::closestTo('image_link'));
        $this->assertSame('additional_image_urls', Columns::closestTo('additional_image_link'));
        $this->assertSame('review_count', Columns::closestTo('product_review_count'));
    }

    public function testItPointsAMistypedColumnAtTheOneItResembles(): void
    {
        $this->assertSame('image_url', Columns::closestTo('image_urls'));
        $this->assertSame('item_id', Columns::closestTo('item_di'));
    }

    public function testItMatchesAColumnRegardlessOfItsCasing(): void
    {
        $this->assertSame('item_id', Columns::closestTo('ITEM_ID'));
    }
}
