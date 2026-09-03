<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Attribute;

use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * The ads_metadata attribute: the custom product filter metadata an Ads campaign can target.
 *
 * Only use keys which have been configured for your Ads integration. Keys OpenAI has not been told about are ignored
 * rather than rejected, so confirm them during setup rather than inventing your own.
 *
 * @see https://developers.openai.com/ads/product-feeds
 */
final class AdsMetadata implements StructuredAttribute
{
    /** @var array<string, string> */
    private array $metadata = [];

    /**
     * @param array<string, string> $metadata the filter keys mapped to their values, for example
     *                                        ["custom_label_0" => "summer"]
     */
    public function __construct(array $metadata = [])
    {
        foreach ($metadata as $key => $value) {
            $this->add($key, $value);
        }
    }

    /**
     * Adds a filter key and its value. Setting the same key twice replaces the earlier value.
     */
    public function add(string $key, string $value): self
    {
        $name = Validate::text('ads_metadata key', $key);

        $this->metadata[$name] = Validate::text(\sprintf('ads_metadata "%s" value', $name), $value);

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->metadata;
    }
}
