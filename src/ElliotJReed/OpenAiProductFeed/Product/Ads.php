<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Attribute\AdsMetadata;
use ElliotJReed\OpenAiProductFeed\Internal\Attributes;

/**
 * Whether the item takes part in OpenAI Ads campaigns, and the metadata those campaigns can filter it by.
 *
 * @see https://developers.openai.com/ads/product-feeds
 */
trait Ads
{
    private ?bool $eligibleForAds = null;
    private ?AdsMetadata $adsMetadata = null;

    /**
     * Whether Ads should process this item. It must be true for the item to appear in an Ads campaign.
     *
     * Left unset, the item is not processed for Ads unless your feed has been configured with a default.
     *
     * @return $this
     */
    public function setEligibleForAds(bool $eligibleForAds): static
    {
        $this->eligibleForAds = $eligibleForAds;

        return $this;
    }

    /**
     * The custom product filter metadata an Ads campaign can target, using only the keys configured for your
     * integration.
     *
     * @return $this
     */
    public function setAdsMetadata(AdsMetadata $adsMetadata): static
    {
        $this->adsMetadata = $adsMetadata;

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function adsAttributes(): array
    {
        return Attributes::set([
            'is_ads_eligible' => $this->eligibleForAds,
            'ads_metadata' => $this->adsMetadata
        ]);
    }
}
