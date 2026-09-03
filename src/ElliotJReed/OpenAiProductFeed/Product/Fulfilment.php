<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Attribute\Shipping;
use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * What it costs to deliver the item.
 *
 * There are two ways to express this and they are alternatives, not companions: the simple shipping price, or the
 * four part shipping tuple which only integrations configured for it accept. Do not submit both for the same charge.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait Fulfilment
{
    private ?string $shippingPrice = null;
    private ?string $shipping = null;

    /**
     * The delivery charge for this item, in the same currency as its price. Zero means delivery is free.
     *
     * @param string $currency the ISO 4217 currency code, which must match the price's currency
     *
     * @return $this
     */
    public function setShippingPrice(float | int | string $shippingPrice, string $currency): static
    {
        $this->shippingPrice = Validate::money('shipping_price', $shippingPrice, $currency);

        return $this;
    }

    /**
     * The delivery charge as the four part "country:region:service_class:price" tuple, for integrations configured to
     * accept it.
     *
     * @return $this
     */
    public function setShipping(Shipping $shipping): static
    {
        $this->shipping = $shipping->toString();

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function fulfilmentAttributes(): array
    {
        return Attributes::set([
            'shipping_price' => $this->shippingPrice,
            'shipping' => $this->shipping
        ]);
    }
}
