<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * Where the item is offered.
 *
 * The standard OpenAI format targets the United States regardless of what these attributes say, so confirm that
 * multi-country support has been enabled for your integration before relying on them.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait GeoTargeting
{
    /** @var list<string> */
    private array $targetCountries = [];

    /**
     * Adds a destination country the item is offered in, as an ISO 3166-1 alpha-2 code.
     *
     * @return $this
     */
    public function addTargetCountry(string $country): static
    {
        $this->targetCountries[] = Validate::country('target_countries', $country);

        return $this;
    }

    /**
     * Replaces every destination country with the ones given.
     *
     * @param list<string> $countries
     *
     * @return $this
     */
    public function setTargetCountries(array $countries): static
    {
        $this->targetCountries = [];

        foreach ($countries as $country) {
            $this->addTargetCountry($country);
        }

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function geoTargetingAttributes(): array
    {
        return Attributes::set([
            'target_countries' => [] === $this->targetCountries ? null : $this->targetCountries
        ]);
    }
}
