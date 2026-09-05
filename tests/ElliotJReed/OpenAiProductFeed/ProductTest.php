<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed;

use ElliotJReed\OpenAiProductFeed\Attribute\AdsMetadata;
use ElliotJReed\OpenAiProductFeed\Attribute\Dimensions;
use ElliotJReed\OpenAiProductFeed\Attribute\Shipping;
use ElliotJReed\OpenAiProductFeed\Attribute\VariantOptions;
use ElliotJReed\OpenAiProductFeed\Enum\AgeGroup;
use ElliotJReed\OpenAiProductFeed\Enum\Availability;
use ElliotJReed\OpenAiProductFeed\Enum\Condition;
use ElliotJReed\OpenAiProductFeed\Enum\DimensionsUnit;
use ElliotJReed\OpenAiProductFeed\Enum\Gender;
use ElliotJReed\OpenAiProductFeed\Enum\SizeSystem;
use ElliotJReed\OpenAiProductFeed\Enum\WeightUnit;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Internal\Columns;
use ElliotJReed\OpenAiProductFeed\Product;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(Product::class)]
final class ProductTest extends TestCase
{
    public function testItHasNoAttributesWhenNothingIsSet(): void
    {
        $this->assertSame([], new Product()->attributes());
        $this->assertNull(new Product()->itemId());
    }

    public function testItExposesTheItemIdSoThatAFeedCanNameTheOffendingProduct(): void
    {
        $this->assertSame('TRAIL-BLK-10', new Product()->setItemId('TRAIL-BLK-10')->itemId());
    }

    public function testItReturnsTheRequiredAttributesInSpecificationOrder(): void
    {
        $this->assertSame(
            [
                'item_id' => 'TRAIL-BLK-10',
                'title' => 'Trail running shoes — black, size 10',
                'description' => 'Waterproof trail shoes with a rubber outsole and mesh lining.',
                'url' => 'https://example.com/products/trail?color=black&size=10',
                'brand' => 'Northline',
                'image_url' => 'https://example.com/images/trail-black.jpg',
                'price' => '79.99 USD',
                'availability' => 'in_stock',
                'seller_name' => 'Northline Outdoor'
            ],
            self::minimalProduct()->attributes()
        );
    }

    public function testItEmitsEveryAttributeInTheSpecificationsOwnColumnOrder(): void
    {
        $this->assertSame(Columns::all(), \array_keys(self::fullyPopulatedProduct()->attributes()));
    }

    public function testItEmitsEveryAttributeTheSpecificationDocuments(): void
    {
        $this->assertCount(53, self::fullyPopulatedProduct()->attributes());
    }

    public function testItRendersStructuredAttributesAsNestedObjects(): void
    {
        $attributes = self::fullyPopulatedProduct()->attributes();

        $this->assertSame(['color' => 'Black', 'size' => '10'], $attributes['variant_dict']);
        $this->assertSame(
            ['length' => '30', 'width' => '20', 'height' => '12', 'unit' => 'cm'],
            $attributes['dimensions']
        );
        $this->assertSame(['custom_label_0' => 'summer'], $attributes['ads_metadata']);
    }

    public function testItRendersRepeatedAttributesAsLists(): void
    {
        $attributes = self::fullyPopulatedProduct()->attributes();

        $this->assertSame(
            ['https://example.com/images/trail-side.jpg', 'https://example.com/images/trail-sole.jpg'],
            $attributes['additional_image_urls']
        );
        $this->assertSame(['US', 'CA'], $attributes['target_countries']);
    }

    public function testItRendersBooleanAttributesAsBooleans(): void
    {
        $attributes = self::fullyPopulatedProduct()->attributes();

        $this->assertTrue($attributes['listing_has_variations']);
        $this->assertTrue($attributes['is_eligible_search']);
        $this->assertFalse($attributes['is_digital']);
    }

    public function testItRendersAnExplicitlyFalseFlagRatherThanOmittingIt(): void
    {
        $attributes = self::minimalProduct()->setEligibleForSearch(false)->attributes();

        $this->assertArrayHasKey('is_eligible_search', $attributes);
        $this->assertFalse($attributes['is_eligible_search']);
    }

    public function testItRendersAZeroReviewCountRatherThanOmittingIt(): void
    {
        $this->assertSame(0, self::minimalProduct()->setReviewCount(0)->attributes()['review_count']);
    }

    public function testItRendersTheColourAttributeUnderTheSpecificationsSpelling(): void
    {
        $this->assertSame('Navy', self::minimalProduct()->setColour('Navy')->attributes()['color']);
    }

    public function testItRendersTheTermsOfServiceAttributeUnderTheSpecificationsAbbreviation(): void
    {
        $attributes = self::minimalProduct()->setSellerTermsOfService('https://example.com/terms')->attributes();

        $this->assertSame('https://example.com/terms', $attributes['seller_tos']);
    }

    public function testItSetsTheWeightAndItsUnitTogether(): void
    {
        $attributes = self::minimalProduct()->setWeight('0.75', WeightUnit::Kilograms)->attributes();

        $this->assertSame('0.75', $attributes['weight']);
        $this->assertSame('kg', $attributes['item_weight_unit']);
    }

    public function testItRendersTheShippingTuple(): void
    {
        $attributes = self::minimalProduct()
            ->setShipping(new Shipping('US', 'Standard', 5.00, 'USD'))
            ->attributes();

        $this->assertSame('US::Standard:5.00 USD', $attributes['shipping']);
    }

    public function testItReplacesAdditionalImagesRatherThanAppendingWhenSetAsAList(): void
    {
        $attributes = self::minimalProduct()
            ->addAdditionalImageUrl('https://example.com/images/one.jpg')
            ->setAdditionalImageUrls(['https://example.com/images/two.jpg'])
            ->attributes();

        $this->assertSame(['https://example.com/images/two.jpg'], $attributes['additional_image_urls']);
    }

    public function testItOmitsAdditionalImagesWhenNoneAreAdded(): void
    {
        $this->assertArrayNotHasKey('additional_image_urls', self::minimalProduct()->attributes());
    }

    public function testItOmitsTargetCountriesWhenNoneAreAdded(): void
    {
        $this->assertArrayNotHasKey('target_countries', self::minimalProduct()->attributes());
    }

    public function testItAcceptsEnumsAsTheirStringValues(): void
    {
        $attributes = self::minimalProduct()
            ->setCondition('refurbished')
            ->setGender('unisex')
            ->setAgeGroup('adult')
            ->setSizeSystem('UK')
            ->attributes();

        $this->assertSame('refurbished', $attributes['condition']);
        $this->assertSame('unisex', $attributes['gender']);
        $this->assertSame('adult', $attributes['age_group']);
        $this->assertSame('UK', $attributes['size_system']);
    }

    public function testItThrowsAsSoonAsAnInvalidValueIsSet(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The title attribute must not exceed 150 characters');

        new Product()->setTitle(\str_repeat('a', 151));
    }

    public function testItThrowsWhenTheStructuredAndSeparateDimensionsAreBothIncomplete(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('at least two of length, width and height, 1 given');

        self::minimalProduct()->setDimensions(new Dimensions(DimensionsUnit::Centimetres)->setLength('30'))
            ->attributes();
    }

    public static function minimalProduct(): Product
    {
        return new Product()
            ->setItemId('TRAIL-BLK-10')
            ->setTitle('Trail running shoes — black, size 10')
            ->setDescription('Waterproof trail shoes with a rubber outsole and mesh lining.')
            ->setUrl('https://example.com/products/trail?color=black&size=10')
            ->setBrand('Northline')
            ->setImageUrl('https://example.com/images/trail-black.jpg')
            ->setPrice('79.99', 'USD')
            ->setAvailability(Availability::InStock)
            ->setSellerName('Northline Outdoor');
    }

    /**
     * A product with every documented attribute set, used to prove the feed emits all of them in the
     * specification's own order. It deliberately sets both representations of the delivery charge, which the
     * specification treats as alternatives, so it is not a product a feed would accept as it stands.
     */
    public static function fullyPopulatedProduct(): Product
    {
        return self::minimalProduct()
            ->setSalePrice('59.99', 'USD')
            ->setGroupId('TRAIL')
            ->setListingHasVariations(true)
            ->setVariantOptions(new VariantOptions(['color' => 'Black', 'size' => '10']))
            ->setOfferId('northline-TRAIL-BLK-10')
            ->setGtin('09506000134352')
            ->setMpn('NL-TRAIL-10-BLK')
            ->setCondition(Condition::New)
            ->setProductCategory('Apparel & Accessories > Shoes')
            ->setMaterial('Leather and rubber')
            ->setColour('Black')
            ->setSize('10')
            ->setSizeSystem(SizeSystem::UnitedStates)
            ->setGender(Gender::Unisex)
            ->setAgeGroup(AgeGroup::Adult)
            ->setDimensions(
                new Dimensions(DimensionsUnit::Centimetres)->setLength('30')->setWidth('20')->setHeight('12')
            )
            ->setLength('30')
            ->setWidth('20')
            ->setHeight('12')
            ->setDimensionsUnit(DimensionsUnit::Centimetres)
            ->setWeight('0.75', WeightUnit::Kilograms)
            ->setDigital(false)
            ->setAdditionalImageUrls([
                'https://example.com/images/trail-side.jpg',
                'https://example.com/images/trail-sole.jpg'
            ])
            ->setSellerUrl('https://example.com/stores/northline')
            ->setMarketplaceSeller('Example Marketplace')
            ->setStoreCountry('US')
            ->setShippingPrice('5.00', 'USD')
            ->setShipping(new Shipping('US', 'Standard', '5.00', 'USD'))
            ->setAcceptsReturns(true)
            ->setReturnDeadlineInDays(30)
            ->setReturnPolicy('https://example.com/returns')
            ->setAcceptsExchanges(false)
            ->setReviewCount(254)
            ->setStarRating('4.50')
            ->setStoreReviewCount(2000)
            ->setStoreStarRating('4.70')
            ->setTargetCountries(['US', 'CA'])
            ->setEligibleForSearch(true)
            ->setEligibleForCheckout(true)
            ->setSellerPrivacyPolicy('https://example.com/privacy')
            ->setSellerTermsOfService('https://example.com/terms')
            ->setEligibleForAds(true)
            ->setAdsMetadata(new AdsMetadata(['custom_label_0' => 'summer']));
    }
}
