<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Attribute;

use ElliotJReed\OpenAiProductFeed\Exception\InvalidAttribute;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * The shipping attribute: a delivery charge expressed as the four part tuple
 * "country:region:service_class:price", for example "US::Standard:5.00 USD".
 *
 * The region is optional and left empty where a charge applies to the whole country. This form is only accepted by
 * integrations which have been configured for shipping tuples; every other integration uses the simpler
 * shipping_price attribute instead. Do not submit both for the same charge.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
final class Shipping
{
    private const string SEPARATOR = ':';

    private readonly string $country;
    private readonly string $serviceClass;
    private readonly string $price;

    private string $region = '';

    /**
     * @param string $country      the destination country, as an ISO 3166-1 alpha-2 code, for example "US"
     * @param string $serviceClass the name of the delivery service, for example "Standard" or "Overnight"
     * @param string $currency     the ISO 4217 currency code of the price, which must match the item's own currency
     */
    public function __construct(
        string $country,
        string $serviceClass,
        float | int | string $price,
        string $currency
    ) {
        $this->country = Validate::country('shipping', $country);
        $this->serviceClass = self::withoutSeparator('shipping service class', $serviceClass);
        $this->price = Validate::money('shipping', $price, $currency);
    }

    /**
     * The region within the country the charge applies to. Leave this unset where the charge applies countrywide.
     */
    public function setRegion(string $region): self
    {
        $this->region = self::withoutSeparator('shipping region', $region);

        return $this;
    }

    /**
     * The charge as the tuple OpenAI expects, for example "US:CA:Overnight:16.00 USD".
     */
    public function toString(): string
    {
        return \implode(self::SEPARATOR, [$this->country, $this->region, $this->serviceClass, $this->price]);
    }

    /**
     * The tuple is split on colons, so a value containing one would be read as more parts than it has.
     */
    private static function withoutSeparator(string $attribute, string $value): string
    {
        $text = Validate::text($attribute, $value);

        if (\str_contains($text, self::SEPARATOR)) {
            throw InvalidAttribute::invalidFormat($attribute, $text, 'free of the ":" separator');
        }

        return $text;
    }
}
