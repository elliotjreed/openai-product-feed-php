<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed;

use ElliotJReed\OpenAiProductFeed\Product\Ads;
use ElliotJReed\OpenAiProductFeed\Product\BasicProductData;
use ElliotJReed\OpenAiProductFeed\Product\Fulfilment;
use ElliotJReed\OpenAiProductFeed\Product\GeoTargeting;
use ElliotJReed\OpenAiProductFeed\Product\ItemInformation;
use ElliotJReed\OpenAiProductFeed\Product\Media;
use ElliotJReed\OpenAiProductFeed\Product\MerchantInformation;
use ElliotJReed\OpenAiProductFeed\Product\PriceAndAvailability;
use ElliotJReed\OpenAiProductFeed\Product\Returns;
use ElliotJReed\OpenAiProductFeed\Product\Reviews;
use ElliotJReed\OpenAiProductFeed\Product\SearchAndCheckout;
use ElliotJReed\OpenAiProductFeed\Product\Variants;

/**
 * A single item within an OpenAI product feed, rendered as one JSON Lines record or one row of a delimited feed.
 *
 * Every attribute is set through a fluent setter which validates its value immediately, so a value OpenAI would
 * reject raises an exception at the point it is set rather than when the feed is ingested. Attributes are grouped
 * into traits mirroring the sections of the product feed specification.
 *
 * The rules which span more than one attribute, such as a sale price having to be lower than the price it discounts,
 * cannot be checked until every value is known. They are applied when the product is added to a feed.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
final class Product
{
    use Ads;
    use BasicProductData;
    use Fulfilment;
    use GeoTargeting;
    use ItemInformation;
    use Media;
    use MerchantInformation;
    use PriceAndAvailability;
    use Returns;
    use Reviews;
    use SearchAndCheckout;
    use Variants;

    /**
     * Every attribute which has been set, keyed by its feed field name, in the order the specification documents them.
     *
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    public function attributes(): array
    {
        return [
            ...$this->basicProductDataAttributes(),
            ...$this->priceAndAvailabilityAttributes(),
            ...$this->variantAttributes(),
            ...$this->itemInformationAttributes(),
            ...$this->mediaAttributes(),
            ...$this->merchantInformationAttributes(),
            ...$this->fulfilmentAttributes(),
            ...$this->returnsAttributes(),
            ...$this->reviewAttributes(),
            ...$this->geoTargetingAttributes(),
            ...$this->searchAndCheckoutAttributes(),
            ...$this->adsAttributes()
        ];
    }
}
