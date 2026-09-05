<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Enum\Availability;
use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * What the item costs and whether it can be ordered.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait PriceAndAvailability
{
    private ?string $price = null;
    private ?string $salePrice = null;
    private ?string $availability = null;

    /**
     * Required. The item's regular price, in major currency units.
     *
     * Pass the amount as a string to preserve its precision exactly, or as a number to have it formatted to two
     * decimal places. The currency is an ISO 4217 code, and every other money attribute on the product must use the
     * same one.
     *
     * @param string $currency the ISO 4217 currency code, for example "USD" or "GBP"
     *
     * @return $this
     */
    public function setPrice(float | int | string $price, string $currency): static
    {
        $this->price = Validate::money('price', $price, $currency);

        return $this;
    }

    /**
     * The current sale price, which must be greater than zero and strictly less than the regular price.
     *
     * Sale windows are not scheduled from the feed, so remove the sale price once the promotion has ended.
     *
     * @param string $currency the ISO 4217 currency code, which must match the regular price's currency
     *
     * @return $this
     */
    public function setSalePrice(float | int | string $salePrice, string $currency): static
    {
        $this->salePrice = Validate::money('sale_price', $salePrice, $currency);

        return $this;
    }

    /**
     * Required. Whether the item can be ordered right now.
     *
     * @return $this
     */
    public function setAvailability(Availability | string $availability): static
    {
        $this->availability = Validate::enum('availability', $availability, Availability::class)->value;

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function priceAndAvailabilityAttributes(): array
    {
        return Attributes::set([
            'price' => $this->price,
            'sale_price' => $this->salePrice,
            'availability' => $this->availability
        ]);
    }
}
