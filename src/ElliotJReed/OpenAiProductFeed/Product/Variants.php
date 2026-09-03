<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Attribute\VariantOptions;
use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * How an item relates to the other variants of the same product, and the identifiers which describe it.
 *
 * A variant row needs all three of the group identifier, the variations flag and the variant options. Setting any one
 * of them without the others is rejected when the product is added to a feed.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait Variants
{
    private ?string $groupId = null;
    private ?bool $listingHasVariations = null;
    private ?VariantOptions $variantOptions = null;
    private ?string $offerId = null;
    private ?string $gtin = null;
    private ?string $mpn = null;

    /**
     * The stable identifier of the parent listing every variant of this product shares.
     *
     * It must differ from the item identifier, and should represent the product as customers see it on your site,
     * rather than one particular variant.
     *
     * @return $this
     */
    public function setGroupId(string $groupId): static
    {
        $this->groupId = Validate::text('group_id', $groupId);

        return $this;
    }

    /**
     * Whether this listing has variations. Set it to true on every row of a variant group.
     *
     * @return $this
     */
    public function setListingHasVariations(bool $listingHasVariations): static
    {
        $this->listingHasVariations = $listingHasVariations;

        return $this;
    }

    /**
     * The options which distinguish this variant, mapped to the values selected for it.
     *
     * Keep these consistent with the equivalent top level attributes such as colour and size, since OpenAI does not
     * reconcile conflicts between them.
     *
     * @return $this
     */
    public function setVariantOptions(VariantOptions $variantOptions): static
    {
        $this->variantOptions = $variantOptions;

        return $this;
    }

    /**
     * A stable offer identifier, unique within the feed, distinguishing offers which share a product page.
     *
     * @return $this
     */
    public function setOfferId(string $offerId): static
    {
        $this->offerId = Validate::text('offer_id', $offerId);

        return $this;
    }

    /**
     * The item's Global Trade Item Number, as assigned by the manufacturer.
     *
     * It must be exactly 8, 12, 13 or 14 digits and carry a valid check digit. Spaces and hyphens are removed, and
     * leading zeros are significant and preserved.
     *
     * @return $this
     */
    public function setGtin(string $gtin): static
    {
        $this->gtin = Validate::gtin($gtin);

        return $this;
    }

    /**
     * The manufacturer's part number, preserving its punctuation and casing exactly as the manufacturer assigns it.
     *
     * @return $this
     */
    public function setMpn(string $mpn): static
    {
        $this->mpn = Validate::text('mpn', $mpn);

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function variantAttributes(): array
    {
        return Attributes::set([
            'group_id' => $this->groupId,
            'listing_has_variations' => $this->listingHasVariations,
            'variant_dict' => $this->variantOptions,
            'offer_id' => $this->offerId,
            'gtin' => $this->gtin,
            'mpn' => $this->mpn
        ]);
    }
}
