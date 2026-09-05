<?php

declare(strict_types=1);

namespace ElliotJReed\Tests\OpenAiProductFeed\Internal;

use ElliotJReed\OpenAiProductFeed\Attribute\Shipping;
use ElliotJReed\OpenAiProductFeed\Attribute\VariantOptions;
use ElliotJReed\OpenAiProductFeed\Enum\DimensionsUnit;
use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Internal\ProductRules;
use ElliotJReed\OpenAiProductFeed\Product;
use ElliotJReed\Tests\OpenAiProductFeed\ProductTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidAttribute::class)]
#[CoversClass(ProductRules::class)]
final class ProductRulesTest extends TestCase
{
    public function testItAcceptsAProductWithOnlyTheRequiredAttributes(): void
    {
        $this->expectNotToPerformAssertions();

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->attributes());
    }

    public function testItAcceptsAFullyPopulatedProduct(): void
    {
        $this->expectNotToPerformAssertions();

        $attributes = ProductTest::fullyPopulatedProduct()->attributes();
        unset($attributes['shipping']);

        ProductRules::assertSatisfied($attributes);
    }

    #[DataProvider('requiredAttributes')]
    public function testItThrowsWhenARequiredAttributeIsMissing(string $attribute): void
    {
        $attributes = ProductTest::minimalProduct()->attributes();
        unset($attributes[$attribute]);

        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            \sprintf('The %s attribute is required, but has not been set on this product.', $attribute)
        );

        ProductRules::assertSatisfied($attributes);
    }

    /**
     * @return list<array{string}>
     */
    public static function requiredAttributes(): array
    {
        return [
            ['item_id'],
            ['title'],
            ['description'],
            ['url'],
            ['brand'],
            ['seller_name'],
            ['image_url'],
            ['availability'],
            ['price']
        ];
    }

    public function testItThrowsWhenNothingHasBeenSetAtAll(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('The item_id attribute is required');

        ProductRules::assertSatisfied([]);
    }

    public function testItThrowsWhenTheSalePriceIsNotLowerThanThePrice(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The sale_price attribute must be lower than the price attribute, "79.99 USD" and "79.99 USD" given.'
        );

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->setSalePrice('79.99', 'USD')->attributes());
    }

    public function testItThrowsWhenTheSalePriceIsInADifferentCurrencyToThePrice(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The sale_price attribute must use the same currency as the price attribute, USD and GBP given.'
        );

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->setSalePrice('59.99', 'GBP')->attributes());
    }

    public function testItThrowsWhenTheShippingPriceIsInADifferentCurrencyToThePrice(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The shipping_price attribute must use the same currency as the price attribute, USD and GBP given.'
        );

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->setShippingPrice('5.00', 'GBP')->attributes());
    }

    public function testItThrowsWhenBothRepresentationsOfTheDeliveryChargeAreSubmitted(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The shipping_price and shipping attributes are alternatives, so only one of them may be set.'
        );

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()
                ->setShippingPrice('5.00', 'USD')
                ->setShipping(new Shipping('US', 'Standard', '5.00', 'USD'))
                ->attributes()
        );
    }

    public function testItThrowsWhenTheGroupIdMatchesTheItemId(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The group_id attribute must differ from the item_id attribute, "TRAIL-BLK-10" given for both.'
        );

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()
                ->setGroupId('TRAIL-BLK-10')
                ->setListingHasVariations(true)
                ->setVariantOptions(new VariantOptions(['color' => 'Black']))
                ->attributes()
        );
    }

    public function testItThrowsWhenAVariantIsMissingItsGroupId(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'A variant needs all of the group_id, listing_has_variations and variant_dict attributes, ' .
            'but group_id has not been set on this product.'
        );

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()
                ->setListingHasVariations(true)
                ->setVariantOptions(new VariantOptions(['color' => 'Black']))
                ->attributes()
        );
    }

    public function testItThrowsWhenAVariantIsMissingItsOptions(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage('but variant_dict has not been set on this product.');

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()->setGroupId('TRAIL')->setListingHasVariations(true)->attributes()
        );
    }

    public function testItAcceptsAProductWhichIsExplicitlyNotAVariant(): void
    {
        $this->expectNotToPerformAssertions();

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->setListingHasVariations(false)->attributes());
    }

    #[DataProvider('separateDimensions')]
    public function testItThrowsWhenASeparateDimensionHasNoUnit(string $setter): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The dimensions_unit attribute is required when the length, width or height attributes are set.'
        );

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->{$setter}('30')->attributes());
    }

    /**
     * @return list<array{string}>
     */
    public static function separateDimensions(): array
    {
        return [['setLength'], ['setWidth'], ['setHeight']];
    }

    public function testItAcceptsSeparateDimensionsWhichHaveAUnit(): void
    {
        $this->expectNotToPerformAssertions();

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()
                ->setLength('30')
                ->setWidth('20')
                ->setDimensionsUnit(DimensionsUnit::Centimetres)
                ->attributes()
        );
    }

    public function testItThrowsWhenCheckoutIsOptedIntoWithoutSearchEligibility(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The is_eligible_checkout attribute may only be true where the is_eligible_search attribute is also true.'
        );

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()
                ->setEligibleForSearch(false)
                ->setEligibleForCheckout(true)
                ->setSellerPrivacyPolicy('https://example.com/privacy')
                ->setSellerTermsOfService('https://example.com/terms')
                ->attributes()
        );
    }

    public function testItThrowsWhenCheckoutIsOptedIntoWithoutAPrivacyPolicy(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The seller_privacy_policy attribute is required when the is_eligible_checkout attribute is true.'
        );

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()
                ->setEligibleForCheckout(true)
                ->setSellerTermsOfService('https://example.com/terms')
                ->attributes()
        );
    }

    public function testItThrowsWhenCheckoutIsOptedIntoWithoutTermsOfService(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The seller_tos attribute is required when the is_eligible_checkout attribute is true.'
        );

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()
                ->setEligibleForCheckout(true)
                ->setSellerPrivacyPolicy('https://example.com/privacy')
                ->attributes()
        );
    }

    public function testItAcceptsCheckoutBeingExplicitlyDeclined(): void
    {
        $this->expectNotToPerformAssertions();

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->setEligibleForCheckout(false)->attributes());
    }

    public function testItThrowsWhenAReturnDeadlineIsGivenForAnItemWhichIsNotReturnable(): void
    {
        $this->expectException(InvalidAttribute::class);
        $this->expectExceptionMessage(
            'The return_deadline_in_days attribute may only be set where the accepts_returns attribute is not false.'
        );

        ProductRules::assertSatisfied(
            ProductTest::minimalProduct()->setAcceptsReturns(false)->setReturnDeadlineInDays(30)->attributes()
        );
    }

    public function testItAcceptsAReturnDeadlineWhereReturnsAreNotMentioned(): void
    {
        $this->expectNotToPerformAssertions();

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->setReturnDeadlineInDays(30)->attributes());
    }

    public function testItAcceptsAFinalSaleItem(): void
    {
        $this->expectNotToPerformAssertions();

        ProductRules::assertSatisfied(ProductTest::minimalProduct()->setAcceptsReturns(false)->attributes());
    }

    public function testItNamesTheProductWhichFailedWhenItHasAnItemId(): void
    {
        $product = new Product()->setItemId('TRAIL-BLK-10');

        try {
            ProductRules::assertSatisfied($product->attributes());
        } catch (InvalidAttribute $exception) {
            $this->assertStringContainsString('title', $exception->getMessage());

            return;
        }

        $this->fail('An incomplete product should not satisfy the specification.');
    }
}
