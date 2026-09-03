<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * The core identifiers and descriptive text which establish the canonical record for an item.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait BasicProductData
{
    private const int MAXIMUM_TITLE_LENGTH = 150;
    private const int MAXIMUM_DESCRIPTION_LENGTH = 5000;

    private ?string $itemId = null;
    private ?string $title = null;
    private ?string $description = null;
    private ?string $url = null;
    private ?string $brand = null;
    private ?string $imageUrl = null;

    /**
     * Required. A stable identifier, unique to this item or variant within your feed.
     *
     * Keep it the same for the lifetime of the item, and never reuse it for a different one.
     *
     * @return $this
     */
    public function setItemId(string $itemId): static
    {
        $this->itemId = Validate::text('item_id', $itemId);

        return $this;
    }

    /**
     * The item's identifier, exposed so that a feed can name the offending product when it rejects one.
     */
    public function itemId(): ?string
    {
        return $this->itemId;
    }

    /**
     * Required. The product's name, including the selected variant where relevant.
     *
     * Write it as plain text, as it appears on your product page. Maximum 150 characters.
     *
     * @return $this
     */
    public function setTitle(string $title): static
    {
        $this->title = Validate::text('title', $title, self::MAXIMUM_TITLE_LENGTH);

        return $this;
    }

    /**
     * Required. A factual description of this item.
     *
     * Describe only the product itself, as plain text rather than markup. Maximum 5,000 characters.
     *
     * @return $this
     */
    public function setDescription(string $description): static
    {
        $this->description = Validate::text('description', $description, self::MAXIMUM_DESCRIPTION_LENGTH);

        return $this;
    }

    /**
     * Required. The product detail page for this item, with the variant selected where possible.
     *
     * Use an absolute, publicly accessible URL, and keep it stable. HTTPS is preferred.
     *
     * @return $this
     */
    public function setUrl(string $url): static
    {
        $this->url = Validate::url('url', $url);

        return $this;
    }

    /**
     * Required. The product's brand, as shown on the product page.
     *
     * Use the real brand name rather than a placeholder.
     *
     * @return $this
     */
    public function setBrand(string $brand): static
    {
        $this->brand = Validate::text('brand', $brand);

        return $this;
    }

    /**
     * Required. The main product image, showing this variant.
     *
     * Use a direct, publicly accessible URL to a JPEG or PNG, not a page containing the image.
     *
     * @return $this
     */
    public function setImageUrl(string $imageUrl): static
    {
        $this->imageUrl = Validate::url('image_url', $imageUrl);

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function basicProductDataAttributes(): array
    {
        return Attributes::set([
            'item_id' => $this->itemId,
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url,
            'brand' => $this->brand,
            'image_url' => $this->imageUrl
        ]);
    }
}
