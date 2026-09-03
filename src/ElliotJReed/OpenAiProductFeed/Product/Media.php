<?php

declare(strict_types=1);

namespace ElliotJReed\OpenAiProductFeed\Product;

use ElliotJReed\OpenAiProductFeed\Internal\Attributes;
use ElliotJReed\OpenAiProductFeed\Internal\Validate;

/**
 * The further images which show the item beyond its main product image.
 *
 * @see https://developers.openai.com/commerce/specs/file-upload/products
 */
trait Media
{
    /** @var list<string> */
    private array $additionalImageUrls = [];

    /**
     * Adds a further view of this item, as a direct, publicly accessible image URL.
     *
     * Call this as many times as you have images. They are written as a JSON array in a JSON Lines feed, and as a
     * comma-separated list in a delimited feed.
     *
     * @return $this
     */
    public function addAdditionalImageUrl(string $imageUrl): static
    {
        $this->additionalImageUrls[] = Validate::url('additional_image_urls', $imageUrl);

        return $this;
    }

    /**
     * Replaces every additional image with the ones given.
     *
     * @param list<string> $imageUrls
     *
     * @return $this
     */
    public function setAdditionalImageUrls(array $imageUrls): static
    {
        $this->additionalImageUrls = [];

        foreach ($imageUrls as $imageUrl) {
            $this->addAdditionalImageUrl($imageUrl);
        }

        return $this;
    }

    /**
     * @return array<string, scalar|list<string>|array<string, string>>
     */
    private function mediaAttributes(): array
    {
        return Attributes::set([
            'additional_image_urls' => [] === $this->additionalImageUrls ? null : $this->additionalImageUrls
        ]);
    }
}
