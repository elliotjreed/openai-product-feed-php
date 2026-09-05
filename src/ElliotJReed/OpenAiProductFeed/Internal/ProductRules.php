<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Internal;

use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;

/**
 * The specification's rules which span more than one attribute, applied once per product as it is written.
 *
 * These cannot be checked by the individual setters, because doing so would make the order the setters are called in
 * significant: a sale price set before the price it discounts has nothing to be compared against yet.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 *
 * @internal
 */
final class ProductRules
{
    private const array REQUIRED = [
        'item_id',
        'title',
        'description',
        'url',
        'brand',
        'seller_name',
        'image_url',
        'availability',
        'price'
    ];

    private const array VARIANT = ['group_id', 'listing_has_variations', 'variant_dict'];

    private const array SEPARATE_DIMENSIONS = ['length', 'width', 'height'];

    /**
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    public static function assertSatisfied(array $attributes): void
    {
        self::assertRequiredAttributesPresent($attributes);
        self::assertPricesAreConsistent($attributes);
        self::assertDeliveryChargeIsUnambiguous($attributes);
        self::assertVariantIsComplete($attributes);
        self::assertMeasurementsHaveUnits($attributes);
        self::assertCheckoutIsEligible($attributes);
        self::assertReturnsAreConsistent($attributes);
    }

    /**
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    private static function assertRequiredAttributesPresent(array $attributes): void
    {
        foreach (self::REQUIRED as $attribute) {
            if (!\array_key_exists($attribute, $attributes)) {
                throw InvalidAttribute::missing($attribute);
            }
        }
    }

    /**
     * A sale price has to undercut the price it discounts, and every money attribute on a product has to share one
     * currency.
     *
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    private static function assertPricesAreConsistent(array $attributes): void
    {
        $price = (string) $attributes['price'];
        $currency = self::currencyOf($price);

        foreach (['sale_price', 'shipping_price'] as $attribute) {
            if (!isset($attributes[$attribute])) {
                continue;
            }

            $other = (string) $attributes[$attribute];

            if (self::currencyOf($other) !== $currency) {
                throw InvalidAttribute::mismatchedCurrency($attribute, $currency, self::currencyOf($other));
            }
        }

        if (isset($attributes['sale_price'])) {
            $salePrice = (string) $attributes['sale_price'];

            if ((float) self::amountOf($salePrice) >= (float) self::amountOf($price)) {
                throw InvalidAttribute::notLowerThan('sale_price', 'price', $salePrice, $price);
            }
        }
    }

    /**
     * The simple delivery charge and the four part tuple describe the same thing in two different ways, and the
     * specification says not to submit both.
     *
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    private static function assertDeliveryChargeIsUnambiguous(array $attributes): void
    {
        if (isset($attributes['shipping_price'], $attributes['shipping'])) {
            throw InvalidAttribute::mutuallyExclusive('shipping_price', 'shipping');
        }
    }

    /**
     * A variant row needs its group, the flag marking the listing as having variations, and the options which
     * distinguish it. The group has to name the parent listing rather than repeat the item's own identifier.
     *
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    private static function assertVariantIsComplete(array $attributes): void
    {
        $set = \array_filter(
            self::VARIANT,
            static fn (string $attribute): bool => isset($attributes[$attribute])
                && false !== $attributes[$attribute]
                && [] !== $attributes[$attribute]
        );

        if ([] === $set) {
            return;
        }

        foreach (self::VARIANT as $attribute) {
            if (!\in_array($attribute, $set, true)) {
                throw InvalidAttribute::incompleteVariant($attribute);
            }
        }

        if ($attributes['group_id'] === $attributes['item_id']) {
            throw InvalidAttribute::duplicatedIdentifier('group_id', 'item_id', (string) $attributes['group_id']);
        }
    }

    /**
     * A measurement without its unit cannot be interpreted. The unit accompanying a weight is set alongside it, so
     * only the separate dimensions can be left without one.
     *
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    private static function assertMeasurementsHaveUnits(array $attributes): void
    {
        foreach (self::SEPARATE_DIMENSIONS as $dimension) {
            if (isset($attributes[$dimension]) && !isset($attributes['dimensions_unit'])) {
                throw InvalidAttribute::requiredWhenAnyOf('dimensions_unit', 'length, width or height');
            }
        }
    }

    /**
     * Checkout only opts an item in where it can also be found, and needs the seller's policies alongside it.
     *
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    private static function assertCheckoutIsEligible(array $attributes): void
    {
        if (true !== ($attributes['is_eligible_checkout'] ?? false)) {
            return;
        }

        if (false === ($attributes['is_eligible_search'] ?? true)) {
            throw InvalidAttribute::permittedOnlyWhenTrue('is_eligible_checkout', 'is_eligible_search');
        }

        foreach (['seller_privacy_policy', 'seller_tos'] as $attribute) {
            if (!isset($attributes[$attribute])) {
                throw InvalidAttribute::requiredWhenTrue($attribute, 'is_eligible_checkout');
            }
        }
    }

    /**
     * A returns deadline only means something where returns are accepted.
     *
     * @param array<string, scalar|list<string>|array<string, string>> $attributes
     */
    private static function assertReturnsAreConsistent(array $attributes): void
    {
        if (isset($attributes['return_deadline_in_days']) && false === ($attributes['accepts_returns'] ?? null)) {
            throw InvalidAttribute::permittedOnlyWhenNotFalse('return_deadline_in_days', 'accepts_returns');
        }
    }

    private static function currencyOf(string $money): string
    {
        return \substr($money, \strrpos($money, ' ') + 1);
    }

    private static function amountOf(string $money): string
    {
        return \substr($money, 0, (int) \strrpos($money, ' '));
    }
}
