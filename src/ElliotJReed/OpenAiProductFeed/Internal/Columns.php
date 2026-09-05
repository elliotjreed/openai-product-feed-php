<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Internal;

/**
 * Every field name in the OpenAI product feed specification, in the order the specification documents them.
 *
 * This is the column order of a delimited feed and the key order of a JSON Lines record. Only the current field names
 * appear here: the legacy aliases OpenAI still accepts, such as "id" for "item_id" or "enable_search" for
 * "is_eligible_search", are deliberately not emitted.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 *
 * @internal
 */
final class Columns
{
    private const array NAMES = [
        'item_id',
        'title',
        'description',
        'url',
        'brand',
        'image_url',
        'price',
        'sale_price',
        'availability',
        'group_id',
        'listing_has_variations',
        'variant_dict',
        'offer_id',
        'gtin',
        'mpn',
        'condition',
        'product_category',
        'material',
        'color',
        'size',
        'size_system',
        'gender',
        'age_group',
        'dimensions',
        'length',
        'width',
        'height',
        'dimensions_unit',
        'weight',
        'item_weight_unit',
        'is_digital',
        'additional_image_urls',
        'seller_name',
        'seller_url',
        'marketplace_seller',
        'store_country',
        'shipping_price',
        'shipping',
        'accepts_returns',
        'return_deadline_in_days',
        'return_policy',
        'accepts_exchanges',
        'review_count',
        'star_rating',
        'store_review_count',
        'store_star_rating',
        'target_countries',
        'is_eligible_search',
        'is_eligible_checkout',
        'seller_privacy_policy',
        'seller_tos',
        'is_ads_eligible',
        'ads_metadata'
    ];

    /**
     * The names OpenAI still accepts but no longer documents, mapped to the names which replaced them, along with the
     * names used by the previous revision of the specification. A feed built by this library always uses the current
     * name, so these serve only to point a caller at the one they should be asking for.
     */
    private const array SUPERSEDED_NAMES = [
        'id' => 'item_id',
        'sku' => 'item_id',
        'item_group_id' => 'group_id',
        'enable_search' => 'is_eligible_search',
        'enable_checkout' => 'is_eligible_checkout',
        'is_eligible_ads' => 'is_ads_eligible',
        'return_window' => 'return_deadline_in_days',
        'link' => 'url',
        'image_link' => 'image_url',
        'additional_image_link' => 'additional_image_urls',
        'product_review_count' => 'review_count',
        'product_review_rating' => 'star_rating',
        'store_review_rating' => 'store_star_rating'
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return self::NAMES;
    }

    public static function exists(string $column): bool
    {
        return \in_array($column, self::NAMES, true);
    }

    /**
     * The documented field name most like the one given, used to point a caller who has mistyped a column, or reached
     * for a name the specification has since replaced, at the name it uses now.
     */
    public static function closestTo(string $column): string
    {
        $given = \strtolower(\trim($column));

        if (\array_key_exists($given, self::SUPERSEDED_NAMES)) {
            return self::SUPERSEDED_NAMES[$given];
        }

        $closest = self::NAMES[0];
        $shortestDistance = \PHP_INT_MAX;

        foreach (self::NAMES as $name) {
            $distance = \levenshtein($given, $name);

            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $closest = $name;
            }
        }

        return $closest;
    }
}
